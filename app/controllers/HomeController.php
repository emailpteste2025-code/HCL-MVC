<?php
class HomeController {
    public function index() {
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
        $username = $env['USERNAME'] ?? "Usuário";

        if (!$token) {
            header("Location: /login");
            exit;
        }

        // Paginação e busca
        $page   = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
        $search = $_GET['search'] ?? '';
        $mode   = ($_GET['mode'] ?? 'AND') === 'OR' ? 'OR' : 'AND';
        $count  = 5;
        $start  = ($page - 1) * $count;

        $url = "http://10.100.2.64:8880/api/v1/lists/vlei?dataSource=scopeprojlei&richTextAs=markdown&count=$count&start=$start&documents=true&attachmentnames=true";

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $token"]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $dados = [];
        $total = null;
        $erro  = null;

        if ($httpCode === 200) {
            $json = json_decode($response, true);
            if (is_array($json)) {
                $total = $json["@total"] ?? null;
                if (isset($json["documents"])) {
                    $dados = $json["documents"];
                } elseif (isset($json["items"])) {
                    $dados = $json["items"];
                } elseif (isset($json["entries"])) {
                    $dados = $json["entries"];
                } elseif (array_keys($json) === range(0, count($json) - 1)) {
                    $dados = $json;
                }

                // 🔍 Filtro local multi-termos (AND/OR)
                if ($search !== '') {
                    $terms = preg_split('/[\s,]+/', mb_strtolower($search), -1, PREG_SPLIT_NO_EMPTY);
                    $dados = array_filter($dados, function($item) use ($terms, $mode) {
                        $pn = mb_strtolower($item["prop_num"] ?? '');
                        $em = mb_strtolower($item["prop_ementa"] ?? '');
                        $au = mb_strtolower($item["prop_autordoc_1"] ?? '');
                        foreach ($terms as $term) {
                            $match = str_contains($pn, $term) || str_contains($em, $term) || str_contains($au, $term);
                            if ($match && $mode === 'OR') return true;
                            if (!$match && $mode === 'AND') return false;
                        }
                        return $mode === 'AND';
                    });
                }
            } else {
                $erro = "Erro ao decodificar JSON.";
            }
        } else {
            $erro = "Erro $httpCode ao buscar dados.";
        }

        // Calcula última página com base no total retornado pela API
        if ($total !== null && $total > 0) {
            $lastPage = max(1, ceil($total / $count));
        } else {
            // se API não retorna total, assume que pode ter próxima página (otimista)
            $lastPage = ($dados && count($dados) == $count) ? $page + 1 : $page;
        }
                

        // passa variáveis pra view
        ob_start();
        require __DIR__ . '/../Views/home.php';
        $content = ob_get_clean();

        $title = "Dashboard – Sistema";
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

        require __DIR__ . '/../Views/layout.php';
    }
}
 