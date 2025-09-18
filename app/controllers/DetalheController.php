<?php
class DetalheController
{
    public function index()
    {
        require_once __DIR__ . '/../helpers.php';

        // Timeout da sessão (1h)
        $sessionTimeout = 60 * 60;
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $sessionTimeout) {
            clearEnv();
            session_unset();
            session_destroy();
            header("Location: /login?timeout=1");
            exit;
        }
        $_SESSION['last_activity'] = time();

        $env = loadEnv();
        $token = $env['API_KEY'] ?? null;
        if (!$token) {
            header("Location: /login");
            exit;
        }

        // Documento recebido via GET
        $doc = $_GET['doc'] ?? null;
        $docDecoded = $doc ? json_decode($doc, true) : null;

        // Passa variáveis para a view
        ob_start();
        require __DIR__ . '/../views/detalhe.php';
        $content = ob_get_clean();

        $title = "Detalhes do Documento";
        $bodyClass = "bg-light";

        require __DIR__ . '/../views/layout.php';
    }
}
