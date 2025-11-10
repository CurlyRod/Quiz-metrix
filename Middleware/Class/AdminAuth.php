<?php
namespace Middleware\Class;

class AdminAuth {
    private $conn;
    private $table_name = "admin_users";

    public function __construct() {
        $host = 'localhost';
        $db_name = 'quizmetrix';
        $username = 'root';
        $password = '';
        
        try {
            $this->conn = new \PDO("mysql:host=$host;dbname=$db_name;charset=utf8mb4", $username, $password);
            $this->conn->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            error_log("Database connection successful");
        } catch(\PDOException $exception) {
            error_log("Database connection failed: " . $exception->getMessage());
            throw new \Exception("Database connection failed: " . $exception->getMessage());
        }
    }

    public function authenticate($username, $password) {
        try {
            $query = "SELECT id, username, password_hash FROM " . $this->table_name . " WHERE username = :username LIMIT 1";
            error_log("Executing query: " . $query);
            error_log("Looking for username: " . $username);
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':username', $username);
            $stmt->execute();

            $rowCount = $stmt->rowCount();
            error_log("Query returned $rowCount rows");

            if ($rowCount == 1) {
                $row = $stmt->fetch(\PDO::FETCH_ASSOC);
                $hashed_password = $row['password_hash'];
                
                error_log("Found user ID: " . $row['id']);
                error_log("Stored hash: " . $hashed_password);
                error_log("Password verify attempt...");

                $passwordValid = password_verify($password, $hashed_password);
                error_log("Password verify result: " . ($passwordValid ? 'VALID' : 'INVALID'));

                if ($passwordValid) {
                    $this->startSession();
                    $_SESSION['admin_logged_in'] = true;
                    $_SESSION['admin_id'] = $row['id'];
                    $_SESSION['admin_username'] = $row['username'];
                    
                    error_log("Session started - admin_logged_in: " . ($_SESSION['admin_logged_in'] ? 'true' : 'false'));
                    return true;
                }
            } else {
                error_log("No user found with username: $username");
            }
            return false;
        } catch(\PDOException $exception) {
            error_log("Authentication PDO error: " . $exception->getMessage());
            throw new \Exception("Authentication error: " . $exception->getMessage());
        }
    }

    private function startSession() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
            error_log("Session started with ID: " . session_id());
        }
    }

    public function isLoggedIn() {
        $this->startSession();
        return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
    }

    public function logout() {
        $this->startSession();
        $_SESSION = array();
        session_destroy();
    }
}
?>