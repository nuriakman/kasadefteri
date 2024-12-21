<?php

namespace App\Controllers;

use App\Models\Transaction;
use App\Middleware\AuthMiddleware;

class TransactionController
{
  private $db;
  private $transaction;

  public function __construct($db)
  {
    $this->db = $db;
    $this->transaction = new Transaction($db);
  }

  public function create()
  {
    $userData = AuthMiddleware::validateToken();
    $data = json_decode(file_get_contents("php://input"));

    if (!$this->validateTransactionData($data)) {
      http_response_code(400);
      echo json_encode(['message' => 'Geçersiz işlem verileri']);
      return;
    }

    $this->transaction->userId = $userData->id;
    $this->transaction->amount = $data->amount;
    $this->transaction->type = $data->type;
    $this->transaction->description = $data->description;
    $this->transaction->categoryId = $data->categoryId;
    $this->transaction->registerType = $data->registerType;
    $this->transaction->transactionDate = $data->transactionDate;
    $this->transaction->isDayEnd = false;

    if ($this->transaction->create()) {
      http_response_code(201);
      echo json_encode(['message' => 'İşlem başarıyla kaydedildi']);
    } else {
      http_response_code(500);
      echo json_encode(['message' => 'İşlem kaydedilemedi']);
    }
  }

  public function getDailyTransactions()
  {
    AuthMiddleware::validateToken();

    $date = $_GET['date'] ?? date('Y-m-d');
    $transactions = $this->transaction->getDailyTransactions($date);

    http_response_code(200);
    echo json_encode([
      'date' => $date,
      'transactions' => $transactions
    ]);
  }

  public function markDayEnd()
  {
    $userData = AuthMiddleware::validateAdmin();
    $data = json_decode(file_get_contents("php://input"));

    if (!isset($data->date)) {
      http_response_code(400);
      echo json_encode(['message' => 'Tarih gerekli']);
      return;
    }

    if ($this->transaction->markDayEnd($data->date)) {
      http_response_code(200);
      echo json_encode(['message' => 'Gün sonu işlemi başarılı']);
    } else {
      http_response_code(500);
      echo json_encode(['message' => 'Gün sonu işlemi başarısız']);
    }
  }

  private function validateTransactionData($data)
  {
    return (
      isset($data->amount) &&
      isset($data->type) &&
      isset($data->description) &&
      isset($data->categoryId) &&
      isset($data->registerType) &&
      isset($data->transactionDate) &&
      in_array($data->type, ['income', 'expense']) &&
      $data->amount > 0
    );
  }
}
