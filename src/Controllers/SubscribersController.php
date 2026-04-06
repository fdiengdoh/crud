<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Database;
use Delight\Auth\Auth;
use PDO;
use PDOException;
use App\Mailer;

class SubscribersController
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    public function subscribe(string $email, string $name, string $resetLink = ''): array
    {
        if(!$resetLink){
            $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https://" : "http://";
            $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
            $resetLink = $protocol . $host . '/subscriber';
        }

        $token = Auth::createUuid();

        try {
            $stmt = $this->pdo->prepare("SELECT id, token, is_verified FROM subscribers WHERE email = :email");
            $stmt->execute(['email' => $email]);
            $existingSubscriber = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($existingSubscriber) {
                $is_verified = (int)($existingSubscriber['is_verified'] ?? 0);
                $current_token = $existingSubscriber['token'];

                if ($is_verified === 1) {
                    return ['success' => true, 'message' => "This email is already subscribed and verified."];
                } else {
                    $token_to_use = $current_token;
                    $updateStmt = $this->pdo->prepare("UPDATE subscribers SET full_name = :full_name WHERE email = :email");
                    $updateStmt->execute(['full_name' => $name, 'email' => $email]);

                    $verifyLink = $resetLink . "?verify=true&email=$email&token=$token_to_use";
                    $htmlText = "<p><b>Dear $name,</b></p><h1>Please Confirm Your Subscription</h1><p><a target=\"_blank\" style=\"display: inline-block; color: #ffffff; background-color: #3498db; border: solid 1px #3498db; border-radius: 5px; box-sizing: border-box; cursor: pointer; text-decoration: none; font-size: 14px; font-weight: bold; margin: 0; padding: 12px 25px; text-transform: capitalize; border-color: #3498db;\" href='$verifyLink'>Yes, Verify my Subscription</a></p><p>If you received this email by mistake, simply delete it. You won't be subscribed if you don't click the confirmation link above.</p><p>For questions about this list, please contact: info@fdiengdoh.com";

                    $result = Mailer::sendEmail($email, $name, 'Please Confirm your subscription', $htmlText, "Visit link to confirm: $verifyLink");

                    if ($result === true) {
                        return ['success' => true, 'message' => "This email was already registered. A verification email has been re-sent to {$email}."];
                    } else {
                        return ['success' => false, 'message' => "Verification email could not be re-sent."];
                    }
                }
            }

            $stmt = $this->pdo->prepare("INSERT INTO subscribers (email, full_name, token, is_verified) VALUES (:email, :full_name, :token, 0)");
            $stmt->execute(['email' => $email, 'full_name' => $name, 'token' => $token]);

            $verifyLink = $resetLink . "?verify=true&email=$email&token=$token";
            $htmlText = "<p><b>Dear $name,</b></p><h1>Please Confirm Your Subscription</h1><p><a target=\"_blank\" style=\"display: inline-block; color: #ffffff; background-color: #3498db; border: solid 1px #3498db; border-radius: 5px; box-sizing: border-box; cursor: pointer; text-decoration: none; font-size: 14px; font-weight: bold; margin: 0; padding: 12px 25px; text-transform: capitalize; border-color: #3498db;\" href='$verifyLink'>Yes, Verify my Subscription</a></p><p>If you received this email by mistake, simply delete it. You won't be subscribed if you don't click the confirmation link above.</p><p>For questions about this list, please contact: info@fdiengdoh.com";

            $result = Mailer::sendEmail($email, $name, 'Please Confirm your subscription', $htmlText, "Visit link to confirm: $verifyLink");

            if($result === true){
                return ['success' => true, 'message' => "A verification email has been sent to {$email}."];
            } else {
                return ['success' => false, 'message' => "Verification email could not be sent."];
            }

        } catch(\Exception $e){
            error_log('Error subscribing: ' . $e->getMessage());
            return ['success' => false, 'message' => "An unexpected error occurred during subscription."];
        }
    }

    public function addSubscriber(string $email, string $full_name = ''): bool
    {
        empty($full_name) ? $full_name = $email : $full_name = $full_name;
        try {
            $stmt = $this->pdo->prepare("SELECT id FROM subscribers WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                return false;
            }

            $token = Auth::createUuid();
            $stmt = $this->pdo->prepare("INSERT INTO subscribers (email, full_name, token, is_verified) VALUES (:email, :full_name, :token, :is_verified)");
            return $stmt->execute(['email' => $email, 'full_name' => $full_name, 'token' => $token, 'is_verified' => 1]);
        } catch (PDOException $e) {
            error_log('Error adding subscriber during import: ' . $e->getMessage());
            return false;
        }
    }

    public function verify(string $email, string $token): string
    {
        $stmt = $this->pdo->prepare("SELECT token, is_verified FROM subscribers WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $subscriber = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($subscriber === false) {
            return "Error: This email is not registered for subscription.";
        }

        $stoken = $subscriber['token'];
        $sverified = $subscriber['is_verified'];

        if ($token === $stoken && $sverified == 0) {
            $stmt = $this->pdo->prepare("UPDATE subscribers SET is_verified = '1' WHERE token = :token AND email = :email");
            $result = $stmt->execute(['token' => $token, 'email' => $email]);

            if ($result === true) {
                $statusMessage = "Your email is verified for subscription.";
            } else {
                $statusMessage = "Error: There was a problem updating the verification status.";
            }
        } else if ($token !== $stoken) {
            $statusMessage = "Error: Invalid verification token for this email.";
        } else {
            $statusMessage = "This email has already been verified.";
        }

        return $statusMessage;
    }

    public function unsubscribe(string $email): string
    {
        $stmt = $this->pdo->prepare("DELETE FROM subscribers WHERE email = :email");
        $result = $stmt->execute(['email' => $email]);
        if($result){
            $affected_rows = $stmt->rowCount();
            if($affected_rows > 0 ){
                $statusMessage = "Your email is deleted from our email list sucessfully";
            }else{
                $statusMessage = "There is no entry of your email in our email list.";
            }
        }else{
            $statusMessage = "There is an error try again later";
        }
        return $statusMessage;
    }

    public function getSubscribers(): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM subscribers");
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
