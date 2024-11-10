<?php

declare(strict_types=1);

namespace Gatherling\Helpers;

use function Safe\curl_exec;
use function Safe\curl_getinfo;
use function Safe\curl_init;
use function Safe\curl_setopt;
use function Safe\json_encode;

// Use Brevo to send an email from us to a single recipient. Brevo supports multiple recipients, attachments, etc. but we don't need that yet.
function sendEmail(string $to, string $subj, string $msg): bool
{
    $body = [
        'sender'      => ['name' => 'Gatherling', 'email' => 'no-reply@gatherling.com'],
        'to'          => [['name' => $to, 'email' => $to]],
        'subject'     => $subj,
        'htmlContent' => $msg,
    ];

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, 'https://api.brevo.com/v3/smtp/email');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));

    $headers = [];
    $headers[] = 'Accept: application/json';
    $headers[] = 'Api-Key: ' . config()->string('brevo_api_key');
    $headers[] = 'Content-Type: application/json';
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    curl_exec($ch);
    if (curl_errno($ch)) {
        logger()->error('Error sending email: ' . curl_error($ch));
        return false;
    }
    /** @var int $response_code */
    $response_code = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    if ($response_code >= 300) {
        logger()->error('Error sending email: ' . $response_code . ' response code');
        return false;
    }

    return true;
}
