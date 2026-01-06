<?php
class DadosBrutosController
{
    public function index()
    {
        require_once __DIR__ . '/../helpers.php';

        // Timeout (1h)
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

        // Documento recebido via GET (JSON urlencoded)
        $docParam = $_GET['doc'] ?? null;
        $docDecoded = $docParam ? json_decode($docParam, true) : null;
        $propNum = is_array($docDecoded) ? ($docDecoded['prop_num'] ?? null) : null;

        // Monta URL da API vpropexportapi (filtrando por prop_num, se disponível)
        $baseUrl = "http://10.100.2.64:8880/api/v1/lists/vpropexportapi?dataSource=scopeprojlei&richTextAs=markdown&documents=true&attachmentnames=true";
        $apiUrl = $propNum ? $baseUrl . "&search=" . urlencode($propNum) : $baseUrl;

        // Chama a API com Bearer
        $ch = curl_init($apiUrl);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $token"]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $apiResponse = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $rawData = null;
        $filteredDoc = null;
        if ($httpCode === 200 && $apiResponse) {
            $rawData = json_decode($apiResponse, true);

            // Tenta filtrar pelo prop_num, se vier uma lista
            if ($propNum && is_array($rawData)) {
                $docs = [];
                if (isset($rawData['documents']) && is_array($rawData['documents'])) {
                    $docs = $rawData['documents'];
                } elseif (isset($rawData['items']) && is_array($rawData['items'])) {
                    $docs = $rawData['items'];
                } elseif (isset($rawData['entries']) && is_array($rawData['entries'])) {
                    $docs = $rawData['entries'];
                }

                foreach ($docs as $d) {
                    if (isset($d['prop_num']) && $d['prop_num'] === $propNum) {
                        $filteredDoc = $d;
                        break;
                    }
                }

                // Se não encontrou exatamente, apenas pega o primeiro
                if (!$filteredDoc && $docs) {
                    $filteredDoc = $docs[0];
                }
            }
        }

        // Prepara conteúdo para a view
        ob_start();
        require __DIR__ . '/../views/dados_brutos.php';
        $content = ob_get_clean();

        $title = "Dados Brutos da Proposição";
        $bodyClass = "bg-light";

        // Navbar
        $navbar = <<<HTML
        <nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
            <div class="container">
                <a class="navbar-brand" href="/home">📊 Dashboard</a>
                <div class="d-flex"><a href="/logout" class="btn btn-outline-light">Sair</a></div>
            </div>
        </nav>
        HTML;

        require __DIR__ . '/../views/layout.php';
    }
}