<?php
namespace App\Controllers;

use App\Database;
use Delight\Auth\Auth;
use App\Mailer;

class SubscribersController{
    private $pdo;

    //connect to database
    public function __construct() {
        $this->pdo = Database::getConnection();
    }

    /*
    * This function will add a subscriber 
    * $email for email
    * $name of the subscriber
    * $resetLink if needed
    */
    public function subscribe($email, $name, $resetLink = '') {
        // 1. Setup Link (moved to the start for use in both branches)
        if(!$resetLink){
            $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https://" : "http://";
            // Use a default for CLI/non-browser contexts (like unit tests)
            $host = $_SERVER['HTTP_HOST'] ?? 'localhost'; 
            $resetLink = $protocol . $host . '/subscriber';
        }
    
        $token = Auth::createUuid(); // Generate a token for a potential new subscriber
    
        try{
            // 2. CHECK IF SUBSCRIBER EXISTS
            $stmt = $this->pdo->prepare("SELECT id, token, is_verified FROM subscribers WHERE email = :email");
            $stmt->execute(['email' => $email]);
            $existingSubscriber = $stmt->fetch(\PDO::FETCH_ASSOC);
    
            // --- Existing Subscriber Logic ---
            if ($existingSubscriber) {
                $is_verified = (int)($existingSubscriber['is_verified'] ?? 0); 
                $current_token = $existingSubscriber['token'];
    
                if ($is_verified === 1) {
                    // Case A: Already Verified
                    return ['success' => true, 'message' => "This email is already subscribed and verified."];
                } else {
                    // Case B: Not Verified - RESEND VERIFICATION
                    
                    // OPTION 1: Use existing token and just update the name (safer and simpler)
                    $token_to_use = $current_token;
                    
                    // OPTION 2: Generate a NEW token and update the database (more secure, requires extra query)
                    /*
                    $token_to_use = Auth::createUuid();
                    $updateStmt = $this->pdo->prepare("UPDATE subscribers SET full_name = :full_name, token = :token, is_verified = 0 WHERE email = :email");
                    $updateStmt->execute(['full_name' => $name, 'token' => $token_to_use, 'email' => $email]);
                    */
    
                    // If using OPTION 1, just update name if needed
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
            
            // --- New Subscriber Logic (Only runs if $existingSubscriber is false) ---
            
            // Insert new subscriber (Use the $token generated at the start)
            // Add `is_verified` column, default to 0 (unverified)
            $stmt = $this->pdo->prepare("INSERT INTO subscribers (email, full_name, token, is_verified) VALUES (:email, :full_name, :token, 0)");
            $stmt->execute(['email' => $email, 'full_name' => $name, 'token' => $token]);
    
            // Send verification email
            $verifyLink = $resetLink . "?verify=true&email=$email&token=$token";
            
            $htmlText = "<p><b>Dear $name,</b></p><h1>Please Confirm Your Subscription</h1><p><a target=\"_blank\" style=\"display: inline-block; color: #ffffff; background-color: #3498db; border: solid 1px #3498db; border-radius: 5px; box-sizing: border-box; cursor: pointer; text-decoration: none; font-size: 14px; font-weight: bold; margin: 0; padding: 12px 25px; text-transform: capitalize; border-color: #3498db;\" href='$verifyLink'>Yes, Verify my Subscription</a></p><p>If you received this email by mistake, simply delete it. You won't be subscribed if you don't click the confirmation link above.</p><p>For questions about this list, please contact: info@fdiengdoh.com";
            
            $result = Mailer::sendEmail($email, $name, 'Please Confirm your subscription', $htmlText, "Visit link to confirm: $verifyLink");
    
            if($result === true){
                return ['success' => true , 'message' => "A verification email has been sent to {$email}."];
            } else {
                // Note: If email sending fails, the subscriber is still in the DB. You may want to log this failure.
                return ['success' => false , 'message' => "Verification email could not be sent."];
            }
            
        } catch(\Exception $e){
            error_log('Error subscribing: ' . $e->getMessage());
            // Return a more informative error for the user if the exception is due to a DB issue other than a duplicate (which is now handled)
            return ['success' => false, 'message' => "An unexpected error occurred during subscription."];
        }
    }

    /**
     * Adds a new subscriber to the database.
     * This method should handle duplicate emails (e.g., by doing nothing or updating details)
     * and set verification status/token as per your application's logic.
     * For import, typically new subscribers are set as unverified initially.
     *
     * @param string $email The subscriber's email.
     * @param string|null $full_name The subscriber's full name (optional).
     * @return bool True if the subscriber was added/updated, false on error or if already exists.
     */
    public function addSubscriber($email, $full_name = '') {
        empty($full_name) ? $full_name = $email : $full_name = $full_name;
        try {
            // Check if subscriber already exists
            $stmt = $this->pdo->prepare("SELECT id FROM subscribers WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                // Subscriber already exists, optionally update name or just skip
                // For a simple import, we might just skip to avoid overwriting verification status
                // Or you could update:
                // $updateStmt = $this->pdo->prepare("UPDATE subscribers SET full_name = ? WHERE email = ?");
                // $updateStmt->execute([$full_name, $email]);
                // return true; // Indicate it was 'handled'
                return false; // Indicate it was not added as new (already exists)
            }

            // Generate a verification token (if your system requires it for new sign-ups)
            $token = Auth::createUuid();//

            // Insert new subscriber
            $stmt = $this->pdo->prepare("INSERT INTO subscribers (email, full_name, token, is_verified) VALUES (:email, :full_name, :token, :is_verified)");
            // Assuming imported subscribers are NOT verified by default, just like regular sign-ups
            return $stmt->execute(['email' => $email, 'full_name' => $full_name, 'token' => $token, 'is_verified' => 1]);
        } catch (PDOException $e) {
            error_log('Error adding subscriber during import: ' . $e->getMessage());
            return false;
        }
    }


    /**
     * This function is to verify subscriber email 
     * before sending newsletter is allowed
     */
    
    public function verify($email, $token) {
        // 1. Fetch the subscriber record using email
        $stmt = $this->pdo->prepare("SELECT token, is_verified FROM subscribers WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $subscriber = $stmt->fetch(\PDO::FETCH_ASSOC); // Use FETCH_ASSOC for clarity/consistency
    
        // --- Check if the subscriber exists ---
        if ($subscriber === false) {
            return "Error: This email is not registered for subscription.";
        }
        // -------------------------------------
    
        // Now we know $subscriber is an array and we can safely access its keys
        $stoken = $subscriber['token'];
        $sverified = $subscriber['is_verified'];
    
        // 2. Compare token and check verification status
        // Note: I recommend explicitly casting $sverified to a boolean or using a strict comparison 
        // depending on how 'is_verified' is stored (e.g., '0'/'1' or 0/1)
        if ($token === $stoken && $sverified == 0) { // Assuming '0' means not verified
            // 3. Verify the user
            $stmt = $this->pdo->prepare("UPDATE subscribers SET is_verified = '1' WHERE token = :token AND email = :email");
            $result = $stmt->execute(['token' => $token, 'email' => $email]);
    
            if ($result === true) {
                $statusMessage = "Your email is verified for subscription.";
            } else {
                // This is a more specific error, perhaps log the actual PDO error for debugging
                $statusMessage = "Error: There was a problem updating the verification status.";
            }
        } else if ($token !== $stoken) {
            $statusMessage = "Error: Invalid verification token for this email.";
        } else {
            // If the token matches but $sverified is already true ('1')
            $statusMessage = "This email has already been verified.";
        }
    
        return $statusMessage;
    }


    /**
     * Function to unsubscribe a subscriber
     */
    public function unsubscribe($email){
        $stmt = $this->pdo->prepare("DELETE FROM subscribers WHERE email = :email");
        $result = $stmt->execute(['email' => $email ]);
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

    public function getSubscribers(){
        $stmt = $this->pdo->prepare("SELECT * FROM subscribers");
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
