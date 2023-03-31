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

$linkpdo = connectionBDD($mySqlConnection, $user, $pwd);
if ($http_method == "POST") {
    //Vérifie si l'utilisateur à bien rentré ses logins et mot de passes dans l'UR
    if (!empty($_GET['login']) && !empty($_GET['password'])) {
        $req = $linkpdo->prepare('SELECT Libellé FROM role
        INNER JOIN utilisateur
        ON role.Id_Role = utilisateur.Id_Role
        WHERE utilisateur.nom = :nom AND utilisateur.mdp = :mdp;');

        if ($req == false) {
            die('Error preparation');
        }

        $req2 = $req->execute(
            array(
                "nom" => $_GET['login'],
                "mdp" => $_GET['password']
            )
        );

        if ($req2 == false) {
            $req->DebugDumpParams();
            die('Error execute');
        }

        $matchingData = $req->fetchAll();

        //print_r($matchingData[0][0]);

        //Si l'utilisateur n'est pas enregistré
        if (count($matchingData) == 0) {
            /// Envoi de la réponse au Client
            deliver_response(401, "401 Opération refusée : Votre compte n'est pas enregistré dans notre base. Vous continurez en tant que non Authentifé", NULL);
        }
        //Si l'utilisateur n'est pas Publisher
        else {
            $username = $_GET['login'];
            $password = $_GET['password'];
            $role = $matchingData[0][0];
            $headers = array('alg' => 'HS256', 'typ' => 'JWT');
            $payload = array('nom' => $username, 'mdp' => $password, 'role' => $role, 'exp' => (time() + 600));
            $jwt = generate_jwt($headers, $payload);
            echo json_encode(array('token' => $jwt));
            deliver_response(201, "Token bien généré !", $jwt);
        }

            } else {
                deliver_response(403, "Erreur le token n'est pas généré",NULL);
            }

    if ($http_method == "GET") {
        $JWT = get_bearer_token();
                $validity = is_jwt_valid($JWT);
                $data=get_payload_token($JWT);
                if($validity){
                    deliver_response(201, "Le Token JWT est valide", $validity);
                    deliver_response(201, "Le Token JWT est valide", $data);
                } else {
                    deliver_response(402, "Le Token JWT est invalide",$validity);
                }
            }
}
function deliver_response($status, $status_message, $data)
{
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
function connectionBDD($mySqlConnection, $user, $pwd)
{
    try {
        $linkpdo = new PDO($mySqlConnection, $user, $pwd);
    } catch (Exception $e) {
        die('Error : ' . $e->getMessage());
    }

    return $linkpdo;
}
?>