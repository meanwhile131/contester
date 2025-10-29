<?php
include "vendor/autoload.php";
include "secrets.php";

if (!$_SESSION["user_id"]) {
    header('Location: /');
    exit();
}
?>
<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Редактор профиля</title>
    <link rel="stylesheet" href="/css/general.css">
    <style>
        .success_message {
            color: green;
            text-align: center;
        }
    </style>
</head>

<body>
    <?php
    $user_query = pg_query_params($db, "SELECT first_name,second_name,third_name,\"group\" FROM users WHERE sub=$1;", [$_SESSION["user_id"]]);
    $user = pg_fetch_row($user_query, null, PGSQL_ASSOC);
    if ($user) {
        $first_name = htmlspecialchars($user["first_name"]);
        $second_name = htmlspecialchars($user["second_name"]);
        $third_name = htmlspecialchars($user["third_name"]);
        $group = htmlspecialchars($user["group"]);
        $group_211 = ($group == 211) ? "checked" : "";
        $group_212 = ($group == 212) ? "checked" : "";
        $group_231 = ($group == 231) ? "checked" : "";
        $group_241 = ($group == 241) ? "checked" : "";
    }
    if (isset($_GET["success"])) {
        echo "<h4 class=\"success_message\">Профиль успешно изменен.</h4>";
    }
    echo <<<EOF
        <form action="/save_profile.php" method="post">
            <label>Фамилия:
                <input required type="text" name="second_name" value="$second_name">
            </label>
            <br>
            <label>Имя:
                <input required type="text" name="first_name" value="$first_name">
            </label>
            <br>
            <label>Отчество:
                <input required type="text" name="third_name" value="$third_name">
            </label>
            <br>
            <label>Группа:
                <label><input required type="radio" name="group" value="211" $group_211>211</label>
                <label><input required type="radio" name="group" value="212" $group_212>212</label>
                <label><input required type="radio" name="group" value="231" $group_231>231</label>
                <label><input required type="radio" name="group" value="241" $group_241>241</label>
            </label>
            <br>
            <button type="submit">Сохранить профиль</button>
        </form>
    EOF;
    ?>
    <a href="/" class="centered-a">Вернуться на главную страницу</a>
</body>

</html>