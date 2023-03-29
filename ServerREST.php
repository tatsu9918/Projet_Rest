<?php
require_once 'jwt_utils.php';
/// Paramétrage de l'entête HTTP (pour la réponse au Client)
 header("Content-Type:application/json");
 
 /// Identification du type de méthode HTTP envoyée par le client
 $http_method = $_SERVER['REQUEST_METHOD'];


$host = 'localhost';
$dbname = 'projet_rest';
$user = 'root';
$pwd = '';

//Variable de connection
$mySqlConnection = "mysql:host=" . $host . ";dbname=" . $dbname;

try {
    $linkpdo = new PDO($mySqlConnection, $user, $pwd);
}
catch (Exception $e) {
    die('Error : ' . $e->getMessage());
}

 switch ($http_method){
    /// Cas de la méthode GET
    case "GET" :
        $req = $linkpdo->prepare('SELECT Id_Articles, titre, Contenu, date_publi, utilisateur.nom AS Auteur FROM articles, utilisateur;');

        if ($req == false) {
             die ('Error preparation');
        }

        $req2 = $req->execute();

        if ($req2 == false) {
            $req->DebugDumpParams();
            die ('Error execute');
        }

        $matchingData  = $req->fetchAll();

        /// Envoi de la réponse au Client
        deliver_response(200, "Succes. Voici les articles", $matchingData);
        //}
    break;

    /// Cas de la méthode POST
    case "POST" :
        //Vérifie si l'utilisateur à bien rentré ses logins et mot de passes dans l'URL
        if(!empty($_GET['login']) && !empty($_GET['password']))
        {
            $req = $linkpdo->prepare('SELECT Libellé FROM role
            INNER JOIN utilisateur
            ON role.Id_Role = utilisateur.Id_Role
            WHERE utilisateur.nom = :nom AND utilisateur.mdp = :mdp;');

            if ($req == false) {
                die ('Error preparation');
            }

            $req2 = $req->execute(array(
                "nom" => $_GET['login'],
                "mdp" => $_GET['password']
            ));

            if ($req2 == false) {
                $req->DebugDumpParams();
                die ('Error execute');
            }

            $matchingData  = $req->fetchAll();

            //print_r($matchingData[0][0]);

            //Si l'utilisateur n'est pas enregistré
            if(count($matchingData) == 0){
                /// Envoi de la réponse au Client
                deliver_response(401, "401 Opération refusée : Votre compte n'est pas enregistré dans notre base. Vous continurez en tant que non Authentifé", NULL);
            }
            //Si l'utilisateur n'est pas Publisher
            elseif($matchingData[0][0] == 'Moderator'){
                /// Envoi de la réponse au Client
                deliver_response(401, "401 Opération refusée : Vous êtes enregistré en tant que Moderator et non Publisher", NULL);
            }
            else{
                $username = $matchingData['nom'];
                $headers = array('alg'=>'HS256','typ'=>'JWT');
                $payload = array('nom'=>$username, 'exp'=>(time() + 60));

                $jwt = generate_jwt($headers, $payload);
                echo json_encode(array('token' => $jwt));
                /// Récupération des données envoyées par le Client
                $postedData = file_get_contents('php://input');
                
                /// Traitement

                $postedData = json_decode($postedData, true);

                var_dump($postedData);

                $matchingData  = idEnLogin($_GET['login']);

                //Seulement un test
                //print_r("L'id recherché : ", $matchingData[0][0]);

                $req = $linkpdo->prepare('INSERT INTO articles (titre, date_publi, Contenu, Id_Utilisateur) VALUES (:titre, CURRENT_TIMESTAMP, :contenu, :id)');

                if ($req == false) {
                    die ('Error preparation');
                }

                $req2 = $req->execute(array(
                    "contenu" => $postedData['Contenu'],
                    "titre" => $postedData['titre'],
                    "id" => $matchingData[0][0]
                ));

                if ($req2 == false) {
                    $req->DebugDumpParams();
                    die ('Error execute');
                }

                /// Envoi de la réponse au Client
                deliver_response(201, "Insertion réussie !", NULL);
            }
        }
        
    break;

    /// Cas de la méthode PUT
    case "PUT" :
        if(!empty($_GET['login']) && !empty($_GET['password']))
        {
            $req = $linkpdo->prepare('SELECT Libellé FROM role
            INNER JOIN utilisateur
            ON role.Id_Role = utilisateur.Id_Role
            WHERE utilisateur.nom = :nom AND utilisateur.mdp = :mdp;');

            if ($req == false) {
                die ('Error preparation');
            }

            $req2 = $req->execute(array(
                "nom" => $_GET['login'],
                "mdp" => $_GET['password']
            ));

            if ($req2 == false) {
                $req->DebugDumpParams();
                die ('Error execute');
            }

            $matchingData  = $req->fetchAll();

            //print_r($matchingData[0][0]);

            if(count($matchingData) == 0){
                /// Envoi de la réponse au Client
                deliver_response(401, "401 Opération refusée : Votre compte n'est pas enregistré dans notre base. Vous continurez en tant que non Authentifé", NULL);
            }
            else{
                if (!empty($_GET['Id_articles'])){
                    /// Traitement : Si Moderator, l'utilisateur ne peut pas modifier n'importe quel article
                    if($matchingData[0][0] == 'Moderator'){
                         /// Envoi de la réponse au Client
                         deliver_response(401, "Votre compte est enregistré en tant que moderator dans notre base. Impossible d'effectuer l'opération", NULL);
                    }
                    /// Traitement : Sinon, l'utilisateur peut modifier l'article si il lui appartient seulement
                    elseif ($matchingData[0][0] == 'Publisher') {
                        $req = $linkpdo->prepare('SELECT * FROM `articles`
                        INNER JOIN utilisateur
                        ON utilisateur.Id_Utilisateur = articles.Id_Utilisateur
                        WHERE Id_Articles = :Id_articles AND utilisateur.nom = :leLogin;');
        
                        if ($req == false) {
                            die ('Error preparation');
                        }
            
                        $req2 = $req->execute(array("Id_articles" => $_GET['Id_articles'],
                            "leLogin" => $_GET['login']));
            
                        if ($req2 == false) {
                            $req->DebugDumpParams();
                            die ('Error execute');
                        }

                        
                        $matchingData  = $req->fetchAll();

                        //Si il n'y a aucun article avec l'ID spécifié et le login associé
                        if(count($matchingData) == 0){
                            /// Envoi de la réponse au Client
                            deliver_response(401, "L'article ne vous appartient pas ! Impossible d'effectuer l'opération", NULL);
                        }
                        else{
                            /// Récupération des données envoyées par le Client
                            $postedData = file_get_contents('php://input');

                            $postedData = json_decode($postedData, true);

                            var_dump($postedData);

                            $matchingData  = idEnLogin($_GET['login']);

                            $req = $linkpdo->prepare('UPDATE articles SET titre = :titre, date_publi = CURRENT_TIMESTAMP, Contenu = :contenu, Id_Utilisateur = :id WHERE Id_articles=:Id_articles');

                            if ($req == false) {
                                die ('Error preparation');
                            }

                            $req2 = $req->execute(array("contenu" => $postedData['Contenu'],
                                                        "Id_articles" => $_GET['Id_articles'],
                                                        "titre" => $postedData['titre'],
                                                        "id" => $matchingData[0][0]));

                            if ($req2 == false) {
                                $req->DebugDumpParams();
                                die ('Error execute');
                            }
                            
                            /// Traitement
                            /// Envoi de la réponse au Client
                            deliver_response(200, "Modification de l'article réussie !", NULL);
                        }
                    }
                    else {
                        /// Envoi de la réponse au Client
                        deliver_response(401, "Utilisateur non authentifié, opération refusée", NULL);
                    }
                }
            }
        }
        else{
            /// Sinon refus
            deliver_response(401, "Vous n'êtes pas Authentifé. Opération refusée !", NULL);
        }
    break;

    /// Cas de la méthode DELETE
    case "DELETE" :
        /// Si la récupération du login et du mot de passe n'est pas vide
        if(!empty($_GET['login']) && !empty($_GET['password']))
        {
            $req = $linkpdo->prepare('SELECT Libellé FROM role
            INNER JOIN utilisateur
            ON role.Id_Role = utilisateur.Id_Role
            WHERE utilisateur.nom = :nom AND utilisateur.mdp = :mdp;');

            if ($req == false) {
                die ('Error preparation');
            }

            $req2 = $req->execute(array(
                "nom" => $_GET['login'],
                "mdp" => $_GET['password']
            ));

            if ($req2 == false) {
                $req->DebugDumpParams();
                die ('Error execute');
            }

            $matchingData  = $req->fetchAll();

            //print_r($matchingData[0][0]);

            if(count($matchingData) == 0){
                /// Envoi de la réponse au Client
                deliver_response(401, "401 Opération refusée : Votre compte n'est pas enregistré dans notre base. Vous continurez en tant que non Authentifé", NULL);
            }
            else{
                if (!empty($_GET['Id_articles'])){
                    /// Traitement : Si Moderator, l'utilisateur peut supprimer n'importe quel article
                    if($matchingData[0][0] == 'Moderator'){
                        $req = $linkpdo->prepare('DELETE FROM articles WHERE Id_articles=:Id_articles;');
        
                        if ($req == false) {
                            die ('Error preparation');
                        }
            
                        $req2 = $req->execute(array("Id_articles" => $_GET['Id_articles']));
            
                        if ($req2 == false) {
                            $req->DebugDumpParams();
                            die ('Error execute');
                        }

                        /// Envoi de la réponse au Client
                        deliver_response(200, "articles supprimé !", NULL);
                    }
                    /// Traitement : Sinon, l'utilisateur peut supprimer l'article si il lui appartient seulement
                    elseif ($matchingData[0][0] == 'Publisher') {
                        $req = $linkpdo->prepare('SELECT * FROM `articles`
                        INNER JOIN utilisateur
                        ON utilisateur.Id_Utilisateur = articles.Id_Utilisateur
                        WHERE Id_Articles = :Id_articles AND utilisateur.nom = :leLogin;');
        
                        if ($req == false) {
                            die ('Error preparation');
                        }
            
                        $req2 = $req->execute(array("Id_articles" => $_GET['Id_articles'],
                            "leLogin" => $_GET['login']));
            
                        if ($req2 == false) {
                            $req->DebugDumpParams();
                            die ('Error execute');
                        }

                        
                        $matchingData  = $req->fetchAll();

                        //Si il n'y a aucun article avec l'ID spécifié et le login associé
                        if(count($matchingData) == 0){
                            /// Envoi de la réponse au Client
                            deliver_response(401, "Votre compte n'est pas enregistré en tant que moderator dans notre base. Impossible d'effectuer l'opération", NULL);
                        }
                        else{
                            $req = $linkpdo->prepare('DELETE FROM articles WHERE Id_articles=:Id_articles;');
            
                            if ($req == false) {
                                die ('Error preparation');
                            }
                
                            $req2 = $req->execute(array("Id_articles" => $_GET['Id_articles']));
                
                            if ($req2 == false) {
                                $req->DebugDumpParams();
                                die ('Error execute');
                            }

                            /// Envoi de la réponse au Client
                            deliver_response(200, "articles supprimé !", NULL);
                        }
                    }
                    else {
                        /// Envoi de la réponse au Client
                        deliver_response(401, "Utilisateur non authentifié, opération refusée", NULL);
                    }

                    
                }
            }
        }
        else{
            /// Sinon refus
            deliver_response(401, "Vous n'êtes pas Authentifé. Opération refusée !", NULL);
        }
    break;
}

/// Envoi de la réponse au Client
function deliver_response($status, $status_message, $data){
    /// Paramétrage de l'entête HTTP, suite
   header("HTTP/1.1 $status $status_message");
   /// Paramétrage de la réponse retournée
   $response['status'] = $status;
   $response['status_message'] = $status_message;
   $response['data'] = $data;
   /// Mapping de la réponse au format JSON
   $json_response = json_encode($response);
   echo $json_response;
}

//Transformation de l'ID de l'utilisateur pour récupérer son login pour son l'insertion dans la base de données
function idEnLogin($login){
    try {
        $linkpdo = new PDO($mySqlConnection, $user, $pwd);
    }
    catch (Exception $e) {
        die('Error : ' . $e->getMessage());
    }
    
    $req = $linkpdo->prepare('SELECT Id_Utilisateur FROM utilisateur WHERE nom=:monLogin');

    if ($req == false) {
        die ('Error preparation');
    }

    $req2 = $req->execute(array(
        "monLogin" => $login
    ));

    if ($req2 == false) {
        $req->DebugDumpParams();
        die ('Error execute');
    }

    return $req->fetchAll();
}

/*function liker_disliker($like, $idArticle, $idUser){
    try {
        $linkpdo = new PDO($mySqlConnection, $user, $pwd);
    }
    catch (Exception $e) {
        die('Error : ' . $e->getMessage());
    }
    
    $req = $linkpdo->prepare('INSERT INTO like_dislikearticles VALUES ("", :likes, :idUser, :id)');

    if ($req == false) {
        die ('Error preparation');
    }

    $req2 = $req->execute(array(
        "monLogin" => $login
    ));

    if ($req2 == false) {
        $req->DebugDumpParams();
        die ('Error execute');
    }

    return $req->fetchAll();
    //INSERT INTO like_dislikearticles VALUES ("", 0, 2, 1);
}*/
?>

