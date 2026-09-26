<?php
/**
 * Relays the Student & Group Rate and Invitation Letter/Invoice request forms as email,
 * using this server's own PHP mail() instead of a third-party relay (e.g. Web3Forms).
 * Only ever sends to one of the fixed inboxes below, keyed by "form_type" — never to an
 * address supplied in the request — so this endpoint can't be abused as an open mail relay.
 * Only works when this file is actually served (the cPanel-hosted domain); GitHub Pages
 * is static-only and can't run PHP, so the forms will show their fallback error there.
 */

// Never let a PHP warning/notice leak raw HTML into what must stay a clean JSON response.
error_reporting(0);
ini_set('display_errors', '0');

header('Content-Type: application/json; charset=UTF-8');

function respond($success, $message) {
    echo json_encode(['success' => $success, 'message' => $message]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    respond(false, 'Method not allowed.');
}

// Honeypot: real visitors never see or fill this hidden field, so a non-empty value
// means a bot submitted the form. Pretend success so the bot doesn't retry.
if (!empty($_POST['botcheck'])) {
    respond(true, 'Received.');
}

$recipients = [
    'rate'   => ['to' => 'admin@kidneyhealth.africa',       'subject' => 'Student / Group Rate Request — Africa Kidney Health Summit'],
    'letter' => ['to' => 'secretariat@kidneyhealth.africa', 'subject' => 'Invitation Letter / Invoice Request — Africa Kidney Health Summit'],
];

$formType = $_POST['form_type'] ?? '';
if (!isset($recipients[$formType])) {
    http_response_code(400);
    respond(false, 'Unknown form.');
}

$submitterEmail = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
if (!$submitterEmail) {
    http_response_code(400);
    respond(false, 'A valid email address is required.');
}

$body = trim($_POST['summary'] ?? '');
if ($body === '') {
    http_response_code(400);
    respond(false, 'The request was empty.');
}

$to = $recipients[$formType]['to'];
$subject = $recipients[$formType]['subject'];
$headers = implode("\r\n", [
    'From: Africa Kidney Health Summit <noreply@kidneyhealth.africa>',
    'Reply-To: ' . $submitterEmail,
    'Content-Type: text/plain; charset=UTF-8',
]);

if (mail($to, $subject, $body, $headers)) {
    respond(true, 'Sent.');
}

http_response_code(500);
respond(false, 'The mail server could not send this request.');
