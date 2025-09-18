<?php
require_once __DIR__ . '/../helpers.php';

class LoginController
{
    public function index()
    {
        // ⚠️ Removido session_start(), já iniciado no index.php

        $env = loadEnv();
        if (!empty($env['API_KEY'])) {
            header("Location: /home");
            exit;
        }

        $error = "";

        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $username = $_POST['username'] ?? "";
            $password = $_POST['password'] ?? "";

            $url = "http://10.100.2.64:8880/api/v1/auth";
            $payload = json_encode([
                "username" => $username,
                "password" => $password
            ]);

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode === 200) {
                $data = json_decode($response, true);
                if (!empty($data['bearer'])) {
                    // Salva token + usuário no .env
                    setEnvKey("API_KEY", $data['bearer']);
                    setEnvKey("USERNAME", $username);

                    // Atualiza timestamp da sessão
                    $_SESSION['last_activity'] = time();

                    header("Location: /home");
                    exit;
                } else {
                    $error = "Não foi possível obter o token.";
                }
            } else {
                $error = "Erro $httpCode: " . htmlspecialchars($response);
            }
        }

        // Renderiza a view
        ob_start();
        require __DIR__ . '/../Views/login.php';
        $content = ob_get_clean();

        $title = "Login - Sistema";
        $bodyClass = "login-page d-flex align-items-center";

        require __DIR__ . '/../Views/layout.php';
    }
}
