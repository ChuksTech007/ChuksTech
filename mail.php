<?php
// mail.php – simple contact form handler
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = trim($_POST['name'] ?? '');
    $email   = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
    $message = trim($_POST['message'] ?? '');

    if ($name && $email && $message) {
        $to      = 'charleschukwudichukwudi@gmail.com';
        $subject = "New portfolio contact from $name";
        $headers = "From: $email\r\nReply-To: $email\r\nContent-Type: text/plain; charset=UTF-8";
        $body    = "Name: $name\nEmail: $email\n\nMessage:\n$message";
        // @ suppress errors; in production you'd handle failures.
        if (mail($to, $subject, $body, $headers)) {
            echo "Thank you, $name. Your message has been sent.";
        } else {
            http_response_code(500);
            echo "Sorry, there was a problem sending your message. Please try again later.";
        }
    } else {
        http_response_code(400);
        echo "All fields are required and email must be valid.";
    }
} else {
    http_response_code(405);
    echo "Method not allowed.";
}
?>
