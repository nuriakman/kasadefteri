<?php
namespace App\Controllers;

use App\Models\User;
use Firebase\JWT\JWT;
use Google_Client;
use Exception;

class AuthController {
    private $db;
    private $user;

    public function __construct($db) {
        $this->db = $db;
        $this->user = new User($db);
    }

    public function login() {
        $data = json_decode(file_get_contents("php://input"));
        
        if (!isset($data->email) || !isset($data->password)) {
            http_response_code(400);
            echo json_encode(['message' => 'Email ve şifre gerekli']);
            return;
        }

        if ($this->user->emailExists($data->email)) {
            if (password_verify($data->password, $this->user->password)) {
                $token = $this->generateToken($this->user);
                
                http_response_code(200);
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Giriş başarılı',
                    'user' => [
                        'id' => $this->user->id,
                        'userName' => $this->user->userName,
                        'email' => $this->user->email,
                        'role' => $this->user->role,
                        'avatar' => $this->user->avatar
                    ],
                    'token' => $token
                ]);
            } else {
                http_response_code(401);
                echo json_encode(['message' => 'Hatalı şifre']);
            }
        } else {
            http_response_code(404);
            echo json_encode(['message' => 'Kullanıcı bulunamadı']);
        }
    }

    public function googleLogin() {
        $data = json_decode(file_get_contents("php://input"));
        
        if (!isset($data->googleToken)) {
            http_response_code(400);
            echo json_encode(['message' => 'Google token gerekli']);
            return;
        }

        try {
            $client = new Google_Client(['client_id' => $_ENV['GOOGLE_CLIENT_ID']]);
            $payload = $client->verifyIdToken($data->googleToken);

            if ($payload) {
                $googleData = [
                    'id' => $payload['sub'],
                    'email' => $payload['email'],
                    'given_name' => $payload['given_name'],
                    'family_name' => $payload['family_name'],
                    'picture' => $payload['picture']
                ];

                if ($this->user->createOrUpdateWithGoogle($googleData)) {
                    $this->user->getByGoogleId($googleData['id']);
                    $token = $this->generateToken($this->user);

                    http_response_code(200);
                    echo json_encode([
                        'status' => 'success',
                        'message' => 'Giriş başarılı',
                        'user' => [
                            'id' => $this->user->id,
                            'userName' => $this->user->userName,
                            'email' => $this->user->email,
                            'role' => $this->user->role,
                            'avatar' => $this->user->avatar
                        ],
                        'token' => $token
                    ]);
                } else {
                    throw new Exception('Kullanıcı kaydedilemedi');
                }
            } else {
                throw new Exception('Geçersiz Google token');
            }
        } catch (Exception $e) {
            http_response_code(401);
            echo json_encode(['message' => $e->getMessage()]);
        }
    }

    private function generateToken($user) {
        $token = [
            'iss' => $_ENV['APP_URL'],
            'aud' => $_ENV['APP_URL'],
            'iat' => time(),
            'exp' => time() + (int)$_ENV['JWT_EXPIRE'],
            'data' => [
                'id' => $user->id,
                'email' => $user->email,
                'role' => $user->role
            ]
        ];

        return JWT::encode($token, $_ENV['JWT_SECRET'], 'HS256');
    }
}
