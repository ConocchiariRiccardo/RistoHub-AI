<?php
error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
ini_set('display_errors', 0);

session_start();
require_once '../template2.inc.php';
require_once '../php/config/conf.php';
require_once '../php/class/Ordine.php';
require_once '../php/includes/auth.php';

requireAccesso('cuoco.php');

$ordineObj = new Ordine();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cambia_stato'])) {
    $ordineObj->cambiaStato(intval($_POST['id_ordine']), $_POST['nuovo_stato']);
    header("Location: " . $_SERVER['PHP_SELF']); exit;
}

$ordiniAttivi = $ordineObj->getOrdiniAttivi();

// Raggruppa per stato
$perStato = ['inviato' => [], 'in_preparazione' => [], 'pronto' => []];
foreach ($ordiniAttivi as $o) {
    if (isset($perStato[$o['stato']])) {
        $o['piatti'] = $ordineObj->getPiattiOrdine($o['id']);
        $perStato[$o['stato']][] = $o;
    }
}

function renderColonna(array $ordini, string $stato, string $etichetta, string $btnStato, string $btnLabel): string {
    $html = "<div class='kanban-col'>";
    $html .= "<div class='kanban-header'>$etichetta <span class='kanban-count'>" . count($ordini) . "</span></div>";
    foreach ($ordini as $o) {
        $html .= "<div class='kanban-card'>";
        $html .= "<div class='kanban-card-header'>";
        $html .= "<strong>Tavolo {$o['numero_tavolo']}</strong>";
        $html .= "<span class='kanban-time'>" . date('H:i', strtotime($o['created_at'])) . "</span>";
        $html .= "</div>";
        if (!empty($o['note'])) {
            $html .= "<p class='kanban-note'>📝 " . htmlspecialchars($o['note']) . "</p>";
        }
        $html .= "<ul class='kanban-piatti'>";
        foreach ($o['piatti'] as $p) {
            $html .= "<li><span class='piatto-qty'>{$p['quantita']}×</span> " . htmlspecialchars($p['nome']) . "</li>";
        }
        $html .= "</ul>";
        $html .= "<form method='post' style='margin-top:12px'>";
        $html .= "<input type='hidden' name='id_ordine' value='{$o['id']}'>";
        $html .= "<input type='hidden' name='nuovo_stato' value='$btnStato'>";
        $html .= "<button type='submit' name='cambia_stato' class='btn-primary btn-full btn-sm'>$btnLabel</button>";
        $html .= "</form></div>";
    }
    $html .= "</div>";
    return $html;
}

$htmlKanban  = "<div class='kanban-board'>";
$htmlKanban .= renderColonna($perStato['inviato'],        'inviato',        '🆕 Nuovi ordini',    'in_preparazione', '→ Inizia preparazione');
$htmlKanban .= renderColonna($perStato['in_preparazione'],'in_preparazione','👨‍🍳 In preparazione', 'pronto',           '→ Segna come pronto');
$htmlKanban .= renderColonna($perStato['pronto'],         'pronto',         '✅ Pronti',           'completato',       '→ Consegnato');
$htmlKanban .= "</div>";

$t = new Template("../templates/cuoco");
$t->setContent("nome_cuoco", htmlspecialchars($_SESSION['nome']));
$t->setContent("kanban", $htmlKanban);
$t->setContent("totale_ordini", count($ordiniAttivi));
$t->close();
?>