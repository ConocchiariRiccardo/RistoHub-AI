<?php
error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
ini_set('display_errors', 0);

session_start();
require_once '../template2.inc.php';
require_once '../php/config/conf.php';
require_once '../php/class/Utente.php';
require_once '../php/includes/auth.php';

if (isLoggato()) {
    header("Location: ./utente.php");
    exit;
}

$errore = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['registra'])) {
    $username = trim($_POST['username'] ?? '');
    $nome     = trim($_POST['nome'] ?? '');
    $cognome  = trim($_POST['cognome'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $conferma = $_POST['conferma_password'] ?? '';

    if (empty($username) || empty($nome) || empty($cognome) || empty($email) || empty($password)) {
        $errore = "Compila tutti i campi.";
    } elseif ($password !== $conferma) {
        $errore = "Le password non coincidono.";
    } elseif (strlen($password) < 8) {
        $errore = "La password deve essere di almeno 8 caratteri.";
    } else {
        $utenteObj = new Utente();
        $ok = $utenteObj->registra($username, $nome, $cognome, $email, $password);
        if ($ok) {
            header("Location: ./login.php?registrato=1");
            exit;
        } else {
            $errore = "Email o username già in uso.";
        }
    }
}

$t = new Template("../templates/registrazione");
$t->setContent("errore", !empty($errore) ? "<p class='msg-errore'>" . htmlspecialchars($errore) . "</p>" : "");
$t->close();
?>