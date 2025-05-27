<?php
// index.php
?>
<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <title>DBpedia Search App - Ricerca SPARQL - By Bocaletto Luca</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  
  <!-- Includo Bootstrap 5 per uno stile moderno e responsive -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  
  <style>
    /* Temi Day e Night */
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
      padding: 15px;
      text-align: center;
    }
    
    /* Contenitore principale */
    .container-main {
      max-width: 960px;
      margin: 20px auto;
      padding: 0 15px;
    }
    
    /* Form di ricerca */
    #searchForm {
      margin-bottom: 30px;
    }
    
    /* Card dei risultati */
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
    
    /* Spinner */
    .spinner {
      margin: 50px auto;
      display: block;
    }
    
    /* Messaggio di errore */
    #errorMessage {
      color: #d9534f;
      font-weight: bold;
      text-align: center;
    }
    
    /* Footer */
    footer {
      margin-top: 40px;
      font-size: 0.9rem;
    }
    
    /* Modal: il classico z-index alto è garantito da Bootstrap */
  </style>
</head>
<body class="day">
  <!-- Header -->
  <header>
    <div class="d-flex justify-content-between align-items-center">
      <h1 class="m-0">DBpedia Search App</h1>
      <!-- Toggle per Tema Day/Night -->
      <div>
        <label for="themeToggle" class="form-check-label me-2">Tema</label>
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
        <!-- Input parola chiave -->
        <div class="col-md-5">
          <input type="text" id="searchQuery" class="form-control" placeholder="Inserisci la parola chiave..." required>
        </div>
        <!-- Selezione lingua -->
        <div class="col-md-2">
          <select id="languageSelect" class="form-select">
            <option value="en" selected>Inglese</option>
            <option value="it">Italiano</option>
            <option value="de">Tedesco</option>
            <option value="fr">Francese</option>
            <option value="es">Spagnolo</option>
          </select>
        </div>
        <!-- Opzioni visualizzazione -->
        <div class="col-md-3">
          <div class="form-check">
            <input class="form-check-input" type="checkbox" id="showImages" checked>
            <label class="form-check-label" for="showImages">Mostra Immagini</label>
          </div>
          <div class="form-check">
            <input class="form-check-input" type="checkbox" id="showText" checked>
            <label class="form-check-label" for="showText">Mostra Testo</label>
          </div>
          <div class="form-check">
            <input class="form-check-input" type="checkbox" id="showCategories">
            <label class="form-check-label" for="showCategories">Mostra Categorie</label>
          </div>
        </div>
        <!-- Bottone di ricerca -->
        <div class="col-md-2">
          <button type="submit" class="btn btn-primary w-100">Cerca</button>
        </div>
      </div>
    </form>
    
    <!-- Messaggio di errore -->
    <p id="errorMessage"></p>
    
    <!-- Container per i risultati -->
    <div id="resultContainer"></div>
  </div>
  
  <!-- Footer -->
  <footer>
    <p>&copy; <?php echo date("Y"); ?> DBpedia Search App - Realizzato da Bocaletto Luca</p>
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
          <!-- Qui verrà mostrato il contenuto completo -->
        </div>
      </div>
    </div>
  </div>
  
  <!-- Bootstrap Bundle JS (include Popper) -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  
  <script>
    // Gestione del tema Day/Night
    const themeToggle = document.getElementById("themeToggle");
    const themeText = document.getElementById("themeText");
    themeToggle.addEventListener("change", () => {
      if (themeToggle.checked) {
        document.body.classList.remove("day");
        document.body.classList.add("night");
        themeText.textContent = "Night";
      } else {
        document.body.classList.remove("night");
        document.body.classList.add("day");
        themeText.textContent = "Day";
      }
    });
    
    // Gestione del form di ricerca e variabili globali
    const searchForm = document.getElementById("searchForm");
    const resultContainer = document.getElementById("resultContainer");
    const errorMessage = document.getElementById("errorMessage");
    
    searchForm.addEventListener("submit", function(e) {
      e.preventDefault();
      resultContainer.innerHTML = "";
      errorMessage.textContent = "";
      const query = document.getElementById("searchQuery").value.trim();
      const lang = document.getElementById("languageSelect").value;
      const showImages = document.getElementById("showImages").checked;
      const showText = document.getElementById("showText").checked;
      const showCategories = document.getElementById("showCategories").checked;
      
      if(query === "") return;
      
      performSearch(query, lang, showImages, showText, showCategories);
    });
    
    // Funzione per eseguire la ricerca su DBpedia tramite SPARQL
    async function performSearch(keyword, lang, showImages, showText, showCategories) {
      resultContainer.innerHTML = '<div class="text-center"><div class="spinner-border spinner" role="status"><span class="visually-hidden">Caricamento...</span></div></div>';
      const sparqlQuery = `
PREFIX dbo: <http://dbpedia.org/ontology/>
PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
PREFIX foaf: <http://xmlns.com/foaf/0.1/>
PREFIX dct: <http://purl.org/dc/terms/>

SELECT DISTINCT ?s ?label ?abstract ?depiction ?category WHERE {
  ?s dbo:abstract ?abstract.
  ?s rdfs:label ?label.
  OPTIONAL { ?s foaf:depiction ?depiction. }
  OPTIONAL { ?s dct:subject ?category. }
  FILTER(lang(?abstract) = "${lang}" && lang(?label) = "${lang}").
  FILTER(CONTAINS(LCASE(?label), "${keyword.toLowerCase()}"))
} LIMIT 20
      `;
      
      // Se ci fossero problemi di CORS, puoi anteporre un proxy (ad esempio, la URL cors-anywhere)
      const endpointUrl = "https://dbpedia.org/sparql?query=" + encodeURIComponent(sparqlQuery) + "&format=json";
      
      try {
        const response = await fetch(endpointUrl);
        if (!response.ok) throw new Error("Errore nella risposta di rete");
        const data = await response.json();
        displayResults(data, showImages, showText, showCategories);
      } catch (error) {
        console.error(error);
        resultContainer.innerHTML = "";
        errorMessage.textContent = "Si è verificato un errore durante la ricerca.";
      }
    }
    
    // Funzione per visualizzare i risultati
    function displayResults(data, showImages, showText, showCategories) {
      resultContainer.innerHTML = "";
      if(!data.results || !data.results.bindings || data.results.bindings.length === 0) {
        resultContainer.innerHTML = "<p class='text-center'>Nessun risultato trovato.</p>";
        return;
      }
      
      data.results.bindings.forEach(item => {
        const label = item.label ? item.label.value : "Sconosciuto";
        const abstractFull = item.abstract ? item.abstract.value : "";
        let abstractTruncated = abstractFull;
        if(abstractTruncated.length > 300) {
          abstractTruncated = abstractTruncated.substring(0, 300) + "...";
        }
        const depiction = item.depiction ? item.depiction.value : "";
        const resource = item.s ? item.s.value : "";
        let categoryDisplay = "";
        if(item.category && showCategories) {
          categoryDisplay = item.category.value.substring(item.category.value.lastIndexOf("/") + 1);
        }
        
        let content = "";
        if(showImages && depiction) {
          content += `<img src="${depiction}" alt="${escapeHtml(label)}" class="img-fluid mb-3">`;
        }
        if(showText) {
          content += `<p>${escapeHtml(abstractTruncated)}</p>`;
        }
        if(showCategories && categoryDisplay) {
          content += `<p><strong>Categoria:</strong> ${escapeHtml(categoryDisplay)}</p>`;
        }
        // Utilizziamo JSON.stringify per passare in modo sicuro i dati alla funzione della modale
        content += `<button class="btn btn-sm btn-secondary" onclick='openDetailModal(${JSON.stringify(label)}, ${JSON.stringify(abstractFull)}, ${JSON.stringify(depiction)}, ${showImages}, ${showText}, ${JSON.stringify(categoryDisplay)}, ${showCategories}, ${JSON.stringify(resource)})'>Leggi di più</button>`;
        
        const card = document.createElement("div");
        card.className = "result-item";
        card.innerHTML = `<h3>${escapeHtml(label)}</h3>` + content;
        resultContainer.appendChild(card);
      });
    }
    
    // Funzione per aprire la modale con i dettagli completi
    function openDetailModal(title, fullText, depiction, showImages, showText, categoryText, showCategories, resource) {
      const modalTitle = document.getElementById("detailModalLabel");
      const modalBody = document.getElementById("modalBodyContent");
      
      modalTitle.textContent = title;
      let innerHtml = "";
      if(showImages && depiction) {
        innerHtml += `<img src="${depiction}" alt="${title}" class="img-fluid mb-3">`;
      }
      if(showText) {
        innerHtml += `<p>${fullText}</p>`;
      }
      if(showCategories && categoryText) {
        innerHtml += `<p><strong>Categoria:</strong> ${categoryText}</p>`;
      }
      // Aggiungiamo anche un link per visitare la pagina DBpedia (apre in nuova scheda)
      if(resource) {
        innerHtml += `<p><a href="${resource}" target="_blank" class="btn btn-sm btn-primary">Visita DBpedia</a></p>`;
      }
      
      modalBody.innerHTML = innerHtml;
      
      const detailModalElem = document.getElementById("detailModal");
      const detailModal = new bootstrap.Modal(detailModalElem);
      detailModal.show();
    }
    
    // Funzione per effettuare l'escape dei caratteri HTML per prevenire XSS
    function escapeHtml(text) {
      if (typeof text !== "string") return text;
      const map = {
        "&": "&amp;",
        "<": "&lt;",
        ">": "&gt;",
        "\"": "&quot;",
        "'": "&#039;"
      };
      return text.replace(/[&<>"']/g, function(m) { return map[m]; });
    }
  </script>
</body>
</html>
