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
        } else {
            echo "Ошибка при удалении задачи!<br>";
        }
    } else {
        pg_query($db, "BEGIN");
        $result = pg_query_params($db, <<<EOF
        INSERT INTO challenges (id, "name", "text") VALUES ($1, $2, $3)
        ON CONFLICT (id) DO UPDATE SET
        "name" = EXCLUDED."name",
        "text" = EXCLUDED."text";
        EOF, [$_POST["id"], $_POST["name"], $_POST["text"]]);

        if (pg_affected_rows($result) != 1) {
            pg_query($db, "ROLLBACK");
            echo "Ошибка при сохранении задачи!<br>";
            exit();
        }

        pg_query_params($db, "DELETE FROM tests WHERE challenge=$1", [$_POST["id"]]);

        $result = pg_query_params($db, <<<EOF
            INSERT INTO tests (challenge, "in", "out")
            SELECT $1, "in", "out"
            FROM json_to_recordset($2) AS x("in" text, "out" text);
            EOF, [$_POST["id"], $_POST["tests"]]);
        if (!$result) {
            pg_query($db, "ROLLBACK");
            echo "Ошибка при сохранении тестов!<br>";
            exit();
        }

        pg_query($db, "COMMIT");
        echo "Задача сохранена!<br>";
    }
    ?>
</body>

</html>