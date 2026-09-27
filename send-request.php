<?php
/**
 * Relays the Student & Group Rate and Invitation Letter/Invoice request forms as email,
 * sent via authenticated Gmail SMTP (PHPMailer) rather than a third-party relay or PHP's
 * bare mail() — Gmail's SMTP servers reject unauthenticated mail from most shared hosts,
 * so real auth is needed for reliable delivery.
 *
 * Only ever sends to one of the fixed inboxes below, keyed by "form_type" — never to an
 * address supplied in the request — so this endpoint can't be abused as an open mail relay.
 *
 * The SMTP credential lives in mail-config.local.php, which is NOT committed to git (see
 * .gitignore) and is written fresh by the deploy workflow from GitHub secrets on every
 * deploy. This file only works on the cPanel-hosted domain; GitHub Pages is static-only.
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

// Temporarily routed to a mailbox the team can actually access while
// admin@/secretariat@kidneyhealth.africa are set up — switch the 'to' values
// back once those mailboxes are confirmed working.
$recipients = [
    'rate'   => ['to' => 'onwongasamm@gmail.com', 'subject' => 'Student / Group Rate Request — Africa Kidney Health Summit'],
    'letter' => ['to' => 'onwongasamm@gmail.com', 'subject' => 'Invitation Letter / Invoice Request — Africa Kidney Health Summit'],
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

$configFile = __DIR__ . '/mail-config.local.php';
if (!file_exists($configFile)) {
    http_response_code(500);
    respond(false, 'Mail is not configured on this server yet.');
}
$config = require $configFile;

require __DIR__ . '/lib/PHPMailer/Exception.php';
require __DIR__ . '/lib/PHPMailer/SMTP.php';
require __DIR__ . '/lib/PHPMailer/PHPMailer.php';
require __DIR__ . '/lib/email-template.php';

$to = $recipients[$formType]['to'];
$subject = $recipients[$formType]['subject'];

// "$body" is a flattened "Label: value" list built client-side (see script.js) — split it
// back into rows so the HTML email can render it as a proper field/value table.
$rows = [];
foreach (explode("\n", $body) as $line) {
    $line = trim($line);
    if ($line === '') continue;
    $pos = strpos($line, ': ');
    $rows[] = $pos === false ? [$line, ''] : [substr($line, 0, $pos), substr($line, $pos + 2)];
}
$htmlBody = render_request_email($recipients[$formType]['subject'], $rows, $submitterEmail);

try {
    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = $config['smtp_user'];
    $mail->Password = $config['smtp_pass'];
    $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;
    $mail->CharSet = 'UTF-8';

    $mail->setFrom($config['smtp_user'], 'Africa Kidney Health Summit');
    $mail->addAddress($to);
    $mail->addReplyTo($submitterEmail);
    $mail->Subject = $subject;
    $mail->isHTML(true);
    $mail->Body = $htmlBody;
    $mail->AltBody = $body;

    $mail->send();
    respond(true, 'Sent.');
} catch (Throwable $e) {
    http_response_code(500);
    respond(false, 'The mail server could not send this request.');
}
