<?php
$title = "Dashboard – Sistema";
ob_start();
?>
<!-- nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
  <div class="container">
    <a class="navbar-brand" href="#">📊 Dashboard</a>
    <div class="d-flex"><a href="/logout" class="btn btn-outline-light">Sair</a></div>
  </div>
</nav -->

<div class="container">
  <h3>Bem-vindo, <?= htmlspecialchars($username) ?> </h3> <br>

  <!-- Filtro de busca -->
  <form method="get" class="row g-2 mb-4">
    <div class="col-md-8">
      <input type="text" name="search" class="form-control"
             placeholder="🔍 Buscar por proposição, ementa ou autor(a)"
             value="<?= htmlspecialchars($search) ?>"/>
      <input type="hidden" name="page" value="1"/>
    </div>
    <div class="col-md-2">
      <select name="mode" class="form-select">
        <option value="AND" <?= $mode === 'AND' ? 'selected' : '' ?>>Todos os termos (AND)</option>
        <option value="OR" <?= $mode === 'OR' ? 'selected' : '' ?>>Qualquer termo (OR)</option>
      </select>
    </div>
    <div class="col-md-2 d-grid">
      <button type="submit" class="btn btn-outline-secondary">Buscar</button>
    </div>
  </form>

  <?php if ($erro): ?>
    <div class="alert alert-warning"><?= $erro ?></div>
  <?php elseif (empty($dados)): ?>
    <div class="alert alert-info">Nenhum resultado encontrado.</div>
  <?php else: ?>
    <table class="table table-bordered table-striped">
      <thead class="table-light">
        <tr>
          <th style="width: 10%;">Proposição</th>
          <th style="width: 55%;">Ementa</th>
          <th style="width: 15%;">Data Publ.</th>
          <th style="width: 20%;">Autor(es)</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($dados as $item): 
          $prop  = htmlspecialchars($item["prop_num"] ?? "N/A");
          $ementa = htmlspecialchars($item["prop_ementa"] ?? "—");
          $dataPublRaw = $item["prop_data_publ"] ?? null;
          $dataPubl = $dataPublRaw ? date("d/m/Y", strtotime($dataPublRaw)) : "—";
          $autorRaw = $item["prop_autordoc_1"] ?? "—";
          $autorLimpo = preg_replace(['/^CN=/', '/\/O=CMRJ$/'], '', $autorRaw);
          $autor = htmlspecialchars($autorLimpo);
          $doc = urlencode(json_encode($item));
        ?>
        <tr>
          <td><a href="/detalhe?doc=<?= $doc ?>" target="_blank" class="prop-link"><?= $prop ?></a></td>
          <td><?= $ementa ?></td>
          <td><?= $dataPubl ?></td>
          <td><?= $autor ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <!-- Paginação -->
    <div class="d-flex justify-content-center mt-4">
      <nav>
        <ul class="pagination">

          <!-- Botão "<< Primeira" -->
          <?php if ($page > 1): ?>
            <li class="page-item">
              <a class="page-link" href="?page=1&search=<?= urlencode($search) ?>&mode=<?= $mode ?>">&laquo;</a>
            </li>

            <!-- Botão "< Anterior" -->
            <li class="page-item">
              <a class="page-link" href="?page=<?= $page-1 ?>&search=<?= urlencode($search) ?>&mode=<?= $mode ?>">&lt;</a>
            </li>
          <?php endif; ?>

          <!-- Página atual -->
          <li class="page-item active">
            <span class="page-link"><?= $page ?></span>
          </li>

          <!-- Botão "Próxima >" -->
          <?php if ($page < $lastPage): ?>
            <li class="page-item">
              <a class="page-link" href="?page=<?= $page+1 ?>&search=<?= urlencode($search) ?>&mode=<?= $mode ?>">&gt;</a>
            </li>

            <!-- Botão "Última >>" -->
            <li class="page-item">
              <a class="page-link" href="?page=<?= $lastPage ?>&search=<?= urlencode($search) ?>&mode=<?= $mode ?>">&raquo;</a>
            </li>
          <?php endif; ?>

        </ul>
      </nav>
    </div>


  <?php endif; ?>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';