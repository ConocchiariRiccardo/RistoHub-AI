<?php
error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
ini_set('display_errors', 0);

session_start();
require_once './template2.inc.php';
require_once './php/config/conf.php';
require_once './php/class/Piatto.php';
require_once './php/class/Recensione.php';

$piattoObj    = new Piatto();
$recensioneObj = new Recensione();

$piattiFeatured  = array_slice($piattoObj->getMenu(), 0, 3);
$ultimiRecensioni = $recensioneObj->getUltimeRecensioni(5);
$mediaVoti       = $recensioneObj->getMediaVoti();

// HTML piatti in evidenza
$htmlPiatti = "";
foreach ($piattiFeatured as $p) {
    $htmlPiatti .= "<article class='card'>";
    $htmlPiatti .= "<img src='" . htmlspecialchars($p['img']) . "' alt='" . htmlspecialchars($p['nome']) . "'>";
    $htmlPiatti .= "<div class='card-body'>";
    $htmlPiatti .= "<div class='card-title-row'>";
    $htmlPiatti .= "<h3 class='card-title'>" . htmlspecialchars($p['nome']) . "</h3>";
    $htmlPiatti .= "<span class='card-price'>€ " . number_format($p['prezzo'], 2, ',', '.') . "</span>";
    $htmlPiatti .= "</div>";
    $htmlPiatti .= "<p class='card-text'>" . htmlspecialchars($p['descrizione']) . "</p>";
    $htmlPiatti .= "<span class='card-tag'>" . htmlspecialchars($p['categoria_nome']) . "</span>";
    $htmlPiatti .= "</div></article>";
}

// HTML slider recensioni
$htmlRecensioni = "";
foreach ($ultimiRecensioni as $r) {
    $stelle = str_repeat("★", $r['voto']) . str_repeat("☆", 5 - $r['voto']);
    $autore = $r['nome'] ? htmlspecialchars($r['nome']) : 'Ospite';
    $htmlRecensioni .= "<div class='review-slide'>";
    $htmlRecensioni .= "<p class='review-stars'>" . $stelle . "</p>";
    $htmlRecensioni .= "<p class='review-text'>\"" . htmlspecialchars($r['commento']) . "\"</p>";
    $htmlRecensioni .= "<p class='review-author'>— " . $autore . "</p>";
    $htmlRecensioni .= "</div>";
}

// Navbar auth
if (isset($_SESSION['user_id'])) {
    switch ($_SESSION['ruolo']) {
        case 'admin':     $dest = './public/admin.php'; break;
        case 'cameriere': $dest = './public/cameriere.php'; break;
        case 'cuoco':     $dest = './public/cuoco.php'; break;
        default:          $dest = './public/utente.php';
    }
    $htmlNavAuth  = "<span class='nav-benvenuto'>Ciao, " . htmlspecialchars($_SESSION['nome']) . "!</span>";
    $htmlNavAuth .= "<a href='{$dest}' class='nav-cta'>Il mio account</a>";
    $htmlNavAuth .= "<a href='./public/logout.php' class='nav-cta nav-cta-outline'>Logout</a>";
} else {
    $htmlNavAuth  = "<a href='./public/login.php' class='nav-cta'>Login</a>";
    $htmlNavAuth .= "<a href='./public/registrazione.php' class='nav-cta nav-cta-outline'>Registrati</a>";
}

$t = new Template("templates/index");
$t->setContent("nav_auth", $htmlNavAuth);
$t->setContent("piatti", $htmlPiatti);
$t->setContent("recensioni", $htmlRecensioni);
$t->setContent("media_voti", $mediaVoti);
$t->close();
?>