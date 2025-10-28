<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Регистрация</title>
    <link rel="stylesheet" href="/css/general.css">
</head>

<body>
    <?php
    include "vendor/autoload.php";
    include "database.php";

    

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