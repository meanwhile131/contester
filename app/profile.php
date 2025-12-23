<?php
include "vendor/autoload.php";
include "secrets.php";

if (!$_SESSION["user_id"]) {
    header('Location: /');
    exit();
}
if ($_SERVER['REQUEST_METHOD'] === "POST") {
    if (!empty($_POST["first_name"]) && !empty($_POST["second_name"]) && !empty($_POST["third_name"]) && in_array($_POST["group"], ["211", "212", "231", "241"])) { 
        $result = pg_query_params($db, <<<EOF
            INSERT INTO users (sub,first_name,second_name,third_name,"group") VALUES ($1, $2, $3, $4, $5)
            ON CONFLICT (sub) DO UPDATE SET 
                first_name = EXCLUDED.first_name,
                second_name = EXCLUDED.second_name,
                third_name = EXCLUDED.third_name,
                "group" = EXCLUDED."group";
            EOF, [$_SESSION["user_id"], $_POST["first_name"], $_POST["second_name"], $_POST["third_name"], $_POST["group"]]);
        if ($result) {
            $success = true;
        }
        else {
            $success = false;
        }
    }
    else {
        $success = false;
    }
}
?>
<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Редактор профиля</title>
    <link rel="stylesheet" href="/css/general.css">
    <link rel="stylesheet" href="/css/profile.css">
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
    if (isset($success)) {
        if ($success) {
            echo "<h4 class=\"success_message\">Профиль успешно изменен.</h4>";
        }
        else {
            echo "<h4 class=\"error_message\">Ошибка сохранения профиля.</h4>";
        }
    }
    echo <<<EOF
        <form action="/profile.php" method="post">
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