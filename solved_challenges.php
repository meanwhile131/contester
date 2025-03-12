<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Главная страница</title>
    <script src="https://accounts.google.com/gsi/client"></script>
    <link rel="stylesheet" href="/css/general.css">
    <link rel="stylesheet" href="/css/solved_challenges.css">
</head>

<body>
    <?php
    include "vendor/autoload.php";
    include "database.php";

    error_reporting(E_ALL);
    ini_set('display_errors', '1');
    $CLIENT_ID = "";
    $client = new Google_Client(["466834063559-e8ntnvvptcbbdp70ovb3v1m4h8qm3c8i.apps.googleusercontent.com" => $CLIENT_ID]);
    try {
        $token = $client->verifyIdToken($_COOKIE["token"]);
    } catch (LogicException) {
        echo <<<EOF
        <p>Войдите в аккаунт!</p>
        <script src="https://accounts.google.com/gsi/client"></script>
        <script src="/js/auth.js"></script>
        EOF;
        exit();
    }

    if ($token["email"] != "sashachernyakov111111@gmail.com" && $token["email"] != "nadezhdasergeeva77@gmail.com") {
        echo "<p>Вы не являетесь админом!</p>";
        exit();
    }

    $users = $db->query("SELECT * FROM `users`")->fetch_all(MYSQLI_ASSOC);
    $challenges = $db->query("SELECT id FROM `challenges`")->fetch_all();
    ?>
    <h1>Решённые задачи</h1>
    <table>
        <thead>
            <tr>
                <th>Фамилия</th>
                <th>Имя</th>
                <th>Группа</th>
                <?php
                foreach ($challenges as $_ => $id) {
                    echo "<th>{$id[0]}</th>";
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
                foreach ($challenges as $_ => $id) {
                    $solved = intval($user["tasks"]) & 1 << (intval($id[0]) - 1) ? "+" : "-";
                    echo "<td>$solved</td>";
                }
            }
            ?>
        </tbody>
    </table>
</body>

</html>