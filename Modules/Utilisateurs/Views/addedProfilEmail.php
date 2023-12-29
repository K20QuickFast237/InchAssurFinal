<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<head>
    <meta name="x-apple-disable-message-reformatting">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="format-detection" content="telephone=no, date=no, address=no, email=no">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title><?= $subject ?></title>
</head>

<body>
    <h2>Hello <?= $nomComplet . " Merci d'être avec Nous." ?></h2>
    <div style="text-align: left">
        <p>
            Nous Souhaitons vous confirmer l'ajout du profil <bold><?= $profil ?></blod> parmi vos profils.
                <br>Vous pouvez désormais l'utiliser afin d'accéder à de nouvelles fonctionalités.
        </p>
        <p>
            Heureux de vous compter parmi nous.
            IncH Digital Services&copy;
        </p>
        <!-- <p>Temporary Password: < ?= $tmpPass; ?></p> -->
    </div>

    <p>Email System Generated for Profil Added. &nbsp;<?= lang('Auth.emailDate') ?> <?= esc($date) ?></p>
</body>

</html>