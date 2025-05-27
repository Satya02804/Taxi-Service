<?php
class User {
    private $conn;
    private $table_name = "users";

    public $user_id;
    public $username;
    public $password;
    public $email;
    public $full_name;
    public $phone_number;
    public $address;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function register() {
        $query = "INSERT INTO " . $this->table_name . "
                SET
                    username = :username,
                    password = :password,
                    email = :email,
                    full_name = :full_name,
                    phone_number = :phone_number,
                    address = :address";

        $stmt = $this->conn->prepare($query);

        // Sanitize and hash password
        $this->username = htmlspecialchars(strip_tags($this->username));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->full_name = htmlspecialchars(strip_tags($this->full_name));
        $this->phone_number = htmlspecialchars(strip_tags($this->phone_number));
        $this->address = htmlspecialchars(strip_tags($this->address));
        $this->password = password_hash($this->password, PASSWORD_BCRYPT);

        // Bind values
        $stmt->bindParam(":username", $this->username);
        $stmt->bindParam(":password", $this->password);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":full_name", $this->full_name);
        $stmt->bindParam(":phone_number", $this->phone_number);
        $stmt->bindParam(":address", $this->address);

        try {
            return $stmt->execute();
        } catch(PDOException $e) {
            return false;
        }
    }

    public function login($username, $password) {
        $query = "SELECT user_id, username, password, full_name 
                FROM " . $this->table_name . "
                WHERE username = ?
                LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $username);
        $stmt->execute();

        if($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if(password_verify($password, $row['password'])) {
                // Set session variables
                $_SESSION['user_id'] = $row['user_id'];
                $_SESSION['username'] = $row['username'];
                $_SESSION['full_name'] = $row['full_name'];
                return true;
            }
        }
        return false;
    }


public function getUserProfile($user_id) {
    $query = "SELECT user_id, username, email, full_name, phone_number, address 
              FROM " . $this->table_name . " 
              WHERE user_id = ? 
              LIMIT 1";

    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(1, $user_id);
    $stmt->execute();

    if($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        return $row;
    } else {
        return null;
    }
}



    public function getUserReport() {
        $query = "SELECT 
                    COUNT(*) AS total_users,
                    COUNT(*) AS active_users,
                    0 AS inactive_users
                  FROM users";
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [
                'total_users' => 0,
                'active_users' => 0,
                'inactive_users' => 0
            ];
        }
    }

    public function getUserById() {
        $query = "SELECT username, full_name, email, phone_number, address 
                  FROM " . $this->table_name . " 
                  WHERE user_id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->user_id);
        
        if($stmt->execute()) {
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        
        return false;
    }

    public function updatePassword($current_password, $new_password) {
        // First verify the current password
        $query = "SELECT password FROM " . $this->table_name . " WHERE user_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->user_id);
        $stmt->execute();
        
        if($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if(password_verify($current_password, $row['password'])) {
                // Current password is correct, update to new password
                $query = "UPDATE " . $this->table_name . " 
                         SET password = :password 
                         WHERE user_id = :user_id";
                
                $stmt = $this->conn->prepare($query);
                
                // Hash the new password
                $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
                
                // Bind parameters
                $stmt->bindParam(":password", $hashed_password);
                $stmt->bindParam(":user_id", $this->user_id);
                
                // Execute the query
                return $stmt->execute();
            }
        }
        
        return false;
    }
}
?> 