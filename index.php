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
    <?php
    // ini_set('display_errors', '1');
    // ini_set('display_startup_errors', '1');
    // error_reporting(E_ALL);
    include "vendor/autoload.php";
    include "database.php";

    try {
        $token = $client->verifyIdToken($_COOKIE["token"]);
        $logged_in = true;
    } catch (LogicException) {
        echo <<<EOF
        <script src="https://accounts.google.com/gsi/client"></script>
        <script src="js/auth.js"></script>
        EOF;
    }
    $tasks = pg_query($db, "SELECT * FROM challenges");
    if ($logged_in) {
        $admin = $token["email"] == "sashachernyakov111111@gmail.com" || $token["email"] == "nadezhdasergeeva77@gmail.com";
        $profilequery = $db->prepare("SELECT * FROM `users` WHERE id=?");
        $profilequery->execute([$token["sub"]]);
        $profile = $profilequery->get_result()->fetch_assoc();
        // $solved_tasks = $db->prepare("SELECT `tasks` FROM `users` WHERE id=?");
        // $solved_tasks->execute([$token["sub"]]);
        // $solved_tasks = intval($solved_tasks->get_result()->fetch_row()[0]);
    }
    ?>

    <h1>Задачи</h1>
    <?php if (!$logged_in) {
        echo "<h3>Войдите в аккаунт, чтобы видеть решения!</h3>";
    }
    elseif (!$profile) {
        echo "<h3>Сначала укажите своё ФИО и группу в <a href=\"profile.php\">редакторе профиля, чтобы видеть решения!</a></h3>";
    }?>
    <div>
        <table>
            <thead>
                <tr>
                    <th>Номер</th>
                    <th>Название</th>
                    <th>Решение</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach (pg_fetch_all($tasks) as $row) {
                    $id = $row["id"];
                    $edit_link = $admin ? " (<a href=\"view_challenge.php?id=$id&edit=1\">редактировать</a>)" : "";
                    $name = $row["name"];
                    $is_solved = $profile ? ($profile["tasks"] & 1 << (intval($id) - 1) ? "+" : "-") : "?";
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
        <?php if ($admin) {
            $new_task_id = intval($id) + 1;
            echo <<<EOF
            <a href="view_challenge.php?id=$new_task_id&edit=1">Добавить задачу</a>
            <br>
            <a href="solved_challenges.php">Решённые задачи</a>
            EOF;
        } ?>
    </div>
</body>

</html>