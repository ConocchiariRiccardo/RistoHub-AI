<?php
error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
ini_set('display_errors', 0);

session_start();
require_once '../template2.inc.php';
require_once '../php/config/conf.php';
require_once '../php/class/Utente.php';
require_once '../php/class/Piatto.php';
require_once '../php/class/Tavolo.php';
require_once '../php/class/Prenotazione.php';
require_once '../php/class/Ordine.php';
require_once '../php/class/Ingrediente.php';
require_once '../php/includes/auth.php';

requireAccesso('admin.php');

$utenteObj      = new Utente();
$piattoObj      = new Piatto();
$tavoloObj      = new Tavolo();
$prenotazioneObj = new Prenotazione();
$ordineObj      = new Ordine();
$ingredienteObj = new Ingrediente();

$utenti  = $utenteObj->getUtenti();
$piatti  = $piattoObj->getPiatti();
$tavoli  = $tavoloObj->getTavoli();
$categorie = $piattoObj->getCategorie();
$prenotazioni = $prenotazioneObj->getPrenotazioni();
$ordini  = $ordineObj->getTuttiOrdini();
$ingredienti = $ingredienteObj->getIngredienti();
$sottoSoglia = $ingredienteObj->getIngredientSottoSoglia();

// Statistiche
$incassoOggi  = $ordineObj->getIncassoOggi();
$incassoMese  = $ordineObj->getIncassoMese();
$incassoTotale = $ordineObj->getIncassoTotale();
$topPiatti    = $ordineObj->getTopPiatti();
$statTavoli   = $tavoloObj->getStatistiche();

$piattoDaModificare  = null;
$utenteDaModificare  = null;
$tavoloDaModificare  = null;
$ingredienteDaModificare = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Gestione piatti
    if (isset($_POST['modifica_piatto'])) {
        $piattoDaModificare = $piattoObj->getPiatto(intval($_POST['modifica_piatto']));
    } elseif (isset($_POST['elimina_piatto'])) {
        $piattoObj->eliminaPiatto(intval($_POST['elimina_piatto']));
        header("Location: " . $_SERVER['PHP_SELF'] . "?tab=menu"); exit;
    } elseif (isset($_POST['salva_piatto'])) {
        $nome        = trim($_POST['nome_piatto']);
        $descrizione = trim($_POST['descrizione']);
        $prezzo      = floatval($_POST['prezzo']);
        $categorieId = intval($_POST['categorie_id']);
        $img         = trim($_POST['img']);
        $disponibile = isset($_POST['disponibile']) ? 1 : 0;
        if (!empty($_POST['id_piatto'])) {
            $piattoObj->modificaPiatto(intval($_POST['id_piatto']), $nome, $descrizione, $prezzo, $categorieId, $img, $disponibile);
        } else {
            $piattoObj->aggiungiPiatto($nome, $descrizione, $prezzo, $categorieId, $img, $disponibile);
        }
        header("Location: " . $_SERVER['PHP_SELF'] . "?tab=menu"); exit;
    }

    // Gestione tavoli
    if (isset($_POST['modifica_tavolo'])) {
        $tavoloDaModificare = $tavoloObj->getTavolo(intval($_POST['modifica_tavolo']));
    } elseif (isset($_POST['elimina_tavolo'])) {
        $tavoloObj->eliminaTavolo(intval($_POST['elimina_tavolo']));
        header("Location: " . $_SERVER['PHP_SELF'] . "?tab=tavoli"); exit;
    } elseif (isset($_POST['salva_tavolo'])) {
        $numero     = intval($_POST['numero']);
        $capacita   = intval($_POST['capacita_max']);
        $posizione  = trim($_POST['posizione'] ?? '');
        $stato      = $_POST['stato'];
        if (!empty($_POST['id_tavolo'])) {
            $tavoloObj->modificaTavolo(intval($_POST['id_tavolo']), $numero, $capacita, $posizione, $stato);
        } else {
            $tavoloObj->aggiungiTavolo($numero, $capacita, $posizione, $stato);
        }
        header("Location: " . $_SERVER['PHP_SELF'] . "?tab=tavoli"); exit;
    }

    // Gestione utenti
    if (isset($_POST['modifica_utente'])) {
        $utenteDaModificare = $utenteObj->getUtente(intval($_POST['modifica_utente']));
    } elseif (isset($_POST['elimina_utente'])) {
        $utenteObj->eliminaUtente(intval($_POST['elimina_utente']));
        header("Location: " . $_SERVER['PHP_SELF'] . "?tab=utenti"); exit;
    } elseif (isset($_POST['salva_utente'])) {
        $username = trim($_POST['username']);
        $nome     = trim($_POST['nome']);
        $cognome  = trim($_POST['cognome']);
        $email    = trim($_POST['email']);
        $ruolo    = $_POST['ruolo'];
        $password = !empty($_POST['password']) ? $_POST['password'] : null;
        if (!empty($_POST['id_utente'])) {
            $utenteObj->modificaUtente(intval($_POST['id_utente']), $username, $nome, $cognome, $email, $ruolo, $password);
        } else {
            $utenteObj->aggiungiUtente($username, $nome, $cognome, $email, $password ?? '', $ruolo);
        }
        header("Location: " . $_SERVER['PHP_SELF'] . "?tab=utenti"); exit;
    }

    // Gestione ordini
    if (isset($_POST['cambia_stato_ordine'])) {
        $ordineObj->cambiaStato(intval($_POST['id_ordine']), $_POST['stato_ordine']);
        header("Location: " . $_SERVER['PHP_SELF'] . "?tab=ordini"); exit;
    }

    // Gestione ingredienti
    if (isset($_POST['modifica_ingrediente'])) {
        $ingredienteDaModificare = $ingredienteObj->getIngrediente(intval($_POST['modifica_ingrediente']));
    } elseif (isset($_POST['elimina_ingrediente'])) {
        $ingredienteObj->eliminaIngrediente(intval($_POST['elimina_ingrediente']));
        header("Location: " . $_SERVER['PHP_SELF'] . "?tab=magazzino"); exit;
    } elseif (isset($_POST['salva_ingrediente'])) {
        $nome      = trim($_POST['nome_ingrediente']);
        $quantita  = floatval($_POST['quantita']);
        $unita     = trim($_POST['unita_misura']);
        $soglia    = floatval($_POST['soglia_minima']);
        if (!empty($_POST['id_ingrediente'])) {
            $ingredienteObj->modificaIngrediente(intval($_POST['id_ingrediente']), $nome, $quantita, $unita, $soglia);
        } else {
            $ingredienteObj->aggiungiIngrediente($nome, $quantita, $unita, $soglia);
        }
        header("Location: " . $_SERVER['PHP_SELF'] . "?tab=magazzino"); exit;
    }
}

