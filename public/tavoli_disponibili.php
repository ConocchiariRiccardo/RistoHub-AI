<?php
error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
ini_set('display_errors', 0);

require_once '../php/config/conf.php';
require_once '../php/class/Prenotazione.php';

header('Content-Type: application/json');

$data    = $_GET['data']    ?? '';
$ora     = $_GET['ora']     ?? '';
$persone = intval($_GET['persone'] ?? 0);

if (!$data || !$ora || $persone <= 0) {
    echo json_encode([]);
    exit;
}

$d = DateTime::createFromFormat('Y-m-d', $data);
if (!$d || $d->format('Y-m-d') !== $data) {
    echo json_encode([]);
    exit;
}

$prenotazioneObj = new Prenotazione();
$tavoli = $prenotazioneObj->getTavoliDisponibili($persone, $data, $ora);
echo json_encode($tavoli);
?>