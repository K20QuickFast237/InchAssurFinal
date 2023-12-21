<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<head>
    <meta name="x-apple-disable-message-reformatting">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="format-detection" content="telephone=no, date=no, address=no, email=no">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title><?= lang('Auth.emailActivateSubject') ?></title>
</head>

<body>
    <h2>Hello <?= $nomComplet . " Bienvenue sur IncH Assurance." ?></h2>

    <div style="text-align: left">
        <h3><a href='<?= $front_baseURL . "/maison/" . $token ?>'>Cliquer pour activer votre compte</a></h3>
        <p>User ID: <?= $userEmail ?></p>
        <p>
            Code d'acces: <blod><?= $codeconnect; ?></blod>
        </p>
        <!-- <p>Temporary Password: < ?= $tmpPass; ?></p> -->
    </div>

    <p>Email System Generated for a New User. &nbsp;<?= lang('Auth.emailDate') ?> <?= esc($date) ?></p>
</body>

</html>