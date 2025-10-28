<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Регистрация</title>
    <link rel="stylesheet" href="/css/general.css">
</head>

<body>
    <?php
    require __DIR__ . '/vendor/autoload.php';
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);

    use Webauthn\PublicKeyCredentialRpEntity;
    use Webauthn\PublicKeyCredentialUserEntity;
    use Webauthn\PublicKeyCredentialCreationOptions;
    use Webauthn\AttestationStatement\AttestationStatementSupportManager;
    use Webauthn\AttestationStatement\NoneAttestationStatementSupport;
    use Webauthn\Denormalizer\WebauthnSerializerFactory;
    use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
    use Ramsey\Uuid\Uuid;

    $rpEntity = PublicKeyCredentialRpEntity::create(
        'Контестер',
        'contester.meanwhile131.dpdns.org'
    );
    $uuid = Uuid::uuid4();
    $userEntity = PublicKeyCredentialUserEntity::create(
        "username",
        $uuid->toString(),
        "username"
    );

    $challenge = random_bytes(16);
    $publicKeyCredentialCreationOptions = PublicKeyCredentialCreationOptions::create(
        $rpEntity,
        $userEntity,
        $challenge
    );

    $attestationStatementSupportManager = AttestationStatementSupportManager::create();
    $attestationStatementSupportManager->add(NoneAttestationStatementSupport::create());
    $factory = new WebauthnSerializerFactory($attestationStatementSupportManager);
    $serializer = $factory->create();
    $jsonObject = $serializer->serialize(
        $publicKeyCredentialCreationOptions,
        'json',
        [
            AbstractObjectNormalizer::SKIP_NULL_VALUES => true
        ]
    );
    ?>
    <form id="registration_form">
        <label>Имя пользователя:
            <input required type="text" name="username" id="username_input">
        </label>
        <br>
        <label>Фамилия:
            <input required type="text" name="second_name">
        </label>
        <br>
        <label>Имя:
            <input required type="text" name="first_name">
        </label>
        <br>
        <label>Отчество:
            <input required type="text" name="third_name">
        </label>
        <br>
        <label>Группа:
            <label><input required type="radio" name="group" value="211">211</label>
            <label><input required type="radio" name="group" value="212">212</label>
            <label><input required type="radio" name="group" value="231">231</label>
            <label><input required type="radio" name="group" value="241">241</label>
        </label>
        <br>
        <input type="hidden" id="publickey_input">
        <button type="submit">Зарегистрироваться</button>
    </form>
    <script>
        const optionsString = '<?php echo $jsonObject; ?>';
        const publicKey = JSON.parse(optionsString);
        let utf8Encode = new TextEncoder();
        publicKey.challenge = utf8Encode.encode(publicKey.challenge);
        publicKey.user.id = utf8Encode.encode(publicKey.user.id);
        const registrationForm = document.getElementById("registration_form");
        async function submitRegistration(e) {
            if (e.preventDefault) e.preventDefault();
            const username = document.getElementById("username_input").value;
            publicKey.user.name = username;
            publicKey.user.displayName = username;
            const publicKeyCredential = await navigator.credentials.create({
                publicKey
            });
            const publicKeyInput = document.getElementById("publickey_input");
            publicKeyInput.value = JSON.stringify(publicKeyCredential);
            return false;
            // .submit();
        }
        registrationForm.onsubmit = submitRegistration;
    </script>
</body>

</html>