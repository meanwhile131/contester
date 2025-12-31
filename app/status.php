<?php
include "secrets.php";

$is_admin = false; // default value
$userid = $_SESSION["user_id"];
if ($userid) {
    $user_query = pg_query_params($db, "SELECT is_admin FROM users WHERE sub=$1", [$userid]);
    $user = pg_fetch_row($user_query, null, PGSQL_ASSOC);
    if (!$user) {
        header("Location: /profile.php");
        exit();
    }
    $is_admin = $user["is_admin"] == "t";
} else {
    header("Location: /");
    exit();
}
$solutions = pg_query_params($db, 'SELECT id,challenge,"status",all_passed FROM solutions WHERE sub=$1 ORDER BY id DESC', [$userid]);
?>
<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Мои решения</title>
    <link rel="stylesheet" href="/css/challenges.css">
    <link rel="stylesheet" href="/css/general.css">
</head>

<body>
    <h1>Мои решения</h1>
    <?php
    ?>
    <main class="table-container">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Задача</th>
                    <th>Статус</th>
                    <th>Правильное?</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach (pg_fetch_all($solutions) as $solution) {
                    $id = $solution["id"];
                    $challenge = $solution["challenge"];
                    $status = match ($solution["status"]) {
                        'submitted' => "Отправлено",
                        'queued' => "В очереди",
                        'testing' => "Проверяется",
                        'done' => "Проверено"
                    };
                    if ($solution["status"] == "done") {
                        $all_passed = $solution["all_passed"] == "t" ? "+" : "-";
                    } else {
                        $all_passed = "";
                    }
                    echo <<<EOF
                    <tr>
                    <td>$id</td>
                    <td><a href="view_challenge.php?id=$challenge">$challenge</a></td>
                    <td>$status</td>
                    <td>$all_passed</td>
                    </tr>
                    EOF;
                } ?>
            </tbody>
        </table>
    </main>
    <nav><a href="/" class="centered-a">Вернуться на главную страницу</a></nav>
</body>

</html>