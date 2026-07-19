<?php
error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
ini_set('display_errors', 0);

session_start();
require_once '../template2.inc.php';
require_once '../php/config/conf.php';
require_once '../php/class/Prenotazione.php';
require_once '../php/class/Recensione.php';
require_once '../php/includes/auth.php';

requireRuolo('cliente');

$prenotazioneObj = new Prenotazione();
$recensioneObj   = new Recensione();

$miePrenotazioni = $prenotazioneObj->getPrenotazioniUtente(getUserId());

// HTML prenotazioni
$htmlPrenotazioni = "";
if (!empty($miePrenotazioni)) {
    foreach ($miePrenotazioni as $p) {
        $stato = match($p['stato']) {
            'confermata'  => "<span class='badge badge-green'>Confermata</span>",
            'annullata'   => "<span class='badge badge-red'>Annullata</span>",
            default       => "<span class='badge badge-yellow'>In attesa</span>",
        };
        $tavolo = $p['numero_tavolo'] ? "Tavolo " . $p['numero_tavolo'] : "Non assegnato";
        $htmlPrenotazioni .= "<div class='card prenotazione-card'>";
        $htmlPrenotazioni .= "<div class='card-body'>";
        $htmlPrenotazioni .= "<div class='card-title-row'>";
        $htmlPrenotazioni .= "<h3 class='card-title'>📅 " . htmlspecialchars($p['data']) . " alle " . htmlspecialchars($p['ora']) . "</h3>";
        $htmlPrenotazioni .= $stato;
        $htmlPrenotazioni .= "</div>";
        $htmlPrenotazioni .= "<p class='card-text'>👥 " . htmlspecialchars($p['persone']) . " persone</p>";
        $htmlPrenotazioni .= "<p class='card-text'>🪑 " . $tavolo . "</p>";
        if ($p['note']) {
            $htmlPrenotazioni .= "<p class='card-text'>📝 " . htmlspecialchars($p['note']) . "</p>";
        }
        $htmlPrenotazioni .= "</div></div>";
    }
} else {
    $htmlPrenotazioni = "<p class='empty-msg'>Non hai ancora effettuato prenotazioni.</p>";
}

$t = new Template("../templates/utente");
$t->setContent("nome", htmlspecialchars($_SESSION['nome']));
$t->setContent("cognome", htmlspecialchars($_SESSION['cognome']));
$t->setContent("email", htmlspecialchars($_SESSION['email']));
$t->setContent("username", htmlspecialchars($_SESSION['username']));
$t->setContent("punti", htmlspecialchars($_SESSION['punti'] ?? 0));
$t->setContent("prenotazioni", $htmlPrenotazioni);
$t->close();
?>