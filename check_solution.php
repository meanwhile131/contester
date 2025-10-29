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
    header('X-Accel-Buffering: no');
    ob_implicit_flush(1);
    echo "Подготовка к выполнению тестов...<br>";
    $task = $_POST["id"];
    echo "Задача: $task<br>";
    ob_flush();
    $query = pg_query_params($db, "SELECT * FROM challenges WHERE id=$1", [$_POST["id"]]);
    $challenge = pg_fetch_row($query, null, PGSQL_ASSOC);
    $tests = json_decode($challenge["tests"], true);

    $descriptorspec = [["pipe", "r"], ["pipe", "w"], ["pipe", "w"]];
    $file_path = "/tmp/" . bin2hex(random_bytes(6)) . ".py";
    file_put_contents($file_path, $_POST["code"]);
    $failed = false;
    echo "Выполнение тестов...<br><br>";
    ob_flush();

    foreach ($tests as $input => $expected_output) {
        $process = proc_open(
            "timeout 3s /usr/bin/python3 $file_path",
            $descriptorspec,
            $pipes
        );
        $start = microtime(true);
        fwrite($pipes[0], $input . "\n");
        $output = stream_get_contents($pipes[1]);
        $err_output = stream_get_contents($pipes[2]);
        if ($output == $expected_output . "\n") {
            $time = round((microtime(true) - $start) * 1000, 2);
            echo "Тест пройден за {$time} мс!<br>";
        } else {
            if (proc_get_status($process)["exitcode"] != 124) {
                echo "Тест не пройден! <br> <pre>$err_output $output</pre> <br>";
            } else {
                echo "Ограничение по времени!<br>";
            }
            $failed = true;
            break;
        }
        ob_flush();
        fclose($pipes[0]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        proc_close($process);
    }
    unlink($file_path);
    echo "<br>";
    if ($failed) {
        echo "Тесты не пройдены!";
    } else {
        echo "Тесты пройдены! Сохраняем результат...<br>";
        ob_flush();
        $query = pg_query_params($db, "UPDATE users SET tasks_solved= (tasks_solved | 1<<$1) WHERE sub=$2", [intval($_POST["id"]) - 1, $userid]);
        if (pg_affected_rows($query) == 1) {
            echo "Результат сохранен!";
        }
        else {
            echo "Ошибка при сохранении результата!";
        }
    }
    echo "</p>";
    ob_flush();
    ?>
</body>

</html>