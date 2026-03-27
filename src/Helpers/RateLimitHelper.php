<?php
namespace App\Helpers;

use App\Database;
use PDO;

class RateLimitHelper {
    /**
     * @param string $action  The unique name for the action (e.g., 'subscribe')
     * @param int $limit      Max number of requests allowed
     * @param int $timeFrame  Time window in seconds (e.g., 60 for 1 minute)
     * @return bool           True if allowed, False if throttled
     */
    public static function isAllowed($action, $limit = 5, $timeFrame = 60) {
        $pdo = Database::getConnection();
        $ip = $_SERVER['REMOTE_ADDR'];
        $now = time();

        // 1. Clean up old entries (Optional: Move to a cron job if site gets heavy traffic)
        $pdo->prepare("DELETE FROM rate_limits WHERE last_request < ?")
            ->execute([$now - $timeFrame]);

        // 2. Check current status for this IP and action
        $stmt = $pdo->prepare("SELECT request_count, last_request FROM rate_limits WHERE ip_address = ? AND action = ?");
        $stmt->execute([$ip, $action]);
        $record = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($record) {
            // If the time window has passed, reset the counter
            if (($now - $record['last_request']) > $timeFrame) {
                $stmt = $pdo->prepare("UPDATE rate_limits SET request_count = 1, last_request = ? WHERE ip_address = ? AND action = ?");
                $stmt->execute([$now, $ip, $action]);
                return true;
            }

            // If they are over the limit, block them
            if ($record['request_count'] >= $limit) {
                return false;
            }

            // Otherwise, increment the count
            $stmt = $pdo->prepare("UPDATE rate_limits SET request_count = request_count + 1 WHERE ip_address = ? AND action = ?");
            $stmt->execute([$ip, $action]);
            return true;
        }

        // 3. First request from this IP for this action
        $stmt = $pdo->prepare("INSERT INTO rate_limits (ip_address, action, last_request, request_count) VALUES (?, ?, ?, 1)");
        $stmt->execute([$ip, $action, $now]);
        return true;
    }
}
