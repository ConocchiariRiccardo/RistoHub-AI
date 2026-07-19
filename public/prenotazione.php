<?php
error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
ini_set('display_errors', 0);

session_start();
require_once '../template2.inc.php';
require_once '../php/config/conf.php';
require_once '../php/class/Prenotazione.php';
require_once '../php/includes/auth.php';

$prenotazioneObj = new Prenotazione();
$errore  = "";
$successo = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['prenota'])) {
    $nome     = trim($_POST['nome'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $data     = $_POST['data'] ?? '';
    $ora      = $_POST['ora'] ?? '';
    $persone  = intval($_POST['persone'] ?? 0);
    $note     = trim($_POST['note'] ?? '');
    $tavoliId = !empty($_POST['tavoli_id']) ? intval($_POST['tavoli_id']) : null;
    $usersId  = getUserId();
    $orari_validi = [
        '12:00','12:30','13:00','13:30','14:00','14:30',
        '19:30','20:00','20:30','21:00','21:30','22:00','22:30'
    ];

    if (empty($nome) || empty($telefono) || empty($data) || empty($ora) || $persone <= 0) {
        $errore = "Compila tutti i campi obbligatori.";
    } else {
        $d = DateTime::createFromFormat('Y-m-d', $data);
        if (!$d || $d->format('Y-m-d') !== $data) {
            $errore = "Data non valida.";
        } elseif ($d < new DateTime('today')) {
            $errore = "Non puoi prenotare per una data passata.";
        } elseif (!in_array($ora, $orari_validi)){
            $errore = "Seleziona un orario valido tra quelli disponibili.";
        } else {
            $ok = $prenotazioneObj->aggiungiPrenotazione(
                $nome, $telefono, $data, $ora, $persone, $note, $usersId, $tavoliId
            );
            if ($ok) {
                $successo = "Prenotazione inviata con successo! Ti contatteremo per la conferma.";
            } else {
                $errore = "Errore durante la prenotazione, riprova.";
            }
        }
    }
}

// Precompila nome e telefono se loggato
$nomeDefault     = htmlspecialchars($_SESSION['nome'] ?? '');
$cognomeDefault  = htmlspecialchars($_SESSION['cognome'] ?? '');
$nomeCompleto    = trim($nomeDefault . ' ' . $cognomeDefault);

// Navbar auth
if (isLoggato()) {
    $htmlNavAuth = "<a href='./utente.php' class='nav-cta'>Il mio account</a>";
    $htmlNavAuth .= "<a href='./logout.php' class='nav-cta nav-cta-outline'>Logout</a>";
} else {
    $htmlNavAuth = "<a href='./login.php' class='nav-cta'>Login</a>";
    $htmlNavAuth .= "<a href='./registrazione.php' class='nav-cta nav-cta-outline'>Registrati</a>";
}

$t = new Template("../templates/prenotazione");
$t->setContent("nav_auth", $htmlNavAuth);
$t->setContent("errore", !empty($errore) ? "<p class='msg-errore'>" . htmlspecialchars($errore) . "</p>" : "");
$t->setContent("successo", !empty($successo) ? "<p class='msg-successo'>" . htmlspecialchars($successo) . "</p>" : "");
$t->setContent("nome_default", $nomeCompleto);
$t->close();
?>