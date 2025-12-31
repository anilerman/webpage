<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Form verilerini al
$name = htmlspecialchars($_REQUEST['name'] ?? '');
$from = filter_var($_REQUEST['email'] ?? '', FILTER_SANITIZE_EMAIL);
$subject = htmlspecialchars($_REQUEST['subject'] ?? '');
$message = htmlspecialchars($_REQUEST['message'] ?? '');

// İsim kontrolü
if (empty(trim($name)) || 
    trim($name) === '.' || 
    trim($name) === '..' || 
    trim($name) === ',' || 
    trim($name) === '?' || 
    trim($name) === '*' || 
    trim($name) === '!') {
    echo "<div style='color: red; font-weight: bold;'>Lütfen uygun bir isim giriniz ve isim alanı boş bırakılamaz.</div>";
    exit;
}

if (empty(trim($subject))) {
    echo "<div style='color: red; font-weight: bold;'>Lütfen konu alanını boş bırakmayınız.</div>";
    exit;
}

if (empty(trim($message))) {
    echo "<div style='color: red; font-weight: bold;'>Lütfen mesaj alanını boş bırakmayınız.</div>";
    exit;
}

// Email kontrolü

if (empty($from) || !filter_var($from, FILTER_VALIDATE_EMAIL)) {
    echo "<div style='color: red; font-weight: bold;'>Lütfen geçerli bir email adresi giriniz.</div>";
    exit;
}
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
    echo "<div style='color: green; font-weight: bold;'>Mesajınız başarıyla gönderildi. Teşekkür ederiz!</div>";
} else {
    error_log("Mail gönderme hatası: " . error_get_last()['message']);
    echo "<div style='color: red; font-weight: bold;'>Üzgünüz, mesaj gönderilirken teknik bir sorun oluştu. Lütfen daha sonra tekrar deneyin.</div>";
}
?>