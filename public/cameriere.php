<?php
error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
ini_set('display_errors', 0);

session_start();
require_once '../template2.inc.php';
require_once '../php/config/conf.php';
require_once '../php/class/Tavolo.php';
require_once '../php/class/Ordine.php';
require_once '../php/class/Piatto.php';
require_once '../php/class/Prenotazione.php';
require_once '../php/includes/auth.php';
require_once '../php/class/Coupon.php';

requireAccesso('cameriere.php');

$tavoloObj       = new Tavolo();
$ordineObj       = new Ordine();
$piattoObj       = new Piatto();
$prenotazioneObj = new Prenotazione();

$tavoli          = $tavoloObj->getTavoli();
$piattiPerCat    = $piattoObj->getPiattiPerCategoria();
$prenotazioni    = $prenotazioneObj->getPrenotazioni();
$ordiniAttivi    = $ordineObj->getOrdiniAttivi();
$ordiniPronti    = $ordineObj->getOrdiniPronti();

$idTavolo = isset($_GET['tavolo']) ? intval($_GET['tavolo']) : 0;
$piattiOrdine = [];

if ($idTavolo > 0) {
    $ordineAttivo = $ordineObj->getOrdineAttivoByTavolo($idTavolo);
    if ($ordineAttivo) {
        $piattiOrdine = $ordineObj->getPiattiOrdine($ordineAttivo['id']);
    }
}

$prenotazioneDaModificare = null;
if (isset($_GET['modifica_id'])) {
    $prenotazioneDaModificare = $prenotazioneObj->getPrenotazione(intval($_GET['modifica_id']));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['set_stato_tavolo'])) {
        $tavoloObj->setStato(intval($_POST['id_tavolo_stato']), $_POST['set_stato_tavolo']);
        header("Location: " . $_SERVER['PHP_SELF'] . "?tab=tavoli_vista"); exit;

    } elseif (isset($_POST['assegna_tavolo'])) {
        $idPrenotazione = intval($_POST['id_prenotazione']);
        $idTavoloAssegna = intval($_POST['id_tavolo_assegna'] ?? 0);
        if ($idPrenotazione && $idTavoloAssegna) {
            $prenotazioneObj->assegnaTavolo($idPrenotazione, $idTavoloAssegna);
            $tavoloObj->setStato($idTavoloAssegna, 'prenotato');
        }
        header("Location: " . $_SERVER['PHP_SELF'] . "?tab=sala"); exit;

    } elseif (isset($_POST['elimina_prenotazione'])) {
        $prenotazioneObj->eliminaPrenotazione(intval($_POST['id_prenotazione']));
        header("Location: " . $_SERVER['PHP_SELF'] . "?tab=sala"); exit;

    } elseif (isset($_POST['salva_prenotazione'])) {
        $nome     = trim($_POST['nome_cliente'] ?? '');
        $telefono = trim($_POST['telefono_cliente'] ?? '');
        $data     = $_POST['data_prenotazione'] ?? '';
        $ora      = $_POST['ora_prenotazione'] ?? '';
        $persone  = intval($_POST['persone'] ?? 1);
        $note     = trim($_POST['note'] ?? '');
        $tavoloId = !empty($_POST['id_tavolo']) ? intval($_POST['id_tavolo']) : null;

        if (!empty($_POST['id_prenotazione'])) {
            $prenotazioneObj->modificaPrenotazione(
                intval($_POST['id_prenotazione']),
                $nome, $telefono, $data, $ora, $persone, $note, $tavoloId
            );
        } else {
            $prenotazioneObj->aggiungiPrenotazione(
                $nome, $telefono, $data, $ora, $persone, $note, null, $tavoloId
            );
        }
        header("Location: " . $_SERVER['PHP_SELF'] . "?tab=sala"); exit;

    } elseif (isset($_POST['chiudi_ordine'])) {
        $idTavoloChiusura = intval($_POST['id_tavolo_chiusura']);
        $totale           = floatval($_POST['totale_finale']);
        $codiceCoupon     = strtoupper(trim($_POST['codice_coupon'] ?? ''));
        $ordineAttivo     = $ordineObj->getOrdineAttivoByTavolo($idTavoloChiusura);

        if ($ordineAttivo) {
            $couponObj = new Coupon();

            if ($codiceCoupon) {
                $coupon = $couponObj->getCouponValido($codiceCoupon);
                if ($coupon) {
                    $couponObj->usaCoupon($codiceCoupon, $coupon['users_id']);
                }
            }

            $ordineObj->chiudiOrdine($ordineAttivo['id'], $totale);
            $tavoloObj->setStato($idTavoloChiusura, 'libero');
        }
        header("Location: " . $_SERVER['PHP_SELF']); exit;
    }
}

$htmlSelectTavoli = "";
foreach ($tavoli as $t) {
    $selected = $idTavolo == $t['id'] ? 'selected' : '';
    $htmlSelectTavoli .= "<option value='{$t['id']}' $selected>Tavolo {$t['numero']} ({$t['stato']})</option>";
}

