<?php
class User {
    // Veritabanı bağlantısı ve tablo adı
    private $conn;
    private $table_name = "users";

    // Nesne özellikleri
    public $id;
    public $firstName;
    public $lastName;
    public $email;
    public $password;
    public $googleId;
    public $avatar;
    public $role;
    public $createdAt;
    public $updatedAt;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Kullanıcı oluştur
    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                SET
                    firstName = :firstName,
                    lastName = :lastName,
                    email = :email,
                    password = :password,
                    role = :role,
                    createdAt = :createdAt,
                    updatedAt = :updatedAt";

        $stmt = $this->conn->prepare($query);

        // Değerleri temizle
        $this->firstName = htmlspecialchars(strip_tags($this->firstName));
        $this->lastName = htmlspecialchars(strip_tags($this->lastName));
        $this->email = htmlspecialchars(strip_tags($this->email));
        
        // Şifre varsa hashle
        $password_hash = null;
        if ($this->password) {
            $this->password = htmlspecialchars(strip_tags($this->password));
            $password_hash = password_hash($this->password, PASSWORD_BCRYPT);
        }

        // Değerleri bağla
        $stmt->bindParam(":firstName", $this->firstName);
        $stmt->bindParam(":lastName", $this->lastName);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":password", $password_hash);
        $stmt->bindParam(":role", $this->role);
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
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $query = "UPDATE " . $this->table_name . "
                    SET
                        firstName = :firstName,
                        lastName = :lastName,
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
                        firstName = :firstName,
                        lastName = :lastName,
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
        $stmt->bindParam(":firstName", $googleData['given_name']);
        $stmt->bindParam(":lastName", $googleData['family_name']);
        $stmt->bindParam(":email", $googleData['email']);
        $stmt->bindParam(":avatar", $googleData['picture']);
        $stmt->bindParam(":updatedAt", date('Y-m-d H:i:s'));

        return $stmt->execute();
    }

    // Google ID ile kullanıcı getir
    public function getByGoogleId($googleId) {
        $query = "SELECT id, firstName, lastName, email, role, avatar
                FROM " . $this->table_name . "
                WHERE googleId = ?
                LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $googleId);
        $stmt->execute();

        if($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $this->id = $row['id'];
            $this->firstName = $row['firstName'];
            $this->lastName = $row['lastName'];
            $this->email = $row['email'];
            $this->role = $row['role'];
            $this->avatar = $row['avatar'];

            return true;
        }
        return false;
    }

    // Email ile kullanıcı kontrolü
    public function emailExists() {
        $query = "SELECT id, firstName, lastName, password, role, avatar
                FROM " . $this->table_name . "
                WHERE email = ?
                LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $this->email = htmlspecialchars(strip_tags($this->email));
        $stmt->bindParam(1, $this->email);
        $stmt->execute();

        if($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $this->id = $row['id'];
            $this->firstName = $row['firstName'];
            $this->lastName = $row['lastName'];
            $this->password = $row['password'];
            $this->role = $row['role'];
            $this->avatar = $row['avatar'];

            return true;
        }
        return false;
    }
}
