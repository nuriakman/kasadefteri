<?php
namespace App\Models;

class User {
    // Veritabanı bağlantısı ve tablo adı
    private $conn;
    private $table_name = "users";

    // Nesne özellikleri
    public $id;
    public $userName;
    public $email;
    public $password;
    public $googleId;
    public $role;
    public $avatar;
    public $createdAt;
    public $updatedAt;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Kullanıcı oluştur
    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                SET
                    userName = :userName,
                    email = :email,
                    password = :password,
                    role = :role,
                    avatar = :avatar,
                    createdAt = :createdAt,
                    updatedAt = :updatedAt";

        $stmt = $this->conn->prepare($query);

        // Sanitize
        $this->userName = htmlspecialchars(strip_tags($this->userName));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->password = htmlspecialchars(strip_tags($this->password));
        $this->role = htmlspecialchars(strip_tags($this->role));
        $this->avatar = htmlspecialchars(strip_tags($this->avatar));

        // Hash password
        $password_hash = null;
        if ($this->password) {
            $password_hash = password_hash($this->password, PASSWORD_BCRYPT);
        }

        // Bind values
        $stmt->bindParam(":userName", $this->userName);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":password", $password_hash);
        $stmt->bindParam(":role", $this->role);
        $stmt->bindParam(":avatar", $this->avatar);
        $stmt->bindParam(":createdAt", date('Y-m-d H:i:s'));
        $stmt->bindParam(":updatedAt", date('Y-m-d H:i:s'));

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Google ile kullanıcı oluştur veya güncelle
    public function createOrUpdateWithGoogle($googleData) {
        // Google ID ile kullanıcı ara
        $query = "SELECT id FROM " . $this->table_name . " WHERE googleId = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $googleData['id']);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            // Kullanıcı varsa güncelle
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            $query = "UPDATE " . $this->table_name . "
                    SET
                        userName = :userName,
                        email = :email,
                        avatar = :avatar,
                        updatedAt = :updatedAt
                    WHERE id = :id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":id", $row['id']);
        } else {
            // Yeni kullanıcı oluştur
            $query = "INSERT INTO " . $this->table_name . "
                    SET
                        userName = :userName,
                        email = :email,
                        googleId = :googleId,
                        avatar = :avatar,
                        role = :role,
                        createdAt = :createdAt,
                        updatedAt = :updatedAt";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":googleId", $googleData['id']);
            $stmt->bindParam(":role", "user");
            $stmt->bindParam(":createdAt", date('Y-m-d H:i:s'));
        }

        // Ortak alanları bağla
        $userName = explode('@', $googleData['email'])[0];
        $stmt->bindParam(":userName", $userName);
        $stmt->bindParam(":email", $googleData['email']);
        $stmt->bindParam(":avatar", $googleData['picture']);
        $stmt->bindParam(":updatedAt", date('Y-m-d H:i:s'));

        if($stmt->execute()) {
            return $this->findByGoogleId($googleData['id']);
        }
        return false;
    }

    // Google ID ile kullanıcı getir
    public function findByGoogleId($googleId) {
        $query = "SELECT id, userName, email, role, avatar
                FROM " . $this->table_name . "
                WHERE googleId = ?
                LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $googleId);
        $stmt->execute();

        if($stmt->rowCount() > 0) {
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            $this->id = $row['id'];
            $this->userName = $row['userName'];
            $this->email = $row['email'];
            $this->role = $row['role'];
            $this->avatar = $row['avatar'];

            return true;
        }
        return false;
    }

    // Email ile kullanıcı kontrolü
    public function findByEmail($email) {
        $query = "SELECT id, userName, password, role, avatar
                FROM " . $this->table_name . "
                WHERE email = ?
                LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $email);
        $stmt->execute();

        if($stmt->rowCount() > 0) {
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            $this->id = $row['id'];
            $this->userName = $row['userName'];
            $this->password = $row['password'];
            $this->role = $row['role'];
            $this->avatar = $row['avatar'];

            return true;
        }
        return false;
    }
}
