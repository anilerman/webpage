<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = strip_tags(trim($_POST["name"]));
    $email = filter_var(trim($_POST["email"]), FILTER_SANITIZE_EMAIL);
    $subject = strip_tags(trim($_POST["subject"]));
    $message = trim($_POST["message"]);

    // Check if required fields are filled
    if (empty($name) || empty($subject) || empty($message) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo "Lütfen formu eksiksiz doldurun.";
        exit;
    }

    // Set recipient email address
    $recipient = "tet@tetarchitects.com";
    $email_subject = "Yeni Mesaj: $subject";
    $email_body = "İsim: $name\n";
    $email_body .= "Email: $email\n\n";
    $email_body .= "Mesaj:\n$message\n";

    // Email headers
    $headers = "From: $name <$email>\r\n";
    $headers .= "Reply-To: $email\r\n";

    // Send the email
    if (mail($recipient, $email_subject, $email_body, $headers)) {
        http_response_code(200);
        echo "Mesajınız başarıyla gönderildi!";
    } else {
        http_response_code(500);
        echo "Mesaj gönderilirken bir hata oluştu.";
    }
} else {
    http_response_code(403);
    echo "Bu form yalnızca POST metodu kullanılarak gönderilebilir.";
}
?>
