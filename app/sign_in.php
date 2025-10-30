<?php
require_once 'vendor/autoload.php';
require_once 'secrets.php';
$client = new Google_Client(['client_id' => $google_clientid]);
$payload = $client->verifyIdToken($_POST["credential"]);
if (!$payload) {
    header('Location: /');
    exit();
}
$userid = $payload['sub'];
$_SESSION["user_id"] = $userid;
$user_exists_query = pg_query_params($db, "SELECT 1 FROM users WHERE sub=$1;", [$userid]);
$user_exists = pg_num_rows($user_exists_query);
if ($user_exists) {
    header('Location: /');
    exit();
}
header("Location: /profile.php");
?>