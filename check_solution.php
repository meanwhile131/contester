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
    ini_set("display_errors", "1");
    ini_set("display_startup_errors", "1");
    error_reporting(E_ALL);
    require_once "vendor/autoload.php";
    include "database.php";
    echo "Аутентификация...<br>";
    ob_flush();
    $CLIENT_ID = "";
    $client = new Google_Client(["466834063559-e8ntnvvptcbbdp70ovb3v1m4h8qm3c8i.apps.googleusercontent.com" => $CLIENT_ID]);
    try {
        $token = $client->verifyIdToken($_COOKIE["token"]);
    } catch (LogicException) {
        echo <<<EOF
        <p>Неправильный токен аутентификации!</p>
        EOF;
        exit();
    }
    echo "Подготовка к выполнению тестов...<br>";
    ob_flush();

    $query = $db->prepare("SELECT * FROM `challenges` WHERE id=?");
    $query->execute([$_POST["id"]]);
    $tests = json_decode($query->get_result()->fetch_assoc()["tests"], true);

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
    // unlink($file_path);
    echo "<br>";
    if ($failed) {
        echo "Тесты не пройдены!";
    } else {
        echo "Тесты пройдены! Сохраняем результат...<br>";
        ob_flush();
        $query = $db->prepare("UPDATE `users` SET `tasks`= bin(`tasks`) | 1<<? WHERE id=?");
        $query->execute([intval($_POST["id"]) - 1, $token["sub"]]);
        echo "Результат сохранен!";
    }
    echo "</p>";
    ob_flush();
    ?>
</body>

</html>