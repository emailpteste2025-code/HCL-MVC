<div class="card shadow-lg border-0 rounded-4">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="card-title mb-0">Dados brutos da proposição</h3>
            <button type="button" class="btn btn-secondary" id="btnPdf">
                <i class="bi bi-file-earmark-pdf"></i> Gerar PDF
            </button>
        </div>

        <?php if (isset($filteredDoc) && $filteredDoc): ?>
            <div class="alert alert-info">Exibindo documento filtrado pelo código da proposição.</div>
            <pre class="bg-dark text-light p-3 rounded" style="white-space: pre-wrap;">
<?= htmlspecialchars(json_encode($filteredDoc, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) ?>
            </pre>
        <?php elseif (isset($rawData) && $rawData): ?>
            <div class="alert alert-secondary">Exibindo resposta completa da API (sem filtro específico).</div>
            <pre class="bg-dark text-light p-3 rounded" style="white-space: pre-wrap;">
<?= htmlspecialchars(json_encode($rawData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) ?>
            </pre>
        <?php else: ?>
            <div class="alert alert-warning">Não foi possível obter dados. Verifique sua autenticação e tente novamente.</div>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const btn = document.getElementById('btnPdf');
    if (btn) {
        btn.addEventListener('click', function () {
            window.print();
        });
    }
});
</script>

<style>
@media print {
    .navbar, #btnPdf, .btn, .alert { display: none !important; }
    body { background: #fff !important; }
    pre {
        background: #fff !important;
        color: #000 !important;
        border: 1px solid #ccc;
        padding: 12px;
    }
}
</style>