// Tab attiva
$tabAttiva = $_GET['tab'] ?? 'statistiche';

// HTML statistiche
$htmlStatistiche  = "<div class='stats-grid'>";
$htmlStatistiche .= "<div class='stat-card'><h3>Incasso oggi</h3><div class='stat-value' style='color:#10b981'>€ " . number_format($incassoOggi, 2, ',', '.') . "</div></div>";
$htmlStatistiche .= "<div class='stat-card'><h3>Incasso mese</h3><div class='stat-value' style='color:#3b82f6'>€ " . number_format($incassoMese, 2, ',', '.') . "</div></div>";
$htmlStatistiche .= "<div class='stat-card'><h3>Incasso totale</h3><div class='stat-value' style='color:#f59e0b'>€ " . number_format($incassoTotale, 2, ',', '.') . "</div></div>";
$htmlStatistiche .= "<div class='stat-card'><h3>Tavoli liberi</h3><div class='stat-value'>" . ($statTavoli['liberi'] ?? 0) . " / " . ($statTavoli['totale'] ?? 0) . "</div></div>";
$htmlStatistiche .= "</div>";

// Top piatti
$htmlStatistiche .= "<div class='top-piatti'><h3>Top piatti più ordinati</h3><table class='top-table'>";
$i = 1;
foreach ($topPiatti as $p) {
    $htmlStatistiche .= "<tr><td><span class='rank-badge'>" . $i . "</span></td>";
    $htmlStatistiche .= "<td><strong>" . htmlspecialchars($p['nome']) . "</strong></td>";
    $htmlStatistiche .= "<td style='text-align:right'>" . $p['totale_ordinato'] . " ordinazioni</td></tr>";
    $i++;
}
$htmlStatistiche .= "</table></div>";

