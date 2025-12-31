<?php
header('X-Accel-Buffering: no');
require_once 'secrets.php';
$userid = $_SESSION["user_id"];
if ($userid) {
    $user_query = pg_query_params($db, "SELECT is_admin FROM users WHERE sub=$1", [$userid]);
    $user = pg_fetch_row($user_query, null, PGSQL_ASSOC);
    if (!$user) {
        header("Location: /profile.php");
        exit();
    }
}
else {
    header("Location: /");
    exit();
}
?>
<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Проверка решения</title>
    <link rel="stylesheet" href="/css/general.css">
</head>

<body>
    <?php
    ob_implicit_flush(1);
    $challenge_id = $_POST["id"];
    $code = $_POST["code"];
    if (empty($challenge_id) || empty($code)) {
        echo "Нет всех параметров запроса!";
        exit();
    }
    $challenge_id_safe = htmlspecialchars($challenge_id);
    echo "Задача: $challenge_id_safe<br>";
    pg_query($db, "BEGIN;");
    $result = pg_query_params($db, "INSERT INTO solutions(challenge,code,sub) VALUES ($1,$2,$3);", [$challenge_id, $code, $userid]);
    if (pg_affected_rows($result) == 0) {
        pg_query($db, "ROLLBACK");
        echo "Ошибка при добавлении задачи в очередь!";
        exit();
    }
    pg_query($db, "NOTIFY solutions;");
    pg_query($db, "COMMIT;");
    echo "Задача добавлена в очередь";
    ?>
</body>

</html>