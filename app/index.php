<?php
include "vendor/autoload.php";
include "secrets.php";
if (array_key_exists("log_out", $_GET) && $_GET["log_out"] == 1) {
    unset($_SESSION["user_id"]);
}

$tasks = pg_query($db, "SELECT * FROM challenges ORDER BY id ASC");
$is_admin = false; // default value
if (!empty($_SESSION["user_id"])) {
    $userid = $_SESSION["user_id"];
    $user_query = pg_query_params($db, "SELECT * FROM users WHERE sub=$1", [$userid]);
    $user = pg_fetch_row($user_query, null, PGSQL_ASSOC);
    if (!$user) {
        header("Location: /profile.php");
        exit();
    }
    $is_admin = $user["is_admin"] == "t";
}
?>
<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Главная страница</title>
    <link rel="stylesheet" href="/css/challenges.css">
    <link rel="stylesheet" href="/css/general.css">
</head>

<body>
    <h1>Задачи</h1>
    <?php
    if (empty($userid)) {
        $host = htmlspecialchars($_SERVER['HTTP_HOST']);
        $domain = parse_url($_SERVER['HTTP_HOST'], PHP_URL_HOST);
        $scheme = $domain == "localhost" ? "http" : "https";
        echo "<h3>Войдите в аккаунт, чтобы видеть решения!</h3>";
        echo <<<EOF
        <script src="https://accounts.google.com/gsi/client" async></script>
        <div
            id="g_id_onload"
            data-client_id="$google_clientid"
            data-ux_mode="redirect"
            data-login_uri="$scheme://$host/sign_in.php"
        ></div>
        <div class="sign_in_button"><div class="g_id_signin"></div></div>
        EOF;
    }
    ?>
    <main class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Номер</th>
                    <th>Название</th>
                    <th>Решено?</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach (pg_fetch_all($tasks) as $row) {
                    $id = $row["id"];
                    $edit_link = $is_admin ? " (<a href=\"view_challenge.php?id=$id&edit=1\">редактировать</a>)" : "";
                    $name = $row["name"];
                    if (!empty($user)) {
                        $solved_query = pg_query_params($db, "SELECT 1 FROM solutions WHERE sub=$1 AND challenge=$2 AND all_passed=true LIMIT 1;", [$userid, $id]);
                        $solved = pg_num_rows($solved_query) > 0; // any passing solutions found
                        $is_solved = $solved ? "+" : "-";
                    } else {
                        $is_solved = "?";
                    }
                    echo <<<EOF
                    <tr>
                    <td><a href="view_challenge.php?id=$id">$id</a>$edit_link</td>
                    <td>$name</td>
                    <td>$is_solved</td>
                    </tr>
                    EOF;
                } ?>
            </tbody>
        </table>
    </main>
    <?php
    if (!empty($userid)) {
        echo <<<EOF
            <nav>
            <a href="/profile.php">Редактор профиля</a><br>
            <a href="index.php?log_out=1">Выйти из аккаунта</a><br>
            <a href="status.php">Мои решения</a><br>
            EOF;
        if ($is_admin) {
            $new_task_id = intval($id) + 1;
            echo <<<EOF
                <br>
                <a href="view_challenge.php?id=$new_task_id&edit=1">Добавить задачу</a>
                <br>
                <a href="solved_challenges.php">Решённые задачи</a>
                EOF;
        }
        echo "</nav>";
    }
    ?>
</body>

</html>