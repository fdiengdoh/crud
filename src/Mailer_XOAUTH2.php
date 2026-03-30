<?php
/**
 * To use XOAUTH2 Authentication with Google mail:
 * 1. Remove the Mailer.php file and replace it with this content
 * 2. Keep in mind you need to setup OAuth with Google or other service
 * 3. Check PHPMailer repo for more setup examples
 * 4. Requires: phpmailer/phpmailer and league/oauth2-client packages
 */
declare(strict_types=1);

namespace App;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as MailException;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\OAuth;
use League\OAuth2\Client\Provider\Google as GoogleProvider;

class Mailer
{
    /**
     * Send an email using PHPMailer with Google OAuth 2.0.
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
            // Server settings for Google SMTP with OAuth
            $mail->SMTPDebug = SMTP::DEBUG_OFF;
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port = 465;

            // OAuth 2.0 settings for Google
            $mail->AuthType = 'XOAUTH2';

            // Retrieve Google OAuth 2.0 credentials from environment variables
            $clientId = (string)($_ENV['GOOGLE_CLIENT_ID'] ?? '');
            $clientSecret = (string)($_ENV['GOOGLE_CLIENT_SECRET'] ?? '');
            $refreshToken = (string)($_ENV['GOOGLE_REFRESH_TOKEN'] ?? '');
            $fromEmail = (string)($_ENV['MAIL_FROM'] ?? '');

            // Validate that required OAuth credentials are set
            if (empty($clientId) || empty($clientSecret) || empty($refreshToken) || empty($fromEmail)) {
                throw new MailException(
                    "Google OAuth credentials (GOOGLE_CLIENT_ID, GOOGLE_CLIENT_SECRET, "
                    . "GOOGLE_REFRESH_TOKEN, MAIL_FROM) must be set in environment variables."
                );
            }

            // Instantiate League's Google Provider
            $oauthProvider = new GoogleProvider([
                'clientId' => $clientId,
                'clientSecret' => $clientSecret,
            ]);

            // Set the OAuth 2.0 access token provider for PHPMailer
            $mail->setOAuth(
                new OAuth([
                    'provider' => $oauthProvider,
                    'clientId' => $clientId,
                    'clientSecret' => $clientSecret,
                    'refreshToken' => $refreshToken,
                    'userName' => $fromEmail,
                ])
            );

            // Recipients
            $mailFromName = (string)($_ENV['MAIL_FROM_NAME'] ?? 'Your App');
            $mail->setFrom($fromEmail, $mailFromName);
            $mail->addAddress($to, $toName);

            // Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $body;
            $mail->AltBody = $altBody;

            $mail->send();
            return true;
        } catch (MailException $e) {
            // Log the PHPMailer specific error message
            error_log("Mailer Error: " . $mail->ErrorInfo);

            // Provide user-friendly error message, especially for OAuth issues
            $errorMessage = $e->getMessage();
            if (
                str_contains($errorMessage, 'invalid_grant')
                || str_contains($errorMessage, 'Authentication failed')
            ) {
                return "Mailer Error: Failed to authenticate with Google. Please ensure your "
                    . "Client ID, Client Secret, Refresh Token, and sender email are correct and valid. "
                    . "The refresh token might have expired or been revoked.";
            }
            return "Mailer Error: " . $mail->ErrorInfo;
        } catch (\Exception $e) {
            // Catch any other unexpected exceptions
            error_log("General Error in Mailer: " . $e->getMessage());
            return "An unexpected error occurred: " . $e->getMessage();
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
            . "Please verify your email by clicking <a href=\""
            . htmlspecialchars($verificationLink, ENT_QUOTES, 'UTF-8') . "\">this link</a>.<br><br>"
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
            . "You requested a password reset. Please reset your password by clicking <a href=\""
            . htmlspecialchars($resetLink, ENT_QUOTES, 'UTF-8') . "\">this link</a>.<br><br>"
            . "If you did not request a password reset, please ignore this email.";
        $altBody = "Hello $toName, please reset your password by visiting: $resetLink";
        return self::sendEmail($to, $toName, $subject, $body, $altBody);
    }
}
