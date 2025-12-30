<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Form verilerini al
$name = htmlspecialchars($_REQUEST['name'] ?? '');
$from = filter_var($_REQUEST['email'] ?? '', FILTER_SANITIZE_EMAIL);
$subject = htmlspecialchars($_REQUEST['subject'] ?? '');
$message = htmlspecialchars($_REQUEST['message'] ?? '');

// Alıcı
$to = "tet@tetarchitects.com";

// Headers - From'u hosting domaininden yap
$headers = "From: contact@tetarchitects.com\r\n";
$headers .= "Reply-To: " . $from . "\r\n";
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: text/html; charset=UTF-8\r\n";

// Konu
$email_subject = "Website İletişim Formu: " . $subject;

// Mail içeriği
$body = "<!DOCTYPE html><html lang='tr'><head><meta charset='UTF-8'><title>İletişim Formu</title></head><body>";
$body .= "<h2>Yeni İletişim Formu Mesajı</h2>";
$body .= "<p><strong>İsim:</strong> {$name}</p>";
$body .= "<p><strong>Email:</strong> {$from}</p>";
$body .= "<p><strong>Konu:</strong> {$subject}</p>";
$body .= "<p><strong>Mesaj:</strong></p>";
$body .= "<p>{$message}</p>";
$body .= "</body></html>";

// Mail gönderme
if (mail($to, $email_subject, $body, $headers)) {
    echo "Mesajınız başarıyla gönderildi. Teşekkür ederiz!";
} else {
    error_log("Mail gönderme hatası: " . error_get_last()['message']);
    echo "Üzgünüz, mesaj gönderilirken teknik bir sorun oluştu. Lütfen daha sonra tekrar deneyin.";
}
?>