<?php
session_start();
require_once '../php/config/conf.php';
require_once '../php/class/Ordine.php';
require_once '../php/includes/auth.php';

header('Content-Type: application/json');

if (!isLoggato()) { echo json_encode([]); exit; }

$idTavolo = intval($_GET['tavolo'] ?? 0);
if (!$idTavolo) { echo json_encode([]); exit; }

$ordineObj = new Ordine();
$ordineAttivo = $ordineObj->getOrdineAttivoByTavolo($idTavolo);
if (!$ordineAttivo) { echo json_encode([]); exit; }

echo json_encode($ordineObj->getPiattiOrdine($ordineAttivo['id']));