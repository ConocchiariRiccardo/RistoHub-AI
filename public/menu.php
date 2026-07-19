<?php
error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
ini_set('display_errors', 0);

session_start();
require_once '../template2.inc.php';
require_once '../php/config/conf.php';
require_once '../php/class/Piatto.php';
require_once '../php/includes/auth.php';

$piattoObj       = new Piatto();
$piattiPerCat    = $piattoObj->getPiattiPerCategoria();

// HTML menu per categoria
$htmlMenu = "";
foreach ($piattiPerCat as $categoria => $piatti) {
    $htmlMenu .= "<section class='menu-categoria' id='" . htmlspecialchars(strtolower($categoria)) . "'>";
    $htmlMenu .= "<div class='container'>";
    $htmlMenu .= "<h2 class='section-title'>" . htmlspecialchars($categoria) . "</h2>";
    $htmlMenu .= "<div class='cards-grid'>";

    foreach ($piatti as $p) {
        $allergeni = $piattoObj->getAllergeni($p['id']);

        $htmlMenu .= "<article class='card'>";
        $htmlMenu .= "<img src='" . htmlspecialchars($p['img']) . "' alt='" . htmlspecialchars($p['nome']) . "'>";
        $htmlMenu .= "<div class='card-body'>";
        $htmlMenu .= "<div class='card-title-row'>";
        $htmlMenu .= "<h3 class='card-title'>" . htmlspecialchars($p['nome']) . "</h3>";
        $htmlMenu .= "<span class='card-price'>€ " . number_format($p['prezzo'], 2, ',', '.') . "</span>";
        $htmlMenu .= "</div>";
        $htmlMenu .= "<p class='card-text'>" . htmlspecialchars($p['descrizione']) . "</p>";

        // Allergeni
        if (!empty($allergeni)) {
            $htmlMenu .= "<div class='allergeni'><span class='allergeni-label'>⚠️ Allergeni:</span>";
            foreach ($allergeni as $a) {
                $htmlMenu .= "<span class='allergene-tag'>" . htmlspecialchars($a['nome']) . "</span>";
            }
            $htmlMenu .= "</div>";
        } else {
            $htmlMenu .= "<p class='no-allergeni'>✓ Nessun allergene</p>";
        }

        $htmlMenu .= "</div></article>";
    }

    $htmlMenu .= "</div></div></section>";
}

// Navbar categorie per navigazione rapida
$htmlCategorie = "";
foreach (array_keys($piattiPerCat) as $cat) {
    $htmlCategorie .= "<a href='#" . htmlspecialchars(strtolower($cat)) . "' class='cat-link'>" . htmlspecialchars($cat) . "</a>";
}

// Navbar auth
if (isLoggato()) {
    $htmlNavAuth = "<a href='./utente.php' class='nav-cta'>Il mio account</a>";
    $htmlNavAuth .= "<a href='./logout.php' class='nav-cta nav-cta-outline'>Logout</a>";
} else {
    $htmlNavAuth = "<a href='./login.php' class='nav-cta'>Login</a>";
    $htmlNavAuth .= "<a href='./registrazione.php' class='nav-cta nav-cta-outline'>Registrati</a>";
}

$t = new Template("../templates/menu");
$t->setContent("nav_auth", $htmlNavAuth);
$t->setContent("categorie", $htmlCategorie);
$t->setContent("menu", $htmlMenu);
$t->close();
?>