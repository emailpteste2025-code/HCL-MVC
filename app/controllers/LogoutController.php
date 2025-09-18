<?php
class LogoutController {
    public function index() {
        require_once __DIR__ . '/../helpers.php';
        removeEnvKey("API_KEY");
        removeEnvKey("USERNAME");
        header("Location: /login");
        exit;
    }
}