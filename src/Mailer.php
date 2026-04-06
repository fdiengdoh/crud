<?php
namespace App;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as MailException;
use PHPMailer\PHPMailer\SMTP;

class Mailer {

    /**
     * Send an email using PHPMailer.
     *
     * @param string $to Recipient email address.
     * @param string $toName Recipient name.
     * @param string $subject Email subject.
     * @param string $body HTML email body.
     * @param string $altBody Plain-text email body.
     * @return bool|string Returns true on success or error message on failure.
     */
    public static function sendEmail($to, $toName, $subject, $body, $altBody) {
        $mail = new PHPMailer(true);
        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host       = $_ENV['SMTP_HOST'] ?? 'smtp.example.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = $_ENV['SMTP_USER'] ?? 'user@example.com';
            $mail->Password   = $_ENV['SMTP_PASS'] ?? 'secret';
            // Use SMTPS encryption (typically on port 465)
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = $_ENV['SMTP_PORT'] ?? 465;
            
            // Recipients
            $mail->setFrom($_ENV['MAIL_FROM'] ?? 'noreply@example.com', $_ENV['MAIL_FROM_NAME'] ?? 'Your App');
            $mail->addAddress($to, $toName);
            
            // Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $body;
            $mail->AltBody = $altBody;
            
            $mail->send();
            return true;
        } catch (MailException $e) {
            error_log("Mailer Error: " . $mail->ErrorInfo);
            return "Mailer Error: " . $mail->ErrorInfo;
        }
    }
    
    /**
     * Send a verification email.
     */
    public static function sendVerificationEmail($to, $toName, $verificationLink) {
        $subject = 'Please verify your email';
        $body = "Hello " . htmlspecialchars($toName) . ",<br><br>"
              . "Please verify your email by clicking <a href=\"$verificationLink\">this link</a>.<br><br>"
              . "Thank you.";
        $altBody = "Hello $toName, please verify your email by visiting: $verificationLink";
        return self::sendEmail($to, $toName, $subject, $body, $altBody);
    }
    
    /**
     * Send a reset password email.
     */
    public static function sendResetPasswordEmail($to, $toName, $resetLink) {
        $subject = 'Reset Your Password';
        $body = "Hello " . htmlspecialchars($toName) . ",<br><br>"
              . "You requested a password reset. Please reset your password by clicking <a href=\"$resetLink\">this link</a>.<br><br>"
              . "If you did not request a password reset, please ignore this email.";
        $altBody = "Hello $toName, please reset your password by visiting: $resetLink";
        return self::sendEmail($to, $toName, $subject, $body, $altBody);
    }
}
