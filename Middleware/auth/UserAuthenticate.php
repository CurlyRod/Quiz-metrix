<?php
require_once '../../student/home/db_connect.php';
class UserAuthenticate{  

    private $conn; 

    public function __construct($dbConn) {
        $this->conn = $dbConn;
    }
    
    public function GetUserLogin($email) {
        $stmt = $this->conn->prepare("SELECT * FROM  user_credential WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result(); 
         if ($result->num_rows > 0) { 
            $row = $result->fetch_assoc();
            $userInfo = array($row['email'], $row['id']);
            return [
                'isAuthenticate' => true,
                'userinfo' => [$row['email'], $row['id']]
            ];
        } 
        else
        {
            return [
                'isAuthenticate' => false,
                'message' => 'User not found'
            ];
        }
    } 

    public function CheckIfNewUserLogin($email) {
        $stmt = $this->conn->prepare("SELECT * FROM  user_credential WHERE email = ? AND password = '' ");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result(); 
         if ($result->num_rows > 0) { 
            $row = $result->fetch_assoc();
            return [
                'isAuthenticate' => true, 
                'email' =>  $row['email']    
            ];
        }  
    } 



    public function RegisterUser($email) 
    {   
        $stmt = $this->conn->prepare("SELECT * FROM user_credential WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result(); 

        if ($result->num_rows === 0) 
        {           
            $stmt = $this->conn->prepare("INSERT INTO user_credential (email) VALUES (?)");
            $stmt->bind_param("s", $email);
            if ($stmt->execute()) {
                return $this->GetUserLogin($email); 
            } else {
                return [
                    'isAuthenticate' => false,
                    'message' => 'Insert failed'
                ];
            }
        } else {
            return $this->GetUserLogin($email);
        }

        $stmt->close();
    }  
    }   


 

