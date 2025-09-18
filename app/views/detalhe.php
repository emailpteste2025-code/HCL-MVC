<?php
/** Polyfill array_is_list para PHP < 8.1 */
if (!function_exists('array_is_list')) {
    function array_is_list($array) {
        if (!is_array($array)) return false;
        $i = 0;
        foreach (array_keys($array) as $k) {
            if ($k !== $i++) return false;
        }
        return true;
    }
}

/**
 * Converte texto em HTML seguro
 */
function renderTextWithLegisLinks(string $text): string {
    if ($text === '') return '';
    $allowedTags = '<br><b><i><u><strong><em>';
    $pattern = '/(\[[^\]]+\]\([^)]+\))/';
    $parts = preg_split($pattern, $text, -1, PREG_SPLIT_DELIM_CAPTURE);
    $out = '';
    foreach ($parts as $part) {
        if (preg_match('/^\[([^\]]+)\]\(([^)]+)\)$/', $part, $m)) {
            $label = $m[1];
            $url = $m[2];
            if (preg_match('/\.nsf\/([^\/\?\)]+)\/([^\/\?\)]+)/i', $url, $mm)) {
                $href = 'https://aplicnt.camara.rj.gov.br/apl/Legislativos/contlei.nsf/' .
                        rawurlencode($mm[1]) . '/' . rawurlencode($mm[2]) . '?OpenDocument';
            } else {
                $href = $url;
            }
            $out .= '<a href="' . htmlspecialchars($href) . '" target="_blank" rel="noopener noreferrer">'
                 . htmlspecialchars($label) . '</a>';
        } else {
            $out .= nl2br(strip_tags($part, $allowedTags));
        }
    }
    return $out;
}

function mostrarCampo($array, $chave, $label) {
    $valor = $array[$chave] ?? null;

    if ($chave === 'prop_texto_original') {
        if (is_array($valor) && isset($valor['content'])) {
            $valor = $valor['content'];
        } elseif (is_array($valor) && array_is_list($valor)) {
            $conteudos = [];
            foreach ($valor as $item) {
                if (is_array($item) && isset($item['content'])) {
                    $conteudos[] = $item['content'];
                }
            }
            if ($conteudos) $valor = implode("\n\n", $conteudos);
        }
    }

    if ($chave === 'attachments' && is_array($valor)) {
        $pdfs = array_filter($valor, fn($v) => is_string($v) && preg_match('/\.pdf$/i', $v));
        $valor = $pdfs ? reset($pdfs) : null;
    }

    if (is_array($valor) && isset($valor['content'])) {
        $valor = $valor['content'];
    }

    if (is_array($valor) && array_is_list($valor)) {
        $conteudos = [];
        foreach ($valor as $item) {
            if (is_array($item) && isset($item['content'])) {
                $conteudos[] = $item['content'];
            }
        }
        if ($conteudos) $valor = implode("\n\n", $conteudos);
    }

    if (is_array($valor)) {
        $valor = implode(", ", $valor);
    }

    if ($valor !== null && $valor !== '') {
        $html = renderTextWithLegisLinks((string)$valor);
        $labelHtml = $label !== '' ? "<strong>{$label}</strong><br>" : "";
        return "<p>{$labelHtml}{$html}</p>";
    }

    return "";
}

function coletarNomesPdf(array $doc): array {
    $candidatos = [];
    $possiveis = [
        $doc['attachments'] ?? null,
        $doc['attachmentnames'] ?? null,
        $doc['@attachments'] ?? null,
        $doc['@attachmentnames'] ?? null,
        $doc['@meta']['attachments'] ?? null,
        $doc['@meta']['attachmentnames'] ?? null,
    ];
    foreach ($possiveis as $arr) {
        if (is_array($arr)) {
            foreach ($arr as $nome) {
                if (is_string($nome) && preg_match('/\.pdf$/i', $nome)) {
                    $candidatos[] = trim($nome);
                }
            }
        }
    }

    // Fallback: procurar no prop_texto_original
    $conteudo = null;
    if (isset($doc['prop_texto_original'])) {
        $pto = $doc['prop_texto_original'];
        if (is_array($pto) && isset($pto['content']) && is_string($pto['content'])) {
            $conteudo = $pto['content'];
        } elseif (is_array($pto) && array_is_list($pto)) {
            $parts = [];
            foreach ($pto as $p) {
                if (is_array($p) && isset($p['content']) && is_string($p['content'])) {
                    $parts[] = $p['content'];
                }
            }
            if ($parts) $conteudo = implode("\n", $parts);
        }
    }

    if (is_string($conteudo) && $conteudo !== '') {
        if (preg_match_all('/([A-Za-z0-9_\-\.\s]+\.pdf)/i', $conteudo, $m)) {
            foreach ($m[1] as $fname) {
                $candidatos[] = trim($fname);
            }
        }
    }

    return array_values(array_unique($candidatos));
}
?>

<div class="card shadow-lg border-0 rounded-4">
    <div class="card-body">
        <h3 class="card-title mb-4">Detalhes do Documento</h3>

        <?php if ($docDecoded): ?>
            <div class="mb-4">
                <?= mostrarCampo($docDecoded, 'prop_num', 'PROJETO DE LEI Nº') ?>
                <?= mostrarCampo($docDecoded, 'prop_ementa', 'EMENTA:') ?>
                <?= mostrarCampo($docDecoded, 'prop_autor', 'Autor(es):') ?>
                <?= mostrarCampo($docDecoded, 'Cmp_Resolve', 'A CÂMARA MUNICIPAL DO RIO DE JANEIRO') ?>
                <?= mostrarCampo($docDecoded['prop_texto'] ?? [], 'content', '') ?>
                <?= mostrarCampo($docDecoded['prop_justificativa'] ?? [], 'content', 'JUSTIFICATIVA') ?>

                <?php
                $unid = $docDecoded['@meta']['unid'] ?? null;
                $pdfs = coletarNomesPdf($docDecoded);

                if ($unid && $pdfs): ?>
                    <p>
                        <?php foreach ($pdfs as $pdf): 
                            $url = "http://cmrjdes01/APL/Legislativos/scproteste.nsf/0/" .
                                    rawurlencode($unid) . "/\$FILE/" . rawurlencode($pdf);
                        ?>
                            <a href="<?= htmlspecialchars($url) ?>" target="_blank" rel="noopener noreferrer">
                                <img src="../img/pdf-Icon.png" alt="<?= htmlspecialchars($pdf) ?>" style="height:24px; vertical-align:middle; margin-right:6px;">
                                <?= htmlspecialchars($pdf) ?>
                            </a><br>
                        <?php endforeach; ?>
                    </p>
                <?php endif; ?>

                <?= mostrarCampo($docDecoded['Legcita'] ?? [], 'content', 'Legislação Citada') ?>
            </div>
        <?php else: ?>
            <div class="alert alert-warning">Nenhum dado disponível.</div>
        <?php endif; ?>
    </div>
</div>
