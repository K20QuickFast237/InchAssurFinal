<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<head>
    <meta name="x-apple-disable-message-reformatting">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="format-detection" content="telephone=no, date=no, address=no, email=no">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title><?= lang('Auth.emailActivateSubject') ?></title>
</head>

<body>
    <!-- <h2>Hello < ?= "$nomComplet," ?></h2> -->
    <h2>Hello,</h2>

    <div style="text-align: left">
        <p> Utilisez le code <?= $code; ?> pour signer.</p>
    </div>
    <p>Email System Generated for Subscription Signature. &nbsp;<?= lang('Auth.emailDate') ?> <?= esc($date) ?></p>
</body>

</html>