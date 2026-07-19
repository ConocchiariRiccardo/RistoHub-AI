<?php
error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
ini_set('display_errors', 0);

session_start();
require_once '../template2.inc.php';
require_once '../php/config/conf.php';
require_once '../php/class/Recensione.php';
require_once '../php/includes/auth.php';

$recensioneObj = new Recensione();
$errore  = "";
$successo = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['invia'])) {
    $voto     = intval($_POST['voto'] ?? 0);
    $commento = trim($_POST['commento'] ?? '');
    $usersId  = getUserId();

    if ($voto < 1 || $voto > 5) {
        $errore = "Scegli un voto tra 1 e 5.";
    } elseif (empty($commento)) {
        $errore = "Scrivi un commento.";
    } else {
        $ok = $recensioneObj->aggiungiRecensione($usersId, $voto, $commento);
        $successo = $ok ? "Grazie per la tua recensione!" : "Errore durante l'invio.";
    }
}

$recensioni = $recensioneObj->getRecensioni();
$media      = $recensioneObj->getMediaVoti();

$htmlRecensioni = "";
if (!empty($recensioni)) {
    foreach ($recensioni as $r) {
        $stelle = str_repeat("★", $r['voto']) . str_repeat("☆", 5 - $r['voto']);
        $autore = $r['nome'] ? htmlspecialchars($r['nome'] . ' ' . $r['cognome']) : 'Ospite';
        $htmlRecensioni .= "<article class='review-card'>";
        $htmlRecensioni .= "<p class='review-stars'>" . $stelle . "</p>";
        $htmlRecensioni .= "<p class='review-text'>" . htmlspecialchars($r['commento']) . "</p>";
        $htmlRecensioni .= "<p class='review-author'>— " . $autore . "</p>";
        $htmlRecensioni .= "<p class='review-date'>" . htmlspecialchars($r['created_at']) . "</p>";
        $htmlRecensioni .= "</article>";
    }
} else {
    $htmlRecensioni = "<p style='text-align:center'>Nessuna recensione ancora. Sii il primo!</p>";
}

// Navbar auth
if (isLoggato()) {
    $htmlNavAuth = "<a href='./utente.php' class='nav-cta'>Il mio account</a>";
    $htmlNavAuth .= "<a href='./logout.php' class='nav-cta nav-cta-outline'>Logout</a>";
} else {
    $htmlNavAuth = "<a href='./login.php' class='nav-cta'>Login</a>";
}

$t = new Template("../templates/recensione");
$t->setContent("nav_auth", $htmlNavAuth);
$t->setContent("errore", !empty($errore) ? "<p class='msg-errore'>" . htmlspecialchars($errore) . "</p>" : "");
$t->setContent("successo", !empty($successo) ? "<p class='msg-successo'>" . htmlspecialchars($successo) . "</p>" : "");
$t->setContent("recensioni", $htmlRecensioni);
$t->setContent("media_voti", $media);
$t->close();
?>