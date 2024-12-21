<?php

namespace Middleware;

class ValidationMiddleware {
    private $rules;

    public function __construct($rules) {
        $this->rules = $rules;
    }

    public function handle($request, $next) {
        $data = json_decode(file_get_contents('php://input'), true) ?? [];
        $errors = [];

        foreach ($this->rules as $field => $rule) {
            if (!isset($data[$field]) && strpos($rule, 'required') !== false) {
                $errors[$field] = "$field alanı gereklidir";
                continue;
            }

            if (isset($data[$field])) {
                $value = $data[$field];

                // Kuralları kontrol et
                $ruleArray = explode('|', $rule);
                foreach ($ruleArray as $singleRule) {
                    $params = [];
                    if (strpos($singleRule, ':') !== false) {
                        list($singleRule, $param) = explode(':', $singleRule);
                        $params = explode(',', $param);
                    }

                    $error = $this->validateRule($field, $value, $singleRule, $params);
                    if ($error) {
                        $errors[$field] = $error;
                        break;
                    }
                }
            }
        }

        if (!empty($errors)) {
            http_response_code(422);
            echo json_encode(['errors' => $errors]);
            exit();
        }

        return $next($request);
    }

    private function validateRule($field, $value, $rule, $params = []) {
        switch ($rule) {
            case 'required':
                if (empty($value)) {
                    return "$field alanı gereklidir";
                }
                break;

            case 'email':
                if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    return "Geçerli bir e-posta adresi giriniz";
                }
                break;

            case 'min':
                if (strlen($value) < $params[0]) {
                    return "$field en az {$params[0]} karakter olmalıdır";
                }
                break;

            case 'max':
                if (strlen($value) > $params[0]) {
                    return "$field en fazla {$params[0]} karakter olmalıdır";
                }
                break;

            case 'numeric':
                if (!is_numeric($value)) {
                    return "$field sayısal bir değer olmalıdır";
                }
                break;

            case 'in':
                if (!in_array($value, $params)) {
                    return "$field geçerli bir değer olmalıdır";
                }
                break;

            case 'date':
                if (!strtotime($value)) {
                    return "$field geçerli bir tarih olmalıdır";
                }
                break;

            case 'xss':
                $cleaned = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
                if ($cleaned !== $value) {
                    return "$field güvenli olmayan karakterler içeriyor";
                }
                break;
        }

        return null;
    }
}
