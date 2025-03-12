<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Сохранение задачи</title>
    <link rel="stylesheet" href="/css/general.css">
</head>

<body>
    <?php
    header('X-Accel-Buffering: no');
    ob_implicit_flush(1);
    ini_set("display_errors", "1");
    ini_set("display_startup_errors", "1");
    error_reporting(E_ALL);
    require_once "vendor/autoload.php";
    include "database.php";
    echo "Аутентификация...<br>";
    ob_flush();
    $CLIENT_ID = "";
    $client = new Google_Client(["466834063559-e8ntnvvptcbbdp70ovb3v1m4h8qm3c8i.apps.googleusercontent.com" => $CLIENT_ID]);
    try {
        $token = $client->verifyIdToken($_COOKIE["token"]);
    } catch (LogicException) {
        echo <<<EOF
        <p>Неправильный токен аутентификации!</p>
        EOF;
        exit();
    }
    $admin = $token["email"] == "sashachernyakov111111@gmail.com" || $token["email"] == "nadezhdasergeeva77@gmail.com" || $token["email"] == "nadezhdasergeeva77@gmail.com";

    if ($_POST["delete"] == "on") {
        echo "Удаление задачи...<br>";
        ob_flush();
        $query = $db->prepare("DELETE FROM `challenges` WHERE id=?");
        $query->execute([$_POST["id"]]);
        echo "Задача удалена!<br>";
        ob_flush();
    } else {
        echo "Сохранение задачи...<br>";
        ob_flush();
        $query = $db->prepare("REPLACE INTO `challenges` (`id`, `name`, `text`, `tests`) VALUES (?, ?, ?, ?)");
        $query->execute([$_POST["id"], $_POST["name"], $_POST["text"], $_POST["tests"]]);
        echo "Задача сохранена!<br>";
        ob_flush();
    }
    ?>
</body>

</html>