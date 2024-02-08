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
        <p> Nous vous informons que votre souscription (ref:<?= $codesouscription ?>) est prématurément arrivée à son terme.</p>
        <p> Ceci est du au non règlement de la transaction (ref: <?= $codetransaction ?>) selon les modalités conclus.</p>
        <p> N'hésitez pas à accéder à la rubrique Paiements de votre compte, afin de visualiser la progression des paiements
            associés à cette transaction.
        </p>
    </div>
    <p>Merci de votre conpréhension, <br>Passez une agréable journée.</p>
    <p>Email System Generated for Ended Subscription. &nbsp;<?= lang('Auth.emailDate') ?> <?= esc($date) ?></p>
</body>

</html>


<!-- otherMsg: Félicitation vous avez déjà payé [...frs]. Vous etes en bonne progression pour finaliser la transaction [code transaction]
Votre objectif est de [...frs] avant la date du [.. date] (n jours).
passez une agréable journée.

Cher abonné,
À travers ce rappel, Nous sommes ravi de pouvoir vous aider dans votre planification.
N'oubliez pas
l'échéance de paiement pour la transaction [code] est prévue pour demain.
passez une agréable journée. -->