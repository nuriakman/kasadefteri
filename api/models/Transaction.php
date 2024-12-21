<?php
class Transaction {
    // Veritabanı bağlantısı ve tablo adı
    private $conn;
    private $table_name = "transactions";

    // Nesne özellikleri
    public $id;
    public $userId;
    public $amount;
    public $type; // income/expense
    public $description;
    public $categoryId;
    public $registerType;
    public $transactionDate;
    public $createdAt;
    public $updatedAt;
    public $isDayEnd;

    public function __construct($db) {
        $this->conn = $db;
    }

    // İşlem oluştur
    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                SET
                    userId = :userId,
                    amount = :amount,
                    type = :type,
                    description = :description,
                    categoryId = :categoryId,
                    registerType = :registerType,
                    transactionDate = :transactionDate,
                    createdAt = :createdAt,
                    updatedAt = :updatedAt,
                    isDayEnd = :isDayEnd";

        $stmt = $this->conn->prepare($query);

        // Değerleri temizle ve bağla
        $stmt->bindParam(":userId", $this->userId);
        $stmt->bindParam(":amount", $this->amount);
        $stmt->bindParam(":type", $this->type);
        $stmt->bindParam(":description", htmlspecialchars(strip_tags($this->description)));
        $stmt->bindParam(":categoryId", $this->categoryId);
        $stmt->bindParam(":registerType", $this->registerType);
        $stmt->bindParam(":transactionDate", $this->transactionDate);
        $stmt->bindParam(":createdAt", date('Y-m-d H:i:s'));
        $stmt->bindParam(":updatedAt", date('Y-m-d H:i:s'));
        $stmt->bindParam(":isDayEnd", $this->isDayEnd);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Günlük işlemleri getir
    public function getDailyTransactions($date) {
        $query = "SELECT 
                    t.*, 
                    c.name as categoryName,
                    r.name as registerName
                FROM 
                    " . $this->table_name . " t
                    LEFT JOIN categories c ON t.categoryId = c.id
                    LEFT JOIN register_types r ON t.registerType = r.id
                WHERE 
                    DATE(t.transactionDate) = :date
                ORDER BY 
                    t.transactionDate DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":date", $date);
        $stmt->execute();

        return $stmt;
    }

    // Gün sonu işaretleme
    public function markDayEnd($date) {
        $query = "UPDATE " . $this->table_name . "
                SET isDayEnd = true
                WHERE DATE(transactionDate) = :date";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":date", $date);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }
}
