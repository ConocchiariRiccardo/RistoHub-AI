<?php
error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
ini_set('display_errors', 0);

session_start();
require_once '../php/config/conf.php';
require_once '../php/class/Ordine.php';
require_once '../php/class/Tavolo.php';
require_once '../php/includes/auth.php';

header('Content-Type: application/json');

if (!isLoggato() || getRuolo() !== 'cameriere') {
    echo json_encode(['success' => false, 'message' => 'Non autorizzato']);
    exit;
}

$body     = json_decode(file_get_contents('php://input'), true);
$idTavolo = intval($body['id_tavolo'] ?? 0);
$piatti   = $body['piatti'] ?? [];
$note     = trim($body['note'] ?? '');

if (!$idTavolo || empty($piatti)) {
    echo json_encode(['success' => false, 'message' => 'Dati mancanti']);
    exit;
}

$ordineObj = new Ordine();
$tavoloObj = new Tavolo();

$piattiFormattati = array_map(fn($p) => [
    'id'  => intval($p['id']),
    'qty' => intval($p['qty'])
], $piatti);

$idOrdine = $ordineObj->creaOrdine($idTavolo, $piattiFormattati, $note ?: null);

if ($idOrdine > 0) {
    $tavoloObj->setStato($idTavolo, 'occupato');
    echo json_encode(['success' => true, 'id_ordine' => $idOrdine]);
} else {
    echo json_encode(['success' => false, 'message' => "Errore nella creazione dell'ordine"]);
}
?>