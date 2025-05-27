<?php
// index.php
// In questo esempio PHP serve principalmente come contenitore per la pagina.
?>
<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>DBpedia Search App - Ricerca SPARQL</title>
  <!-- Bootstrap 5 CSS per uno stile moderno e responsive -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <style>
    /* Impostazioni di base per Day e Night theme */
    body.day {
      background-color: #f8f9fa;
      color: #212529;
    }
    body.night {
      background-color: #212529;
      color: #f8f9fa;
    }
    /* Header e Footer */
    header, footer {
      background-color: #333;
      color: #fff;
      padding: 15px 10px;
      text-align: center;
    }
    footer {
      font-size: 0.9rem;
    }
    /* Contenitore principale */
    .container-main {
      max-width: 960px;
      margin: 20px auto;
    }
    /* Form di ricerca */
    #searchForm {
      margin-bottom: 30px;
    }
    /* Risultati (card-style) */
    .result-item {
      margin-bottom: 20px;
      padding: 20px;
      background-color: #fff;
      border: 1px solid #dee2e6;
      border-radius: 0.25rem;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    .result-item h3 {
      margin-bottom: 15px;
    }
    .result-item p {
      font-size: 0.95rem;
    }
    .result-item img {
      max-width: 100%;
      height: auto;
      margin-bottom: 15px;
      border-radius: 0.25rem;
    }
    /* Spinner per il caricamento dei risultati */
    .spinner {
      margin: 50px auto;
      display: block;
    }
    /* Stile della modale per "Leggi di più" */
    .modal-content {
      background-color: inherit;
      color: inherit;
    }
  </style>
</head>
<body class="day">
  <!-- Header -->
  <header>
    <div class="d-flex justify-content-between align-items-center">
      <h1 class="m-0">DBpedia Search App</h1>
      <!-- Toggle tema: Day / Night -->
      <div>
        <label class="form-check-label me-2">Tema</label>
        <input type="checkbox" id="themeToggle" class="form-check-input">
        <span id="themeText">Day</span>
      </div>
    </div>
  </header>
  
  <!-- Contenitore principale -->
  <div class="container-main">
    <!-- Form di Ricerca -->
    <form id="searchForm" class="mb-4">
      <div class="row g-3 align-items-center">
        <div class="col-md-5">
          <input type="text" id="searchQuery" class="form-control" placeholder="Inserisci la parola chiave..." required>
        </div>
        <div class="col-md-2">
          <select id="languageSelect" class="form-select">
            <option value="en" selected>Inglese</option>
            <option value="it">Italiano</option>
            <option value="de">Tedesco</option>
            <option value="fr">Francese</option>
            <option value="es">Spagnolo</option>
          </select>
        </div>
        <div class="col-md-3">
          <div class="form-check form-check-inline">
            <input class="form-check-input" type="checkbox" id="showImages" value="" checked>
            <label class="form-check-label" for="showImages">Mostra Immagini</label>
          </div>
          <div class="form-check form-check-inline">
            <input class="form-check-input" type="checkbox" id="showText" value="" checked>
            <label class="form-check-label" for="showText">Mostra Testo</label>
          </div>
        </div>
        <div class="col-md-2">
          <button type="submit" class="btn btn-primary w-100">Cerca</button>
        </div>
      </div>
    </form>
    <!-- Messaggio di errore -->
    <p id="errorMessage" class="text-center text-danger"></p>
    <!-- Container per i risultati -->
    <div id="resultContainer"></div>
  </div>
  
  <!-- Footer -->
  <footer>
    <p>Realizzato da Bocaletto Luca - Ispirato a DBpedia &copy; <?php echo date("Y"); ?></p>
  </footer>
  
  <!-- Modale per "Leggi di più" -->
  <div class="modal fade" id="detailModal" tabindex="-1" aria-labelledby="detailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="detailModalLabel"></h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button>
        </div>
        <div class="modal-body" id="modalBodyContent">
          <!-- Il testo completo verrà inserito qui -->
        </div>
      </div>
    </div>
  </div>
  
  <!-- Bootstrap Bundle JS (include Popper) -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  
  <script>
    // Variabili globali
    const resultContainer = document.getElementById('resultContainer');
    const errorMessage = document.getElementById('errorMessage');

    // Gestione del tema (Day / Night)
    const themeToggle = document.getElementById('themeToggle');
    const themeText = document.getElementById('themeText');
    themeToggle.addEventListener('change', function(){
      if (this.checked) {
        document.body.classList.remove('day');
        document.body.classList.add('night');
        themeText.textContent = "Night";
      } else {
        document.body.classList.remove('night');
        document.body.classList.add('day');
        themeText.textContent = "Day";
      }
    });
    
    // Gestione del form di ricerca
    document.getElementById('searchForm').addEventListener('submit', function(e){
      e.preventDefault();
      resultContainer.innerHTML = "";
      errorMessage.textContent = "";
      
      const query = document.getElementById('searchQuery').value.trim();
      const lang = document.getElementById('languageSelect').value;
      const showImg = document.getElementById('showImages').checked;
      const showTxt = document.getElementById('showText').checked;
      
      if(query === "") return;
      
      // Esegui la ricerca
      searchDBpedia(query, lang, showImg, showTxt);
    });
    
    // Funzione per eseguire la ricerca su DBpedia (usando SPARQL)
    async function searchDBpedia(keyword, lang, showImg, showTxt) {
      // Mostra spinner
      resultContainer.innerHTML = '<div class="text-center"><div class="spinner-border spinner" role="status"><span class="visually-hidden">Loading...</span></div></div>';
      
      // Costruzione della query SPARQL (filtra per abstract e label nella lingua scelta, e contiene la parola chiave)
      const sparqlQuery = `
PREFIX dbo: <http://dbpedia.org/ontology/>
PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
PREFIX foaf: <http://xmlns.com/foaf/0.1/>

SELECT DISTINCT ?s ?label ?abstract ?depiction WHERE {
  ?s dbo:abstract ?abstract.
  ?s rdfs:label ?label.
  OPTIONAL { ?s foaf:depiction ?depiction. }
  FILTER(lang(?abstract) = "${lang}" && lang(?label) = "${lang}").
  FILTER(CONTAINS(LCASE(?label), "${keyword.toLowerCase()}"))
} LIMIT 20
      `;
      
      // NB: l'endpoint SPARQL di DBpedia potrebbe avere problemi CORS, quindi potresti dover usare un proxy.
      // const endpointUrl = "https://cors-anywhere.herokuapp.com/https://dbpedia.org/sparql?query=" + encodeURIComponent(sparqlQuery) + "&format=json";
      const endpointUrl = "https://dbpedia.org/sparql?query=" + encodeURIComponent(sparqlQuery) + "&format=json";
      
      try {
        const response = await fetch(endpointUrl);
        if (!response.ok) {
          throw new Error("Errore nella risposta di rete");
        }
        const data = await response.json();
        displayResults(data, showImg, showTxt);
      } catch (err) {
        console.error(err);
        resultContainer.innerHTML = "";
        errorMessage.textContent = "Si è verificato un errore durante la ricerca.";
      }
    }
    
    // Funzione per visualizzare i risultati della ricerca
    function displayResults(data, showImg, showTxt) {
      resultContainer.innerHTML = "";
      if (!data.results || data.results.bindings.length === 0) {
        resultContainer.innerHTML = "<p class='text-center'>Nessun risultato trovato.</p>";
        return;
      }
      
      data.results.bindings.forEach(item => {
        const label = item.label ? item.label.value : "Sconosciuto";
        const abstractFull = item.abstract ? item.abstract.value : "";
        let abstractTruncated = abstractFull;
        if(abstractTruncated.length > 300){
          abstractTruncated = abstractTruncated.substring(0, 300) + "...";
        }
        const depiction = item.depiction ? item.depiction.value : "";
        // Costruisci il contenuto in base alle preferenze
        let content = "";
        if(showImg && depiction) {
          content += `<img src="${depiction}" alt="${label}" class="img-fluid mb-3">`;
        }
        if(showTxt) {
          content += `<p>${abstractTruncated}</p>`;
        }
        content += `<button class="btn btn-sm btn-secondary" onclick="openDetailModal('${escapeHtml(label)}', '${escapeHtml(abstractFull)}', '${depiction}', ${showImg}, ${showTxt})">Leggi di più</button>`;
        
        // Costruisci la card per il risultato
        const card = document.createElement('div');
        card.className = "result-item";
        card.innerHTML = `<h3>${label}</h3>` + content;
        resultContainer.appendChild(card);
      });
    }
    
    // Funzione per aprire la modale con i dettagli completi dell'articolo
    function openDetailModal(title, fullText, depiction, showImg, showTxt) {
      const modalTitle = document.getElementById('detailModalLabel');
      const modalBody = document.getElementById('modalBodyContent');
      
      modalTitle.textContent = title;
      let innerHtml = "";
      if(showImg && depiction) {
        innerHtml += `<img src="${depiction}" alt="${title}" class="img-fluid mb-3">`;
      }
      if(showTxt) {
        innerHtml += `<p>${fullText}</p>`;
      }
      modalBody.innerHTML = innerHtml;
      
      // Mostra la modale usando Bootstrap
      const detailModal = new bootstrap.Modal(document.getElementById('detailModal'));
      detailModal.show();
    }
    
    // Funzione per evitare attacchi XSS (escapes HTML)
    function escapeHtml(text) {
      var map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
      };
      return text.replace(/[&<>"']/g, function(m) { return map[m]; });
    }
  </script>
</body>
</html>