$htmlMenu = "";
foreach ($piattiPerCat as $cat => $piatti) {
    $htmlMenu .= "<div class='menu-category'><h3>" . htmlspecialchars($cat) . "</h3><ul>";
    foreach ($piatti as $p) {
        $htmlMenu .= "<li class='dish' data-id='{$p['id']}' data-price='{$p['prezzo']}' data-title='" . htmlspecialchars($p['nome'], ENT_QUOTES) . "'>";
        $htmlMenu .= "<span class='dish-name'>" . htmlspecialchars($p['nome']) . "</span>";
        $htmlMenu .= "<span class='dish-price'>€ " . number_format($p['prezzo'], 2, ',', '.') . "</span>";
        $htmlMenu .= "<button type='button' class='dish-add btn-primary btn-sm'>+</button>";
        $htmlMenu .= "</li>";
    }
    $htmlMenu .= "</ul></div>";
}

$htmlOrdineItems = "";
foreach ($piattiOrdine as $p) {
    $htmlOrdineItems .= "<li class='order-item' data-id='{$p['id']}' data-price='{$p['prezzo_unitario']}'>";
    $htmlOrdineItems .= "<span class='order-item-name'>" . htmlspecialchars($p['nome']) . "</span>";
    $htmlOrdineItems .= "<div class='qty-controls'>";
    $htmlOrdineItems .= "<button type='button' class='qty-minus'>−</button>";
    $htmlOrdineItems .= "<span class='qty'>{$p['quantita']}</span>";
    $htmlOrdineItems .= "<button type='button' class='qty-plus'>+</button>";
    $htmlOrdineItems .= "</div>";
    $htmlOrdineItems .= "<span class='order-item-price'>€ " . number_format($p['subtotale'], 2, ',', '.') . "</span>";
    $htmlOrdineItems .= "</li>";
}

$htmlOrdiniPronti = "";
if (!empty($ordiniPronti)) {
    foreach ($ordiniPronti as $o) {
        $htmlOrdiniPronti .= "<div class='ordine-pronto-badge'>🔔 Tavolo {$o['numero_tavolo']} — Ordine pronto!</div>";
    }
}

$htmlPrenotazioni = "";
foreach ($prenotazioni as $p) {
    $tavolo = $p['numero_tavolo'] ? "Tavolo {$p['numero_tavolo']}" : "Non assegnato";
    $htmlPrenotazioni .= "<div class='prenotazione-item'>";
    $htmlPrenotazioni .= "<div class='pren-info'>";
    $htmlPrenotazioni .= "<strong>" . htmlspecialchars($p['nome']) . "</strong> — ";
    $htmlPrenotazioni .= htmlspecialchars($p['data']) . " " . htmlspecialchars($p['ora']) . " — ";
    $htmlPrenotazioni .= $p['persone'] . " persone — " . $tavolo;
    $htmlPrenotazioni .= "<span class='badge badge-blue' style='margin-left:8px'>" . $p['stato'] . "</span>";
    $htmlPrenotazioni .= "</div>";
    $htmlPrenotazioni .= "<div class='pren-actions'>";

    // Form assegna tavolo con select
    $htmlPrenotazioni .= "<form method='post' style='display:flex;gap:8px;align-items:center;'>";
    $htmlPrenotazioni .= "<input type='hidden' name='id_prenotazione' value='{$p['id']}'>";
    $htmlPrenotazioni .= "<select name='id_tavolo_assegna' style='padding:6px 10px;border-radius:8px;border:1.5px solid var(--border);font-size:.85rem;'>";
    $htmlPrenotazioni .= "<option value=''>-- Tavolo --</option>";
    foreach ($tavoli as $t) {
        if ($t['stato'] !== 'libero') continue;
        $htmlPrenotazioni .= "<option value='{$t['id']}'>Tavolo {$t['numero']} (max {$t['capacita_max']})</option>";
    }
    $htmlPrenotazioni .= "</select>";
    $htmlPrenotazioni .= "<button type='submit' name='assegna_tavolo' class='btn-primary btn-sm'>Assegna</button>";
    $htmlPrenotazioni .= "</form>";

    // Form modifica ed elimina
    $htmlPrenotazioni .= "<form method='post' style='display:flex;gap:8px;'>";
    $htmlPrenotazioni .= "<input type='hidden' name='id_prenotazione' value='{$p['id']}'>";
    $htmlPrenotazioni .= "<button type='button' class='btn-secondary btn-sm' onclick=\"modificaPrenotazione({$p['id']})\">Modifica</button>";
    $htmlPrenotazioni .= "<button type='submit' name='elimina_prenotazione' class='btn-primary btn-sm' onclick=\"return confirm('Eliminare?')\">Elimina</button>";
    $htmlPrenotazioni .= "</form>";

    $htmlPrenotazioni .= "</div></div>";
}

