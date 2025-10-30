<?php
require_once 'secrets.php';
$userid = $_SESSION["user_id"];
if ($userid) {
    $user_query = pg_query_params($db, "SELECT is_admin FROM users WHERE sub=$1", [$userid]);
    $user = pg_fetch_row($user_query, null, PGSQL_ASSOC);
    if (!$user) {
        header("Location: /profile.php");
        exit();
    }
    $is_admin = $user["is_admin"] == "t";
    if (!$is_admin) {
        header("Location: /");
        exit();
    }
}
?>
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
    require_once "vendor/autoload.php";
    require_once "secrets.php";

    if ($_POST["delete"] == "on") {
        $result = pg_query_params($db, "DELETE FROM challenges WHERE id=$1", [$_POST["id"]]);
        $rows = pg_affected_rows($result);
        if ($rows) {
            echo "Задача удалена!<br>";
        }
        else {
            echo "Ошибка при удалении задачи!<br>";
        }
    } else {
        $result = pg_query_params($db, <<<EOF
        INSERT INTO challenges (id, "name", "text", tests) VALUES ($1, $2, $3, $4)
        ON CONFLICT (id) DO UPDATE SET
        "name" = EXCLUDED."name",
        "text" = EXCLUDED."text",
        tests = EXCLUDED.tests;
        EOF, [$_POST["id"], $_POST["name"], $_POST["text"], $_POST["tests"]]);
        $rows = pg_affected_rows($result);
        if ($rows) {
            echo "Задача сохранена!<br>";
        }
        else {
            echo "Ошибка при сохранении задачи!<br>";
        }
    }
    ?>
</body>

</html>