// Avvisi magazzino
if (!empty($sottoSoglia)) {
    $htmlStatistiche .= "<div class='alert-box'><h3>⚠️ Ingredienti sotto soglia minima</h3><ul>";
    foreach ($sottoSoglia as $ing) {
        $htmlStatistiche .= "<li>" . htmlspecialchars($ing['nome']) . " — " . $ing['quantita_magazzino'] . " " . $ing['unita_misura'] . " (minimo: " . $ing['soglia_minima'] . ")</li>";
    }
    $htmlStatistiche .= "</ul></div>";
}

// HTML piatti
$htmlPiatti = "";
$piattiPerCat = [];
foreach ($piatti as $p) {
    $piattiPerCat[$p['categoria_nome']][] = $p;
}
foreach ($piattiPerCat as $cat => $lista) {
    $htmlPiatti .= "<h3>" . htmlspecialchars($cat ?? 'Senza categoria') . "</h3><div class='cards-grid'>";
    foreach ($lista as $p) {
        $disp = $p['disponibile'] ? "✓ Disponibile" : "✗ Non disponibile";
        $htmlPiatti .= "<article class='card'>";
        $htmlPiatti .= "<img src='" . htmlspecialchars($p['img']) . "' alt='" . htmlspecialchars($p['nome']) . "'>";
        $htmlPiatti .= "<div class='card-body'>";
        $htmlPiatti .= "<div class='card-title-row'><h3 class='card-title'>" . htmlspecialchars($p['nome']) . "</h3>";
        $htmlPiatti .= "<span class='card-price'>€ " . number_format($p['prezzo'], 2, ',', '.') . "</span></div>";
        $htmlPiatti .= "<p class='card-text'>" . htmlspecialchars($p['descrizione']) . "</p>";
        $htmlPiatti .= "<p class='card-text'><small>" . $disp . "</small></p>";
        $htmlPiatti .= "<form method='post'>";
        $htmlPiatti .= "<button type='submit' class='btn-secondary' name='modifica_piatto' value='" . $p['id'] . "'>Modifica</button>";
        $htmlPiatti .= "<button type='submit' class='btn-primary' name='elimina_piatto' value='" . $p['id'] . "' onclick=\"return confirm('Eliminare?')\">Elimina</button>";
        $htmlPiatti .= "</form></div></article>";
    }
    $htmlPiatti .= "</div>";
}

// HTML form piatto
$htmlFormPiatto  = "<form method='post' class='admin-form'>";
$htmlFormPiatto .= "<input type='hidden' name='id_piatto' value='" . ($piattoDaModificare['id'] ?? '') . "'>";
$htmlFormPiatto .= "<input type='text' name='nome_piatto' placeholder='Nome piatto' required value='" . htmlspecialchars($piattoDaModificare['nome'] ?? '') . "'>";
$htmlFormPiatto .= "<textarea name='descrizione' placeholder='Descrizione' required>" . htmlspecialchars($piattoDaModificare['descrizione'] ?? '') . "</textarea>";
$htmlFormPiatto .= "<input type='number' step='0.01' name='prezzo' placeholder='Prezzo' required value='" . htmlspecialchars($piattoDaModificare['prezzo'] ?? '') . "'>";
$htmlFormPiatto .= "<select name='categorie_id' required>";
foreach ($categorie as $c) {
    $sel = ($piattoDaModificare['categorie_id'] ?? '') == $c['id'] ? 'selected' : '';
    $htmlFormPiatto .= "<option value='" . $c['id'] . "' $sel>" . htmlspecialchars($c['nome']) . "</option>";
}
$htmlFormPiatto .= "</select>";
$htmlFormPiatto .= "<input type='text' name='img' placeholder='URL immagine' required value='" . htmlspecialchars($piattoDaModificare['img'] ?? '') . "'>";
$htmlFormPiatto .= "<label><input type='checkbox' name='disponibile' " . (($piattoDaModificare['disponibile'] ?? 1) ? 'checked' : '') . "> Disponibile</label>";
$htmlFormPiatto .= "<button type='submit' name='salva_piatto' class='btn-primary'>" . ($piattoDaModificare ? 'Salva modifiche' : 'Aggiungi piatto') . "</button>";
$htmlFormPiatto .= "</form>";

