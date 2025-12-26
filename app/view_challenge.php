<?php
require_once 'secrets.php';
if (!empty($_SESSION["user_id"])) {
    $userid = $_SESSION["user_id"];
    $user_query = pg_query_params($db, "SELECT is_admin FROM users WHERE sub=$1", [$userid]);
    $user = pg_fetch_row($user_query, null, PGSQL_ASSOC);
    if (!$user) {
        header("Location: /profile.php");
        exit();
    }
    $is_admin = $user["is_admin"] == "t";
}
$edit_mode = !empty($_GET["edit"]) && $_GET["edit"] == 1;
?>
<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Задача
        <?php
        $task = $_GET["id"];
        echo $task;
        ?>
    </title>
    <link rel="stylesheet" href="/css/general.css">
    <link rel="stylesheet" href="/css/view_challenge.css">
    <?php

    include "secrets.php";
    include "vendor/autoload.php";

    $task_safe = htmlspecialchars($task, ENT_QUOTES | ENT_SUBSTITUTE, "UTF-8");
    if ($edit_mode) {
        echo <<<EOF
        <script src="/js/edit_challenge.js"></script>
        EOF;
    }
    $editable = $edit_mode ? " contenteditable=\"true\"" : "";

    $challenge_request = pg_query_params($db, "SELECT * FROM challenges WHERE id=$1", [$_GET["id"]]);
    $challenge = pg_fetch_row($challenge_request, null, PGSQL_ASSOC);
    if (!$challenge) {
        if ($edit_mode) {
            $challenge = ["name" => "Название задачи", "text" => "Условие задачи", "tests" => "{}"];
        } else {
            echo "<p>Задача не найдена!</p>";
            exit();
        }
    }
    ?>
</head>

<body>
    <?php
    echo <<<EOF
    <h1 id="name"$editable>
    {$challenge["name"]}
    </h1>
    EOF;
    ?>
    <div id="challenge">
        <?php echo <<<EOF
            <p id="text"$editable>{$challenge["text"]}</p>
            EOF; ?>
        <table id="io">
            <thead>
                <tr>
                    <th>Ввод</th>
                    <th>Вывод</th>
                </tr>
            </thead>
            <?php echo "<tbody$editable>"; ?>
            <?php
            $i = 0;
            $tests_request = pg_query_params($db, "SELECT * FROM tests WHERE challenge=$1", [$_GET["id"]]);
            $tests = pg_fetch_all($tests_request, PGSQL_ASSOC);
            foreach ($tests as $test) {
                $input = $test["in"];
                $output = $test["out"];
                echo <<<EOF
                        <tr>
                            <td>$input</td>
                            <td>$output</td>
                        </tr>
                        EOF;
                $i++;
                if ($i >= 3 && !$edit_mode) {
                    break;
                }
            }
            if ($edit_mode) {
                echo <<<EOF
                        <tr>
                            <td></td>
                            <td></td>
                        </tr>
                    EOF;
            }
            ?>
            </tbody>
        </table>
    </div>
    <div id="form">
        <?php
        if ($edit_mode) {
            echo <<<EOF
            <form action="/save_challenge.php" method="post" id="send_form">
                <input type="hidden" id="form_text" name="text" value="">
                <input type="hidden" id="form_name" name="name" value="">
                <input type="hidden" id="form_tests" name="tests" value="">
                <input type="hidden" id="id" name="id" value="$task_safe">
                <label>Удалить задачу<input type="checkbox" name="delete" /></label>
            </form>
            <button onclick="edit()">Сохранить</button>
            EOF;
        } else if (!empty($user)) {
            echo <<<EOF
        
            <form action="/check_solution.php" method="post" id="send_form">
                <textarea type="text" name="code"></textarea>
                <input type="hidden" id="id" name="id" value="$task_safe">
                <br>
                <button type="submit">Отправить</button>
            </form>
        EOF;
        } else {
            if (empty($user)) {
                echo <<<EOF
                <h3>Войдите в аккаунт, чтобы отправлять решения!</h3>
                EOF;
            }
        }
        ?>
    </div>
</body>

</html>