<?php
class Database
{
    public function Connect(){
        $servername = "localhost";
        $username = "root";
        $password = "";
        $dbname = "ssd_db";

        // Create connection
        $conn = new mysqli($servername, $username, $password, $dbname);

        // Check connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }
        return $conn;
    }

    public function CreateUser($inputEmail, $inputPassword){
        $conn = $this->Connect();
        $hashedPassword = password_hash($inputPassword, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (Email, Password) VALUES (?, ?)");
        $stmt->bind_param("ss", $inputEmail, $hashedPassword);
        $stmt->execute();
        $stmt->close();
        $conn->close();
    }

    public function FindUser($inputEmail, $inputPassword){
        $user = null;
        $conn = $this->Connect();
        $stmt = $conn->prepare("SELECT * FROM users WHERE Email = ?");
        $stmt->bind_param("s", $inputEmail);
        $stmt->execute();
        $result = $stmt->get_result();
        if($result->num_rows === 0){
            return null;
        }
        while($row = $result->fetch_assoc()) {
            if(password_verify($inputPassword, $row['Password'])){
                $user = [];
                $user['id'] = $row['UserId'];
                $user['email'] = $row['Email'];
            }            
        }
        $stmt->close();
        $conn->close();
        return $user;
    }
}

?>