<?php
error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
ini_set('display_errors', 0);

session_start();
require_once '../template2.inc.php';
require_once '../php/config/conf.php';
require_once '../php/class/Utente.php';
require_once '../php/includes/auth.php';

// Se già loggato reindirizza
if (isLoggato()) {
    switch (getRuolo()) {
        case 'admin':     header("Location: ./admin.php"); exit;
        case 'cameriere': header("Location: ./cameriere.php"); exit;
        case 'cuoco':     header("Location: ./cuoco.php"); exit;
        default:          header("Location: ./utente.php"); exit;
    }
}

$errore  = "";
$successo = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $errore = "Compila tutti i campi.";
    } else {
        $utenteObj = new Utente();
        $utente    = $utenteObj->login($email, $password);
        if ($utente) {
            setSessioneUtente($utente);
            switch ($utente['ruolo']) {
                case 'admin':     header("Location: ./admin.php"); exit;
                case 'cameriere': header("Location: ./cameriere.php"); exit;
                case 'cuoco':     header("Location: ./cuoco.php"); exit;
                default:          header("Location: ./utente.php"); exit;
            }
        } else {
            $errore = "Email o password errati.";
        }
    }
}

// Messaggio da registrazione
if (isset($_GET['registrato'])) {
    $successo = "Registrazione completata! Ora puoi accedere.";
}

$t = new Template("../templates/login");
$t->setContent("errore", !empty($errore) ? "<p class='msg-errore'>" . htmlspecialchars($errore) . "</p>" : "");
$t->setContent("successo", !empty($successo) ? "<p class='msg-successo'>" . htmlspecialchars($successo) . "</p>" : "");
$t->close();
?>