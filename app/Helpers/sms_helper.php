<?php

/**
 * Send an SMS message by using Infobip API PHP Client.
 *
 * For your convenience, environment variables are already pre-populated with your account data
 * like authentication, base URL and phone number.
 *
 * Please find detailed information in the readme file.
 */

use Infobip\Api\SmsApi;
use Infobip\Configuration;
use Infobip\Model\SmsAdvancedTextualRequest;
use Infobip\Model\SmsDestination;
use Infobip\Model\SmsTextualMessage;

/*
    function testSendSMS()
    {
        $BASE_URL = "https://k29jnx.api.infobip.com";
        $API_KEY = "7a57f9d054aa537ee7d8c0db0c54d76f-bee9ca9c-feb0-4653-a45f-8a0c829ba812";

        $SENDER = "InfoSMS";
        $RECIPIENT = "237676233273";
        $MESSAGE_TEXT = "This is a sample message";

        $configuration = new Configuration(host: $BASE_URL, apiKey: $API_KEY);

        $sendSmsApi = new SmsApi(config: $configuration);

        $destination = new SmsDestination(
            to: $RECIPIENT
        );

        $message = new SmsTextualMessage(destinations: [$destination], from: $SENDER, text: $MESSAGE_TEXT);

        $request = new SmsAdvancedTextualRequest(messages: [$message]);

        try {
            $smsResponse = $sendSmsApi->sendSmsMessage($request);

            echo $smsResponse->getBulkId() . PHP_EOL;

            foreach ($smsResponse->getMessages() ?? [] as $message) {
                echo sprintf('Message ID: %s, status: %s', $message->getMessageId(), $message->getStatus()?->getName()) . PHP_EOL;
            }
        } catch (Throwable $apiException) {
            echo ("HTTP Code: " . $apiException->getCode() . "\n");
        }
    }
*/

function sendSmsMessage(array $destinataireNumbers, string $senderName, string $message)
{
    $BASE_URL = getenv("SMS_API_URL");
    $API_KEY  = getenv("SMS_API_KEY");

    $configuration = new Configuration(host: $BASE_URL, apiKey: $API_KEY);
    $sendSmsApi = new SmsApi(config: $configuration);

    $destinations = array_map(
        fn ($num) => new SmsDestination(to: $num),
        $destinataireNumbers
    );
    $message = new SmsTextualMessage(destinations: $destinations, from: $senderName, text: $message);
    $request = new SmsAdvancedTextualRequest(messages: [$message]);

    try {
        $smsResponse = $sendSmsApi->sendSmsMessage($request);
    } catch (\Throwable $th) {
        return false;
    }

    return array_map(
        fn ($res) => ["idMessage" => $res->getMessageId(), "statut" => $res->getStatus()?->getName()],
        $smsResponse->getMessages()
    );
}
