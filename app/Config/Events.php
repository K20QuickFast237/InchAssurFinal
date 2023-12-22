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
    $msg  = "Pour activez votre compte, utilisez le code:$codeActivation";
    // $dest = ["676233273"];
    $dest = [$user->tel1];
    // sendSmsMessage($dest, "InchAssur", $msg);
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