// HTML tavoli
$htmlTavoli = "<div class='tavoli-grid'>";
foreach ($tavoli as $t) {
    $htmlTavoli .= "<div class='card'><div class='card-body'>";
    $htmlTavoli .= "<h3>Tavolo " . htmlspecialchars($t['numero']) . "</h3>";
    $htmlTavoli .= "<p>Capacità: " . $t['capacita_max'] . " persone</p>";
    $htmlTavoli .= "<p>Posizione: " . htmlspecialchars($t['posizione'] ?? '-') . "</p>";
    $htmlTavoli .= "<p>Stato: <span class='badge badge-" . ($t['stato'] === 'libero' ? 'green' : 'red') . "'>" . $t['stato'] . "</span></p>";
    $htmlTavoli .= "<form method='post'>";
    $htmlTavoli .= "<button type='submit' class='btn-secondary' name='modifica_tavolo' value='" . $t['id'] . "' style='margin: 10px 0;'>Modifica</button>";
    $htmlTavoli .= "<button type='submit' class='btn-primary' name='elimina_tavolo' value='" . $t['id'] . "' onclick=\"return confirm('Eliminare?')\">Elimina</button>";
    $htmlTavoli .= "</form></div></div>";
}
$htmlTavoli .= "</div>";

// HTML form tavolo
$htmlFormTavolo  = "<form method='post' class='admin-form'>";
$htmlFormTavolo .= "<input type='hidden' name='id_tavolo' value='" . ($tavoloDaModificare['id'] ?? '') . "'>";
$htmlFormTavolo .= "<input type='number' name='numero' placeholder='Numero tavolo' required value='" . htmlspecialchars($tavoloDaModificare['numero'] ?? '') . "'>";
$htmlFormTavolo .= "<input type='number' name='capacita_max' placeholder='Capacità massima' required value='" . htmlspecialchars($tavoloDaModificare['capacita_max'] ?? '') . "'>";
$htmlFormTavolo .= "<input type='text' name='posizione' placeholder='Posizione (es. interno, esterno)' value='" . htmlspecialchars($tavoloDaModificare['posizione'] ?? '') . "'>";
$htmlFormTavolo .= "<select name='stato'>";
foreach (['libero', 'occupato', 'prenotato'] as $s) {
    $sel = ($tavoloDaModificare['stato'] ?? 'libero') === $s ? 'selected' : '';
    $htmlFormTavolo .= "<option value='$s' $sel>" . ucfirst($s) . "</option>";
}
$htmlFormTavolo .= "</select>";
$htmlFormTavolo .= "<button type='submit' name='salva_tavolo' class='btn-primary'>" . ($tavoloDaModificare ? 'Salva modifiche' : 'Aggiungi tavolo') . "</button>";
$htmlFormTavolo .= "</form>";

// HTML utenti
$htmlUtenti = "<div class='utenti-table'>";
$htmlUtenti .= "<div class='table-header'><span>Nome</span><span>Email</span><span>Ruolo</span><span>Punti</span><span>Azioni</span></div>";
foreach ($utenti as $u) {
    $htmlUtenti .= "<div class='table-row'>";
    $htmlUtenti .= "<span>" . htmlspecialchars($u['nome'] . ' ' . $u['cognome']) . "</span>";
    $htmlUtenti .= "<span>" . htmlspecialchars($u['email']) . "</span>";
    $htmlUtenti .= "<span><span class='badge badge-blue'>" . htmlspecialchars($u['ruolo']) . "</span></span>";
    $htmlUtenti .= "<span>" . $u['punti_loyalty'] . " pt</span>";
    $htmlUtenti .= "<span><form method='post'>";
    $htmlUtenti .= "<button type='submit' class='btn-secondary btn-sm' name='modifica_utente' value='" . $u['id'] . "'>Modifica</button>";
    $htmlUtenti .= "<button type='submit' class='btn-primary btn-sm' name='elimina_utente' value='" . $u['id'] . "' onclick=\"return confirm('Eliminare?')\">Elimina</button>";
    $htmlUtenti .= "</form></span></div>";
}
$htmlUtenti .= "</div>";

