<?php
///http://www.kilya.biz/api/chuckn_facts.php
/// Librairies éventuelles (pour la connexion à la BDD, etc.)
 //include('mylib.php');
 
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
        /// Récupération des critères de recherche envoyés par le Client
        /*if (!empty($_GET['phrase_a_afficher'])){
        /// Traitement

            if(!empty($_GET['voteSet'])){
                $req = $linkpdo->prepare('SELECT * FROM chuckn_facts 
                ORDER BY vote DESC 
                LIMIT :nb;');
            }
            else{
                $req = $linkpdo->prepare('SELECT * FROM chuckn_facts 
                ORDER BY date_ajout DESC 
                LIMIT :nb;');
            }

            if ($req == false) {
                die ('Error preparation');
            }

            $req->bindValue(':nb', (int) $_GET['phrase_a_afficher'], PDO::PARAM_INT);

            $req2 = $req->execute();

            if ($req2 == false) {
                $req->DebugDumpParams();
                die ('Error execute');
            }

            $matchingData  = $req->fetchAll();

            /// Envoi de la réponse au Client
            deliver_response(200, "Succes. Voici les phrases de Chuck Norris !", $matchingData);
        }
        else {*/
        $req = $linkpdo->prepare('SELECT * FROM article;');

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
        deliver_response(200, "Succes. Voici les article", $matchingData);
        //}
    break;

    /// Cas de la méthode POST
    case "POST" :
        /// Récupération des données envoyées par le Client
        $postedData = file_get_contents('php://input');
        
        /// Traitement

        $postedData = json_decode($postedData, true);

        var_dump($postedData);

        //echo $postedData['phrase'];

        $req = $linkpdo->prepare('INSERT INTO article (date_publication, contenu, Auteur) VALUES (:date_publication, :contenu, :Auteur)');

        if ($req == false) {
            die ('Error preparation');
        }

        $req2 = $req->execute(array(
            "date_publication" => $postedData['date_publication'],
            "contenu" => $postedData['contenu'],
            "Auteur" => $postedData['Auteur']
        ));

        if ($req2 == false) {
            $req->DebugDumpParams();
            die ('Error execute');
        }

        /// Envoi de la réponse au Client
        deliver_response(201, "Insertion réussie !", NULL);
    break;

    /// Cas de la méthode PUT
    case "PUT" :
        /// Récupération des données envoyées par le Client
        $postedData = file_get_contents('php://input');

        $postedData = json_decode($postedData, true);

        var_dump($postedData);

        $req = $linkpdo->prepare('UPDATE Article SET contenu=:contenu, date_publication = CURRENT_TIMESTAMP, Auteur = :Auteur WHERE Id_article=:Id_article');

        if ($req == false) {
            die ('Error preparation');
        }

        $req2 = $req->execute(array("contenu" => $postedData['contenu'],
                                    "Id_article" => $_GET['Id_article'],
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
        /// Récupération de l'identifiant de la ressource envoyé par le Client
        if (!empty($_GET['Id_article'])){
            /// Traitement
            $req = $linkpdo->prepare('DELETE FROM Article WHERE Id_article=:Id_article;');

            if ($req == false) {
                die ('Error preparation');
            }

            $req2 = $req->execute(array("Id_article" => $_GET['Id_article']));

            if ($req2 == false) {
                $req->DebugDumpParams();
                die ('Error execute');
            }
        }

        /// Envoi de la réponse au Client
        deliver_response(200, "Votre message", NULL);
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
   