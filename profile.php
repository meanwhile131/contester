<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Главная страница</title>
    <script src="https://accounts.google.com/gsi/client"></script>
    <link rel="stylesheet" href="/css/general.css">
</head>

<body>
    <?php
    include "vendor/autoload.php";
    include "database.php";

    $CLIENT_ID = "";
    $client = new Google_Client(["466834063559-e8ntnvvptcbbdp70ovb3v1m4h8qm3c8i.apps.googleusercontent.com" => $CLIENT_ID]);
    try {
        $token = $client->verifyIdToken($_COOKIE["token"]);
    } catch (LogicException) {
        echo <<<EOF
        <p>Войдите в аккаунт!</p>
        <script src="https://accounts.google.com/gsi/client"></script>
        <script src="/js/auth.js"></script>
        EOF;
        exit();
    }

    if (array_key_exists("register", $_POST)) {
        $query = $db->prepare("REPLACE INTO `users` (`id`, `first_name`, `second_name`, `third_name`, `group`) VALUES (?, ?, ?, ?, ?)");

        if ($query->execute([$token["sub"], $_POST["first_name"], $_POST["second_name"], $_POST["third_name"], $_POST["group"]])) {
            header('Location: ' . "/");
            exit();
        } else {
            echo "<p>Ошибка при выполнении операции!</p>";
        }
        exit();
    }

    echo <<<EOF
        <form action="/profile.php" method="post">
            <label>Фамилия:
                <input required type="text" name="second_name" value="{$token["family_name"]}">
            </label>
            <br>
            <label>Имя:
                <input required type="text" name="first_name" value="{$token["given_name"]}">
            </label>
            <br>
            <label>Отчество:
                <input required type="text" name="third_name">
            </label>
            <br>
            <label>Группа:
                <label><input required type="radio" name="group" value="211">211</label>
                <label><input required type="radio" name="group" value="212">212</label>
                <label><input required type="radio" name="group" value="231">231</label>
                <label><input required type="radio" name="group" value="241">241</label>
            </label>
            <br>
            <input type="hidden" name="register" value="1">
            <input type="hidden" name="token" value="{$_GET["token"]}">
            <button type="submit">Редактировать профиль</button>
        </form>
    EOF;
    ?>
</body>

</html>