// HTML form utente
$htmlFormUtente  = "<form method='post' class='admin-form'>";
$htmlFormUtente .= "<input type='hidden' name='id_utente' value='" . ($utenteDaModificare['id'] ?? '') . "'>";
$htmlFormUtente .= "<input type='text' name='username' placeholder='Username' required value='" . htmlspecialchars($utenteDaModificare['username'] ?? '') . "'>";
$htmlFormUtente .= "<input type='text' name='nome' placeholder='Nome' required value='" . htmlspecialchars($utenteDaModificare['nome'] ?? '') . "'>";
$htmlFormUtente .= "<input type='text' name='cognome' placeholder='Cognome' required value='" . htmlspecialchars($utenteDaModificare['cognome'] ?? '') . "'>";
$htmlFormUtente .= "<input type='email' name='email' placeholder='Email' required value='" . htmlspecialchars($utenteDaModificare['email'] ?? '') . "'>";
if ($utenteDaModificare) {
    $htmlFormUtente .= "<button type='button' class='btn-secondary' onclick='document.getElementById(\"pwd\").style.display=\"block\"'>Cambia password</button>";
    $htmlFormUtente .= "<input type='password' id='pwd' name='password' placeholder='Nuova password' style='display:none'>";
} else {
    $htmlFormUtente .= "<input type='password' name='password' placeholder='Password' required>";
}
$htmlFormUtente .= "<select name='ruolo' required>";
foreach (['admin', 'cameriere', 'cuoco', 'cliente'] as $r) {
    $sel = ($utenteDaModificare['ruolo'] ?? '') === $r ? 'selected' : '';
    $htmlFormUtente .= "<option value='$r' $sel>" . ucfirst($r) . "</option>";
}
$htmlFormUtente .= "</select>";
$htmlFormUtente .= "<button type='submit' name='salva_utente' class='btn-primary'>" . ($utenteDaModificare ? 'Salva modifiche' : 'Aggiungi utente') . "</button>";
$htmlFormUtente .= "</form>";

// HTML ordini
$htmlOrdini = "<div class='ordini-table'>";
foreach ($ordini as $o) {
    $htmlOrdini .= "<div class='ordine-row'>";
    $htmlOrdini .= "<div><strong>Tavolo " . htmlspecialchars($o['numero_tavolo']) . "</strong> — " . htmlspecialchars($o['created_at']) . "</div>";
    $htmlOrdini .= "<div>€ " . number_format($o['totale'], 2, ',', '.') . "</div>";
    $htmlOrdini .= "<div><span class='badge badge-blue'>" . htmlspecialchars($o['stato']) . "</span></div>";
    $htmlOrdini .= "<div><form method='post' style='display:flex;gap:8px;align-items:center'>";
    $htmlOrdini .= "<input type='hidden' name='id_ordine' value='" . $o['id'] . "'>";
    $htmlOrdini .= "<select name='stato_ordine'>";
    $statiOrdine = [
        'inviato'         => '🆕 Inviato',
        'in_preparazione' => '👨‍🍳 In preparazione',
        'pronto'          => '✅ Pronto',
        'completato'      => '🏁 Completato',
    ];

    foreach ($statiOrdine as $valore => $etichetta) {
        $sel = $o['stato'] === $valore ? 'selected' : '';
        $htmlOrdini .= "<option value='$valore' $sel>$etichetta</option>";
    }
    $htmlOrdini .= "</select>";
    $htmlOrdini .= "<button type='submit' name='cambia_stato_ordine' class='btn-primary btn-sm'>Aggiorna</button>";
    $htmlOrdini .= "</form></div></div>";
}
$htmlOrdini .= "</div>";

// HTML magazzino
$htmlMagazzino = "<div class='magazzino-table'>";
$htmlMagazzino .= "<div class='table-header'><span>Ingrediente</span><span>Quantità</span><span>Unità</span><span>Soglia min.</span><span>Azioni</span></div>";
foreach ($ingredienti as $ing) {
    $alert = $ing['quantita_magazzino'] <= $ing['soglia_minima'] ? " style='background:#fff3cd'" : '';
    $htmlMagazzino .= "<div class='table-row'$alert>";
    $htmlMagazzino .= "<span>" . htmlspecialchars($ing['nome']) . "</span>";
    $htmlMagazzino .= "<span>" . $ing['quantita_magazzino'] . "</span>";
    $htmlMagazzino .= "<span>" . htmlspecialchars($ing['unita_misura']) . "</span>";
    $htmlMagazzino .= "<span>" . $ing['soglia_minima'] . "</span>";
    $htmlMagazzino .= "<span><form method='post'>";
    $htmlMagazzino .= "<button type='submit' class='btn-secondary btn-sm' name='modifica_ingrediente' value='" . $ing['id'] . "'>Modifica</button>";
    $htmlMagazzino .= "<button type='submit' class='btn-primary btn-sm' name='elimina_ingrediente' value='" . $ing['id'] . "' onclick=\"return confirm('Eliminare?')\">Elimina</button>";
    $htmlMagazzino .= "</form></span></div>";
}
$htmlMagazzino .= "</div>";

