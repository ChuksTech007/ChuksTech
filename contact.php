<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require 'vendor/autoload.php';

$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || strpos($line, '#') === 0) {
            continue;
        }
        [$key, $value] = explode('=', $line, 2) + [null, null];
        if ($key === null) {
            continue;
        }
        $key = trim($key);
        $value = trim($value);
        if ($key !== '' && getenv($key) === false) {
            putenv("$key=$value");
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }
    }
}

$name    = trim(strip_tags($_POST['name']    ?? ''));
$email   = trim(strip_tags($_POST['email']   ?? ''));
$subject = trim(strip_tags($_POST['subject'] ?? ''));
$message = trim(strip_tags($_POST['message'] ?? ''));

if (!$name || !$email || !$subject || !$message) {
    echo json_encode(['success' => false, 'message' => 'All fields are required.']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email address.']);
    exit;
}

$smtpHost     = getenv('SMTP_HOST') ?: 'smtp.gmail.com';
$smtpPort     = getenv('SMTP_PORT') ?: 587;
$smtpUsername = getenv('SMTP_USERNAME') ?: '';
$smtpPassword = getenv('SMTP_PASSWORD') ?: '';
$smtpSecure   = strtolower(getenv('SMTP_SECURE') ?: 'tls');
$smtpFrom     = getenv('SMTP_FROM') ?: $smtpUsername;
$smtpFromName = getenv('SMTP_FROM_NAME') ?: 'Portfolio Contact';
$smtpTo       = getenv('SMTP_TO') ?: $smtpUsername;

if (!$smtpUsername || !$smtpPassword || !$smtpFrom || !$smtpTo) {
    error_log('SMTP settings are not configured properly.');
    echo json_encode(['success' => false, 'message' => 'Mail configuration is missing. Please check the server settings.']);
    exit;
}

$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->SMTPDebug = SMTP::DEBUG_OFF;
    $mail->Debugoutput = function($str, $level) {
        error_log('PHPMailer debug: ' . $str);
    };
    $mail->Host       = $smtpHost;
    $mail->SMTPAuth   = true;
    $mail->Username   = $smtpUsername;
    $mail->Password   = str_replace(' ', '', $smtpPassword);
    $mail->SMTPSecure = $smtpSecure === 'ssl' ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = (int) $smtpPort;
    $mail->SMTPOptions = [
        'ssl' => [
            'verify_peer'       => false,
            'verify_peer_name'  => false,
            'allow_self_signed' => true,
        ],
    ];

    $mail->setFrom($smtpFrom, $smtpFromName);
    $mail->addAddress($smtpTo);
    $mail->addReplyTo($email, $name);
    $mail->Subject = "Portfolio Contact: $subject";
    $mail->isHTML(true);
    $mail->Body = "
        <h3 style='color:#38bdf8'>New Portfolio Message</h3>
        <p><strong>Name:</strong> " . htmlspecialchars($name) . "</p>
        <p><strong>Email:</strong> " . htmlspecialchars($email) . "</p>
        <p><strong>Subject:</strong> " . htmlspecialchars($subject) . "</p>
        <p><strong>Message:</strong></p>
        <p>" . nl2br(htmlspecialchars($message)) . "</p>
    ";

    $mail->send();
    echo json_encode(['success' => true, 'message' => "Thanks $name! I'll get back to you shortly."]);
} catch (Exception $e) {
    error_log('PHPMailer error: ' . $mail->ErrorInfo);
    echo json_encode(['success' => false, 'message' => 'Could not send message. Please try WhatsApp or email directly.']);
}
?>
