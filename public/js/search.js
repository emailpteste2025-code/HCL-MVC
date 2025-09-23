/**
 * Script para gerenciar a funcionalidade de busca
 */
document.addEventListener('DOMContentLoaded', function() {
  // Referência ao formulário e campo de busca
  const searchForm = document.getElementById('searchForm');
  const searchInput = document.getElementById('searchInput');

  // Função para realizar a busca
  function realizarBusca(event) {
    event.preventDefault();
    
    // Obter o valor da busca
    const searchTerm = searchInput.value.trim();
    
    // Obter o modo atual (AND/OR) da URL ou usar AND como padrão
    const urlParams = new URLSearchParams(window.location.search);
    const mode = urlParams.get('mode') || 'AND';
    
    // Redirecionar para a URL com o parâmetro de busca e modo
    window.location.href = `/home?search=${encodeURIComponent(searchTerm)}&mode=${mode}`;
  }

  // Adicionar evento de submit ao formulário
  if (searchForm) {
    searchForm.addEventListener('submit', realizarBusca);
  }

  // Adicionar evento de tecla Enter no campo de busca
  if (searchInput) {
    searchInput.addEventListener('keypress', function(e) {
      if (e.key === 'Enter') {
        realizarBusca(e);
      }
    });
  }
});