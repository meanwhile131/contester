<?php
require_once "secrets.php";
$userid = $_SESSION["user_id"];
if (!$userid) {
    header("Location: /");
    exit();
}
if (!empty($_POST["first_name"]) && !empty($_POST["second_name"]) && !empty($_POST["third_name"]) && in_array($_POST["group"], ["211", "212", "231", "241"])) { 
    $result = pg_query_params($db, <<<EOF
        INSERT INTO users (sub,first_name,second_name,third_name,"group") VALUES ($1, $2, $3, $4, $5)
        ON CONFLICT (sub) DO UPDATE SET 
            first_name = EXCLUDED.first_name,
            second_name = EXCLUDED.second_name,
            third_name = EXCLUDED.third_name,
            "group" = EXCLUDED."group";
        EOF, [$_SESSION["user_id"], $_POST["first_name"], $_POST["second_name"], $_POST["third_name"], $_POST["group"]]);
    if ($result) {
        header("Location: /profile.php?success");
        exit();
    }
    else {
        print_r($result);
    }
}
?>
<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Сохранение профиля</title>
    <link rel="stylesheet" href="/css/general.css">
</head>

<body>
    <p>Ошибка при сохранении профиля!</p>
</body>

</html>