// HTML form ingrediente
$htmlFormIngrediente  = "<form method='post' class='admin-form'>";
$htmlFormIngrediente .= "<input type='hidden' name='id_ingrediente' value='" . ($ingredienteDaModificare['id'] ?? '') . "'>";
$htmlFormIngrediente .= "<input type='text' name='nome_ingrediente' placeholder='Nome ingrediente' required value='" . htmlspecialchars($ingredienteDaModificare['nome'] ?? '') . "'>";
$htmlFormIngrediente .= "<input type='number' step='0.01' name='quantita' placeholder='Quantità in magazzino' required value='" . htmlspecialchars($ingredienteDaModificare['quantita_magazzino'] ?? '') . "'>";
$htmlFormIngrediente .= "<input type='text' name='unita_misura' placeholder='Unità (kg, litri, pz...)' required value='" . htmlspecialchars($ingredienteDaModificare['unita_misura'] ?? '') . "'>";
$htmlFormIngrediente .= "<input type='number' step='0.01' name='soglia_minima' placeholder='Soglia minima' required value='" . htmlspecialchars($ingredienteDaModificare['soglia_minima'] ?? '') . "'>";
$htmlFormIngrediente .= "<button type='submit' name='salva_ingrediente' class='btn-primary'>" . ($ingredienteDaModificare ? 'Salva modifiche' : 'Aggiungi ingrediente') . "</button>";
$htmlFormIngrediente .= "</form>";

// Prenotazioni oggi
$prenotazioniOggi = $prenotazioneObj->getPrenotazioniOggi();
$htmlPrenotazioni = "<div class='prenotazioni-list'>";
foreach ($prenotazioni as $p) {
    $htmlPrenotazioni .= "<div class='prenotazione-row'>";
    $htmlPrenotazioni .= "<span>" . htmlspecialchars($p['nome']) . "</span>";
    $htmlPrenotazioni .= "<span>" . htmlspecialchars($p['data']) . " " . htmlspecialchars($p['ora']) . "</span>";
    $htmlPrenotazioni .= "<span>" . $p['persone'] . " persone</span>";
    $htmlPrenotazioni .= "<span>" . ($p['numero_tavolo'] ? "Tavolo " . $p['numero_tavolo'] : "Non assegnato") . "</span>";
    $htmlPrenotazioni .= "<span><span class='badge badge-blue'>" . $p['stato'] . "</span></span>";
    $htmlPrenotazioni .= "<span><form method='post'>";
    $htmlPrenotazioni .= "<button type='submit' class='btn-primary btn-sm' name='elimina_prenotazione' value='" . $p['id'] . "' onclick=\"return confirm('Eliminare?')\">Elimina</button>";
    $htmlPrenotazioni .= "</form></span></div>";
}
$htmlPrenotazioni .= "</div>";

$t = new Template("../templates/admin");
$t->setContent("tab_attiva", $tabAttiva);
$t->setContent("nome_admin", htmlspecialchars($_SESSION['nome']));
$t->setContent("statistiche", $htmlStatistiche);
$t->setContent("piatti", $htmlPiatti);
$t->setContent("form_piatto", $htmlFormPiatto);
$t->setContent("titolo_form_piatto", $piattoDaModificare ? 'Modifica piatto' : 'Aggiungi piatto');
$t->setContent("tavoli", $htmlTavoli);
$t->setContent("form_tavolo", $htmlFormTavolo);
$t->setContent("titolo_form_tavolo", $tavoloDaModificare ? 'Modifica tavolo' : 'Aggiungi tavolo');
$t->setContent("utenti", $htmlUtenti);
$t->setContent("form_utente", $htmlFormUtente);
$t->setContent("titolo_form_utente", $utenteDaModificare ? 'Modifica utente' : 'Aggiungi utente');
$t->setContent("ordini", $htmlOrdini);
$t->setContent("magazzino", $htmlMagazzino);
$t->setContent("form_ingrediente", $htmlFormIngrediente);
$t->setContent("titolo_form_ingrediente", $ingredienteDaModificare ? 'Modifica ingrediente' : 'Aggiungi ingrediente');
$t->setContent("prenotazioni", $htmlPrenotazioni);
$t->close();
?>