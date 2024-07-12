<?php

namespace Config;

use CodeIgniter\Events\Events;
use CodeIgniter\Exceptions\FrameworkException;
use CodeIgniter\HotReloader\HotReloader;

use CodeIgniter\I18n\Time;
use CodeIgniter\Shield\Exceptions\LogicException;
use CodeIgniter\Shield\Exceptions\RuntimeException;

helper(["email", "sms"]);

#---------------------------------------------------------------------
# Evenements à la création de compte permettant d'envoyer un mail et un sms.
#----------------------------------------------------------------------
// Events::on('newRegistration', static function ($user, $tmpPass) {
Events::on('newRegistration', static function ($user, $codeActivation, $token) {
    $userEmail = $user->email;
    if ($userEmail === null) {
        throw new LogicException(
            'Email Activation needs user email address. user_id: ' . $user->id
        );
    }
    $date = Time::now()->toDateTimeString();

    // Send the email
    $email = emailer()->setFrom(setting('Email.fromEmail'), setting('Email.fromName') ?? '');
    $email->setTo($userEmail);
    $email->setSubject("Activation de compte IncHAssur");
    $email->setMessage(view(
        setting('Auth.views')['registration_email'],
        [
            'userEmail'     => $userEmail,
            // 'tmpPass'       => $tmpPass,
            'codeconnect'   => $codeActivation,
            'date'          => $date,
            'token'         => $token,
            'nomComplet'    => $user->username,
            'front_baseURL' => getenv("FRONTBASEURL"),
        ]
    ));

    if ($email->send(false) === false) {
        throw new RuntimeException('Cannot send email for user: ' . $user->email . "\n" . $email->printDebugger(['headers']));
    }

    // Clear the email
    $email->clear();
});
Events::on('newRegistration', static function ($user, $codeActivation, $token = null) {
    $msg  = "Bienvenu sur IncHAssur. Activez votre compte avec le code: $codeActivation";
    $dest = [$user->tel1,];
    sendSmsMessage($dest, "InchAssur", $msg);
});

Events::on('profilAttributed', static function ($utilisateur, $profil) {
    $date = Time::now()->toDateTimeString();
    // Send the email
    $email = emailer()->setFrom(setting('Email.fromEmail'), setting('Email.fromName') ?? '');
    $email->setTo($utilisateur->email);
    $email->setSubject("Profil $profil->titre ajouté");
    $email->setMessage(view(
        setting('Auth.views')['addedProfil_email'],
        [
            'subject'    => "Profil Ajouté",
            'date'       => $date,
            'profil'     => $profil->titre,
            'nomComplet' => $utilisateur->nom . " " . $utilisateur->prenom,
        ]
    ));
    if ($email->send(false) === false) {
        throw new RuntimeException('Cannot send email for user: ' . $utilisateur->email . "\n" . $email->printDebugger(['headers']));
    }
    // Clear the email
    $email->clear();
});

#-------------------------------------------------
# Evenements à l'invalidation d'une souscription.
#-------------------------------------------------
Events::on("EndedSouscription", static function ($utilisateur, $codeSouscription, $codetransaction = null, $normal = true) {
    $userEmail = $utilisateur->email;
    if ($userEmail === null) {
        throw new LogicException(
            'Email Activation needs user email address. user_id: ' . $utilisateur->id
        );
    }
    $date = Time::now()->toDateTimeString();

    // Send the email
    $email = emailer()->setFrom(setting('Email.fromEmail'), setting('Email.fromName') ?? '');
    $email->setTo($userEmail);
    $email->setSubject("Expiration de souscription");
    $data = [
        'link'             => "(Lien de connexion)",
        'codesouscription' => $codeSouscription,
        'codetransaction'  => $codetransaction,
        'date'             => $date,
        'nomComplet'       => $utilisateur->prenom . ' ' . $utilisateur->nom,
    ];
    if ($normal) {
        $email->setMessage(view(setting('Notify.views')['suscription_normaly_ended_email'], $data));
    } else {
        $email->setMessage(view(setting('Notify.views')['suscription_early_ended_email'], $data));
    }


    if ($email->send(false) === false) {
        throw new RuntimeException('Cannot send email for user: ' . $userEmail . "\n" . $email->printDebugger(['headers']));
    }

    // Clear the email
    $email->clear();
});
Events::on('EndedSouscription', static function ($utilisateur, $codeSouscription, $codetransaction = null, $normal = true) {
    $msg  = "Cher $utilisateur->prenom, Nous vous informons que votre souscription $codeSouscription est arrivée à expiration. Connectez vous à votre compte pour voir les détails. Merci de faire confiance à IncHAssur.";
    $dest = [$utilisateur->tel1];
    sendSmsMessage($dest, "InchAssur", $msg);
});

