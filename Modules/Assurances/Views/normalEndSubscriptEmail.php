<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<head>
    <meta name="x-apple-disable-message-reformatting">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="format-detection" content="telephone=no, date=no, address=no, email=no">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title><?= lang('Auth.emailActivateSubject') ?></title>
</head>

<body>
    <h2>Hello <?= "$nomComplet," ?></h2>

    <div style="text-align: left">
        <p> Nous vous informons que votre souscription (ref:<?= $codesouscription ?>) est arrivée à son terme.</p>
        <p> N'hésitez pas à la renouveller en accédent à votre compte où en suivant <a href="<?= $link ?>">ce lien.</a></p>
    </div>
    <p>Passez une agréable journée.</p>
    <p>Email System Generated for Ended Subscription. &nbsp;<?= lang('Auth.emailDate') ?> <?= esc($date) ?></p>
</body>

</html>