$idTavoloAssegnato = $prenotazioneDaModificare['tavoli_id'] ?? null;

$titolo = $prenotazioneDaModificare ? 'Modifica prenotazione' : 'Nuova prenotazione';
$htmlFormPren  = "<form method='post' class='admin-form'>";
$htmlFormPren .= "<input type='hidden' name='id_prenotazione' value='" . ($prenotazioneDaModificare['id'] ?? '') . "'>";
$htmlFormPren .= "<input type='text' name='nome_cliente' placeholder='Nome cliente' required value='" . htmlspecialchars($prenotazioneDaModificare['nome'] ?? '') . "'>";
$htmlFormPren .= "<input type='tel' name='telefono_cliente' placeholder='Telefono' required value='" . htmlspecialchars($prenotazioneDaModificare['telefono'] ?? '') . "'>";
$htmlFormPren .= "<input type='date' name='data_prenotazione' required value='" . ($prenotazioneDaModificare['data'] ?? '') . "'>";
$htmlFormPren .= "<input type='time' name='ora_prenotazione' required value='" . ($prenotazioneDaModificare['ora'] ?? '') . "'>";
$htmlFormPren .= "<input type='number' name='persone' placeholder='Persone' min='1' required value='" . ($prenotazioneDaModificare['persone'] ?? '') . "'>";
$htmlFormPren .= "<textarea name='note' placeholder='Note'>" . htmlspecialchars($prenotazioneDaModificare['note'] ?? '') . "</textarea>";
$htmlFormPren .= "<select name='id_tavolo'>";
$htmlFormPren .= "<option value=''>-- Tavolo (opzionale) --</option>";
foreach ($tavoli as $t) {
    $isTavoloCorrente = $idTavoloAssegnato && $t['id'] == $idTavoloAssegnato;
    if ($t['stato'] !== 'libero' && !$isTavoloCorrente) continue;
    $sel = $isTavoloCorrente ? 'selected' : '';
    $label = $isTavoloCorrente ? "Tavolo {$t['numero']} (assegnato)" : "Tavolo {$t['numero']} — max {$t['capacita_max']} posti";
    $htmlFormPren .= "<option value='{$t['id']}' $sel>{$label}</option>";
}
$htmlFormPren .= "</select>";
$htmlFormPren .= "<button type='submit' name='salva_prenotazione' class='btn-primary'>$titolo</button>";
$htmlFormPren .= "</form>";

$htmlTavoliVista = "";
foreach ($tavoli as $tv) {
    $stato = $tv['stato'];
    $htmlTavoliVista .= "<div class='tavolo-sala-card {$stato}'>";
    $htmlTavoliVista .= "<div class='tavolo-sala-numero'>" . $tv['numero'] . "</div>";
    $htmlTavoliVista .= "<div class='tavolo-sala-info'>👥 max " . $tv['capacita_max'] . " — " . htmlspecialchars($tv['posizione'] ?? '-') . "</div>";
    $htmlTavoliVista .= "<div class='tavolo-sala-stato'>" . ucfirst($stato) . "</div>";
    $htmlTavoliVista .= "<form method='post'>";
    $htmlTavoliVista .= "<input type='hidden' name='id_tavolo_stato' value='{$tv['id']}'>";
    $labelMap = ['libero' => '✓ Libero', 'occupato' => '● Occupato', 'prenotato' => '◷ Prenotato'];
    $classMap  = ['libero' => 'btn-secondary', 'occupato' => 'btn-primary', 'prenotato' => 'btn-secondary'];
    foreach (['libero', 'occupato', 'prenotato'] as $s) {
        if ($s === $stato) continue;
        $htmlTavoliVista .= "<button type='submit' name='set_stato_tavolo' class='{$classMap[$s]} btn-sm' value='{$s}'>{$labelMap[$s]}</button>";
        $htmlTavoliVista .= "<input type='hidden' name='nuovo_stato_tavolo' value='{$s}'>";
    }
    $htmlTavoliVista .= "</form></div>";
}

$tabAttiva = $_GET['tab'] ?? 'ordini';

$t = new Template("../templates/cameriere");
$t->setContent("email_cameriere", htmlspecialchars($_SESSION['email']));
$t->setContent("nome_cameriere", htmlspecialchars($_SESSION['nome']));
$t->setContent("id_tavolo", $idTavolo ?: '');
$t->setContent("select_tavoli", $htmlSelectTavoli);
$t->setContent("menu", $htmlMenu);
$t->setContent("ordine_items", $htmlOrdineItems);
$t->setContent("ordini_pronti", $htmlOrdiniPronti);
$t->setContent("prenotazioni", $htmlPrenotazioni);
$t->setContent("form_prenotazione", $htmlFormPren);
$t->setContent("titolo_form_prenotazione", $titolo);
$t->setContent("tavoli_vista", $htmlTavoliVista);
$t->setContent("tab_attiva", $tabAttiva);
$t->close();
?>