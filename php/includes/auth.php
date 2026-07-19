<?php
require_once __DIR__ . '/../class/Utente.php';

function requireLogin(): void {
    if (!isset($_SESSION['user_id'])) {
        header("Location: ../public/login.php");
        exit;
    }
}

function requireRuolo(string ...$ruoli): void {
    requireLogin();
    if (!in_array($_SESSION['ruolo'], $ruoli)) {
        header("Location: ../public/login.php");
        exit;
    }
}

function requireAccesso(string $servizio): void {
    requireLogin();
    $utente = new Utente();
    if (!$utente->hasAccesso($_SESSION['user_id'], $servizio)) {
        header("Location: /login.php");
        exit;
    }
}

function isLoggato(): bool {
    return isset($_SESSION['user_id']);
}

function getRuolo(): string {
    return $_SESSION['ruolo'] ?? '';
}

function getUserId(): ?int {
    return $_SESSION['user_id'] ?? null;
}

function getNome(): string {
    return $_SESSION['nome'] ?? '';
}

function setSessioneUtente(array $utente): void {
    $_SESSION['user_id']  = $utente['id'];
    $_SESSION['username'] = $utente['username'];
    $_SESSION['nome']     = $utente['nome'];
    $_SESSION['cognome']  = $utente['cognome'];
    $_SESSION['email']    = $utente['email'];
    $_SESSION['ruolo']    = $utente['ruolo'];
    $_SESSION['punti']    = $utente['punti_loyalty'];
}

function distruggiSessione(): void {
    session_unset();
    session_destroy();
}
?>