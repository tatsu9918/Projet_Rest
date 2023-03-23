<?php
 /// Paramétrage de l'entête HTTP (pour la réponse au Client)
 header("Content-Type:application/json");
 
 /// Identification du type de méthode HTTP envoyée par le client
 $http_method = $_SERVER['REQUEST_METHOD'];



try {
    $linkpdo = new PDO("mysql:host=localhost;dbname=projet_rest", 'root', '');
}
catch (Exception $e) {
    die('Error : ' . $e->getMessage());
}

 switch ($http_method){
    /// Cas de la méthode GET
    case "GET" :
        $req = $linkpdo->prepare('SELECT titre, Contenu, date_publi, utilisateur.nom AS Auteur FROM articles, utilisateur;');

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
            elseif($matchingData[0][0] == 'Moderator'){
                /// Envoi de la réponse au Client
                deliver_response(401, "401 Opération refusée : Vous êtes enregistré en tant que Moderator et non Publisher", NULL);
            }
            else{
                /// Récupération des données envoyées par le Client
                $postedData = file_get_contents('php://input');
                
                /// Traitement

                $postedData = json_decode($postedData, true);

                var_dump($postedData);

                //echo $postedData['phrase'];

                $req = $linkpdo->prepare('SELECT Id_Utilisateur FROM utilisateur WHERE nom=:monLogin');

                if ($req == false) {
                    die ('Error preparation');
                }

                $req2 = $req->execute(array(
                    "monLogin" => $_GET['login']
                ));

                if ($req2 == false) {
                    $req->DebugDumpParams();
                    die ('Error execute');
                }

                $matchingData  = $req->fetchAll();

                print_r("L'id recherché : ", $matchingData[0][0]);

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
        /// Récupération des données envoyées par le Client
        $postedData = file_get_contents('php://input');

        $postedData = json_decode($postedData, true);

        var_dump($postedData);

        $req = $linkpdo->prepare('UPDATE articles SET contenu=:contenu, date_publication = CURRENT_TIMESTAMP, Auteur = :Auteur WHERE Id_articles=:Id_articles');

        if ($req == false) {
            die ('Error preparation');
        }

        $req2 = $req->execute(array("contenu" => $postedData['contenu'],
                                    "Id_articles" => $_GET['Id_articles'],
                                    "Auteur" => $postedData['Auteur']));

        if ($req2 == false) {
            $req->DebugDumpParams();
            die ('Error execute');
        }
        
        /// Traitement
        /// Envoi de la réponse au Client
        deliver_response(200, "Votre message", NULL);
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
                    /// Traitement
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

    case "PATCH":
        /// Récupération des données envoyées par le Client
        $postedData = file_get_contents('php://input');

        $postedData = json_decode($postedData, true);

        var_dump($postedData);

        if(!empty($_GET['Signaler'])){
            $req = $linkpdo->prepare('SELECT id FROM chuckn_facts WHERE signalement = 1');

            if ($req == false) {
                die ('Error preparation');
            }

            $req2 = $req->execute();

            if ($req2 == false) {
                $req->DebugDumpParams();
                die ('Error execute');
            }

            $matchingData = $req->fetchAll();

            echo $matchingData[0][0];
            print_r($matchingData);

            if(!array_search($_GET['id'], $matchingData)){
                $req = $linkpdo->prepare('UPDATE chuckn_facts SET signalement = :sign WHERE id=:id');

                if ($req == false) {
                    die ('Error preparation');
                }

                $req2 = $req->execute(array("sign" => $_GET['Signaler'],
                                            "id" => $_GET['id']));

                if ($req2 == false) {
                    $req->DebugDumpParams();
                    die ('Error execute');
                }
            }
            else{
                $req = $linkpdo->prepare('UPDATE chuckn_facts SET signalement = 0 WHERE id=:id');

                if ($req == false) {
                    die ('Error preparation');
                }

                $req2 = $req->execute(array("sign" => $_GET['Signaler'],
                                            "id" => $_GET['id']));

                if ($req2 == false) {
                    $req->DebugDumpParams();
                    die ('Error execute');
                }
            }
        }
        else{
            $req = $linkpdo->prepare('UPDATE chuckn_facts SET vote = vote + :vote WHERE id=:id');

            if ($req == false) {
                die ('Error preparation');
            }
    
            $req2 = $req->execute(array("vote" => $_GET['vote'],
                                        "id" => $_GET['id']));
    
            if ($req2 == false) {
                $req->DebugDumpParams();
                die ('Error execute');
            }
        }    
        /// Traitement
        /// Envoi de la réponse au Client
        deliver_response(200, "Votre message", NULL);
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
?>
   