#-------------------------------------------------
# Evenements à l'échéance de paiement.
#-------------------------------------------------
// Pour le cas de paiement cyclique. 
Events::on("PaiementRemember", static function ($utilisateur, $codetransaction) {
    $userEmail = $utilisateur->email;
    if ($userEmail === null) {
        throw new LogicException(
            'Email Activation needs user email address. user_id: ' . $utilisateur->id
        );
    }
    $date = Time::now()->toDateTimeString();

    // Send the email
    $email = emailer()->setFrom(setting('Email.fromEmail'), setting('Email.fromName') ?? '');
    $email->setTo($userEmail);
    $email->setSubject("Rappel de paiement");
    $data = [
        'link'            => "(Lien de connexion)",
        'codetransaction' => $codetransaction,
        'date'            => $date,
        'nomComplet'      => $utilisateur->prenom . ' ' . $utilisateur->nom,
    ];

    $email->setMessage(view(setting('Notify.views')['paiement_remember_email'], $data));


    if ($email->send(false) === false) {
        throw new RuntimeException('Cannot send email for user: ' . $userEmail . "\n" . $email->printDebugger(['headers']));
    }

    // Clear the email
    $email->clear();
});
Events::on('PaiementRemember', static function ($utilisateur, $codetransaction) {
    $msg  = "Cher $utilisateur->prenom, Nous vous rappelons que l'échéance de paiement pour votre transaction numéro $codetransaction est prévue pour demain. Merci de faire confiance à IncHAssur.";
    $dest = [$utilisateur->tel1];
    sendSmsMessage($dest, "InchAssur", $msg);
});

// Pour le cas de paiement sur une période.
Events::on("PaiementSuggest", static function ($utilisateur, $codetransaction, $dateFin) {
    $userEmail = $utilisateur->email;
    if ($userEmail === null) {
        throw new LogicException(
            'Email Activation needs user email address. user_id: ' . $utilisateur->id
        );
    }
    $date = Time::now()->toDateTimeString();

    // Send the email
    $email = emailer()->setFrom(setting('Email.fromEmail'), setting('Email.fromName') ?? '');
    $email->setTo($userEmail);
    $email->setSubject("Rappel de paiement");
    $data = [
        'link'            => "(Lien de connexion)",
        'codetransaction' => $codetransaction,
        'date'            => $date,
        'dateFin'         => $dateFin,
        'nomComplet'      => $utilisateur->prenom . ' ' . $utilisateur->nom,
    ];

    $email->setMessage(view(setting('Notify.views')['paiement_suggest_email'], $data));


    if ($email->send(false) === false) {
        throw new RuntimeException('Cannot send email for user: ' . $userEmail . "\n" . $email->printDebugger(['headers']));
    }

    // Clear the email
    $email->clear();
});
Events::on('PaiementSuggest', static function ($utilisateur, $codetransaction, $dateFin) {
    $msg  = "Cher $utilisateur->prenom, N'oubliez pas, l'échéance de paiement pour la transaction numero $codetransaction qui est prévue pour le $dateFin. Merci de faire confiance à IncHAssur.";
    $dest = [$utilisateur->tel1];
    sendSmsMessage($dest, "InchAssur", $msg);
});


/*
 * --------------------------------------------------------------------
 * Application Events
 * --------------------------------------------------------------------
 * Events allow you to tap into the execution of the program without
 * modifying or extending core files. This file provides a central
 * location to define your events, though they can always be added
 * at run-time, also, if needed.
 *
 * You create code that can execute by subscribing to events with
 * the 'on()' method. This accepts any form of callable, including
 * Closures, that will be executed when the event is triggered.
 *
 * Example:
 *      Events::on('create', [$myInstance, 'myMethod']);
 */

Events::on('pre_system', static function () {
    if (ENVIRONMENT !== 'testing') {
        if (ini_get('zlib.output_compression')) {
            throw FrameworkException::forEnabledZlibOutputCompression();
        }

        while (ob_get_level() > 0) {
            ob_end_flush();
        }

        ob_start(static function ($buffer) {
            return $buffer;
        });
    }

    /*
     * --------------------------------------------------------------------
     * Debug Toolbar Listeners.
     * --------------------------------------------------------------------
     * If you delete, they will no longer be collected.
     */
    if (CI_DEBUG && !is_cli()) {
        Events::on('DBQuery', 'CodeIgniter\Debug\Toolbar\Collectors\Database::collect');
        Services::toolbar()->respond();
        // Hot Reload route - for framework use on the hot reloader.
        if (ENVIRONMENT === 'development') {
            Services::routes()->get('__hot-reload', static function () {
                (new HotReloader())->run();
            });
        }
    }
});
