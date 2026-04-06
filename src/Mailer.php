<?php
declare(strict_types=1);

namespace App;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as MailException;
use PHPMailer\PHPMailer\SMTP;

class Mailer
{
    /**
     * Send an email using PHPMailer.
     *
     * @param string $to Recipient email address
     * @param string $toName Recipient name
     * @param string $subject Email subject
     * @param string $body HTML email body
     * @param string $altBody Plain-text email body
     * @return bool|string Returns true on success or error message on failure
     */
    public static function sendEmail(
        string $to,
        string $toName,
        string $subject,
        string $body,
        string $altBody
    ): bool|string {
        $mail = new PHPMailer(true);
        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host = (string)($_ENV['SMTP_HOST'] ?? 'smtp.example.com');
            $mail->SMTPAuth = true;
            $mail->Username = (string)($_ENV['SMTP_USER'] ?? 'user@example.com');
            $mail->Password = (string)($_ENV['SMTP_PASS'] ?? 'secret');
            // Use SMTPS encryption (typically on port 465)
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port = (int)($_ENV['SMTP_PORT'] ?? 465);

            // Recipients
            $mail->setFrom(
                (string)($_ENV['MAIL_FROM'] ?? 'noreply@example.com'),
                (string)($_ENV['MAIL_FROM_NAME'] ?? 'Your App')
            );
            $mail->addAddress($to, $toName);

            // Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $body;
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
     *
     * @param string $to Recipient email address
     * @param string $toName Recipient name
     * @param string $verificationLink Verification link
     * @return bool|string
     */
    public static function sendVerificationEmail(
        string $to,
        string $toName,
        string $verificationLink
    ): bool|string {
        $subject = 'Please verify your email';
        $body = "Hello " . htmlspecialchars($toName, ENT_QUOTES, 'UTF-8') . ",<br><br>"
            . "Please verify your email by clicking <a href=\"" . htmlspecialchars($verificationLink, ENT_QUOTES, 'UTF-8') . "\">this link</a>.<br><br>"
            . "Thank you.";
        $altBody = "Hello $toName, please verify your email by visiting: $verificationLink";
        return self::sendEmail($to, $toName, $subject, $body, $altBody);
    }

    /**
     * Send a reset password email.
     *
     * @param string $to Recipient email address
     * @param string $toName Recipient name
     * @param string $resetLink Password reset link
     * @return bool|string
     */
    public static function sendResetPasswordEmail(
        string $to,
        string $toName,
        string $resetLink
    ): bool|string {
        $subject = 'Reset Your Password';
        $body = "Hello " . htmlspecialchars($toName, ENT_QUOTES, 'UTF-8') . ",<br><br>"
            . "You requested a password reset. Please reset your password by clicking <a href=\"" . htmlspecialchars($resetLink, ENT_QUOTES, 'UTF-8') . "\">this link</a>.<br><br>"
            . "If you did not request a password reset, please ignore this email.";
        $altBody = "Hello $toName, please reset your password by visiting: $resetLink";
        return self::sendEmail($to, $toName, $subject, $body, $altBody);
    }
}
