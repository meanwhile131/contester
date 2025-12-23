<?php
include "vendor/autoload.php";
include "secrets.php";
$userid = $_SESSION["user_id"];
if ($userid) {
    $user_query = pg_query_params($db, "SELECT * FROM users WHERE sub=$1", [$userid]);
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
    <title>Главная страница</title>
    <link rel="stylesheet" href="/css/general.css">
    <link rel="stylesheet" href="/css/solved_challenges.css">
</head>

<body>
    <?php
    error_reporting(E_ALL);
    ini_set('display_errors', 'On');
    $users_query = pg_query($db, "SELECT * FROM users");
    $users = pg_fetch_all($users_query);
    $challenges_query = pg_query($db, "SELECT id FROM challenges ORDER BY id ASC");
    $challenges = pg_fetch_all($challenges_query);
    ?>
    <h1>Решённые задачи</h1>
    <table>
        <thead>
            <tr>
                <th>Фамилия</th>
                <th>Имя</th>
                <th>Группа</th>
                <?php
                foreach ($challenges as $_ => $challenge) {
                    $id = $challenge["id"];
                    echo "<th>{$id}</th>";
                }
                ?>
            </tr>
        </thead>
        <tbody>
            <?php
            foreach ($users as $_ => $user) {
                echo <<<EOF
                <tr>
                    <td>{$user["second_name"]}</td>
                    <td>{$user["first_name"]}</td>
                    <td>{$user["group"]}</td>
                EOF;
                foreach ($challenges as $_ => $challenge) {
                    $id = intval($challenge["id"]) - 1;
                    $solved = intval($user["tasks_solved"]) & 1 << $id ? "+" : "-";
                    echo "<td>$solved</td>";
                }
            }
            ?>
        </tbody>
    </table>
</body>

</html>