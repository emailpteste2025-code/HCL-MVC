<?php
$title = "Dashboard – Sistema";
ob_start();
?>

<div class="container">
  <h3>Bem-vindo, <?= htmlspecialchars($username) ?> </h3> <br>
  
  <!-- Formulário de busca -->
  <form method="get" action="/home" class="row mb-4">
    <div class="col-md-10">
      <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" 
             class="form-control" placeholder="Buscar por Proposição (123/2023), Autor ou Ano (2021)">202
    </div>
    <div class="col-md-2">
      <button type="submit" class="btn btn-primary w-100">🔍 Buscar</button>
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