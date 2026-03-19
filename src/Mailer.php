<?php
namespace App;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as MailException;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\OAuth; // Import the PHPMailer OAuth class
use League\OAuth2\Client\Provider\Google as GoogleProvider; // Use League's Google Provider

class Mailer {

    /**
     * Send an email using PHPMailer with Google OAuth 2.0.
     *
     * @param string $to Recipient email address.
     * @param string $toName Recipient name.
     * @param string $subject Email subject.
     * @param string $body HTML email body.
     * @param string $altBody Plain-text email body.
     * @return bool|string Returns true on success or error message on failure.
     */
    public static function sendEmail($to, $toName, $subject, $body, $altBody) {
        $mail = new PHPMailer(true); // Passing `true` enables exceptions

        try {
            // Server settings for Google SMTP with OAuth
            $mail->SMTPDebug = SMTP::DEBUG_OFF; // Set to SMTP::DEBUG_SERVER for debugging, DEBUG_OFF for production
            $mail->isSMTP();                     // Send using SMTP
            $mail->Host       = 'smtp.gmail.com';  // Google's SMTP server
            $mail->SMTPAuth   = true;              // Enable SMTP authentication
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // Enable implicit TLS encryption
            $mail->Port       = 465;               // Standard port for SMTPS

            // OAuth 2.0 settings for Google
            $mail->AuthType = 'XOAUTH2';

            // --- Retrieve Google OAuth 2.0 credentials from environment variables ---
            // Ensure these environment variables are set in your application's .env file or server configuration
            $clientId     = $_ENV['GOOGLE_CLIENT_ID'];
            $clientSecret = $_ENV['GOOGLE_CLIENT_SECRET'];
            $refreshToken = $_ENV['GOOGLE_REFRESH_TOKEN'];
            $fromEmail    = $_ENV['MAIL_FROM']; // The Google account email that you authorized


            // Validate that required OAuth credentials are set
            if (empty($clientId) || empty($clientSecret) || empty($refreshToken) || empty($fromEmail)) {
                throw new MailException("Google OAuth credentials (CLIENT_ID, CLIENT_SECRET, REFRESH_TOKEN, MAIL_FROM) must be set in environment variables.");
            }

            // Instantiate League's Google Provider directly
            // Note: The redirectUri is primarily for the *initial* authorization flow,
            // not typically required for refreshing tokens in a background process.
            // However, the constructor for GoogleProvider may require it.
            // Use your actual redirect URI from Google Cloud Console if needed.
            $oauthProvider = new GoogleProvider([
                'clientId'     => $clientId,
                'clientSecret' => $clientSecret,
                // 'redirectUri'  => 'YOUR_REDIRECT_URI_HERE', // Only if explicitly required by your League setup
            ]);


            // Set the OAuth 2.0 access token provider for PHPMailer
            $mail->setOAuth(
                new OAuth( // Use the imported OAuth class
                    [
                        'provider'      => $oauthProvider, // Pass the instance of League's Google Provider
                        'clientId'      => $clientId,
                        'clientSecret'  => $clientSecret,
                        'refreshToken'  => $refreshToken,
                        'userName'      => $fromEmail, // User email to send from
                    ]
                )
            );

            // Recipients
            $mail->setFrom($fromEmail, $_ENV['MAIL_FROM_NAME'] ?? 'Your App');
            $mail->addAddress($to, $toName);
            
            // Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $body;
            $mail->AltBody = $altBody;
            
            $mail->send();
            return true;

        } catch (MailException $e) {
            // Log the PHPMailer specific error message
            error_log("Mailer Error: " . $mail->ErrorInfo);
            
            // Provide a more user-friendly error message, especially for OAuth issues
            if (strpos($e->getMessage(), 'invalid_grant') !== false || strpos($e->getMessage(), 'Authentication failed') !== false) {
                 return "Mailer Error: Failed to authenticate with Google. Please ensure your Client ID, Client Secret, Refresh Token, and sender email are correct and valid. The refresh token might have expired or been revoked.";
            } else {
                 return "Mailer Error: " . $mail->ErrorInfo;
            }
        } catch (\Exception $e) { // Catch any other unexpected exceptions
            error_log("General Error in Mailer: " . $e->getMessage());
            return "An unexpected error occurred: " . $e->getMessage();
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
