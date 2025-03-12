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
    <script src="https://accounts.google.com/gsi/client"></script>
    <script src="/js/send_solution.js"></script>
    <?php
    // ini_set('display_errors', 1);
    // ini_set('display_startup_errors', 1);
    // error_reporting(E_ALL);

    include "database.php";
    include "vendor/autoload.php";

    $CLIENT_ID = "";
    $client = new Google_Client(["466834063559-e8ntnvvptcbbdp70ovb3v1m4h8qm3c8i.apps.googleusercontent.com" => $CLIENT_ID]);
    try {
        $token = $client->verifyIdToken($_COOKIE["token"]);
        $logged_in = true;
    } catch (LogicException) {
    }
    $edit = array_key_exists("edit", $_GET) && $_GET["edit"] == 1 && ($token["email"] == "sashachernyakov111111@gmail.com" || $token["email"] == "nadezhdasergeeva77@gmail.com");
    if ($edit) {
        echo <<<EOF
        <script src="/js/edit_challenge.js"></script>
        EOF;
    }
    $editable = $edit ? " contenteditable=\"true\"" : "";

    $challenge_request = $db->prepare("SELECT * FROM `challenges` WHERE id=?");
    $challenge_request->execute([$task]);
    $challenge = $challenge_request->get_result()->fetch_assoc();
    if ($challenge == null) {
        if ($edit) {
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
    if (!$logged_in) {
        echo <<<EOF
        <h3>Войдите в аккаунт, чтобы отправить задачу!</h3>
        <script src="https://accounts.google.com/gsi/client"></script>
        <script src="js/auth.js"></script>
        EOF;
    }
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
            foreach (json_decode($challenge["tests"]) as $input => $output) {
                echo <<<EOF
                        <tr>
                            <td>$input</td>
                            <td>$output</td>
                        </tr>
                        EOF;
                $i++;
                if ($i >= 3 && !$edit) {
                    break;
                }
            }
            if ($edit) {
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
        if ($edit) {
            echo <<<EOF
            <form action="/save_challenge.php" method="post" id="send_form">
                <input type="hidden" id="form_text" name="text" value="">
                <input type="hidden" id="form_name" name="name" value="">
                <input type="hidden" id="form_tests" name="tests" value="">
                <input type="hidden" id="id" name="id" value="$task">
                <label>Удалить задачу<input type="checkbox" name="delete" /></label>
            </form>
            <button onclick="edit()">Сохранить</button>
            EOF;
        } else {
            echo <<<EOF
        
            <form action="/check_solution.php" method="post" id="send_form">
                <textarea type="text" name="code"></textarea>
                <input type="hidden" id="id" name="id" value="$task">
            </form>
            <button onclick="send()">Отправить</button>
        EOF;
        }
        ?>
    </div>
</body>

</html>