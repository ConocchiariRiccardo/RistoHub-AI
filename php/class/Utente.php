<?php
require_once __DIR__ . '/../config/conf.php';

class Utente {
    private $pdo;

    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
    }

    // Login
    public function login(string $email, string $password): ?array {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $utente = $stmt->fetch();
        if ($utente && password_verify($password, $utente['password'])) {
            return $utente;
        }
        return null;
    }

    // Registrazione cliente
    public function registra(string $username, string $nome, string $cognome, string $email, string $password): bool {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->pdo->prepare("INSERT INTO users (username, nome, cognome, email, password, ruolo) VALUES (?, ?, ?, ?, ?, 'cliente')");
        $ok = $stmt->execute([$username, $nome, $cognome, $email, $hash]);
        if ($ok) {
            // Assegna automaticamente al gruppo cliente
            $id = $this->pdo->lastInsertId();
            $this->assegnaGruppo($id, 4); // 4 = gruppo cliente
        }
        return $ok;
    }

    // Lista utenti (admin)
    public function getUtenti(): array {
        $stmt = $this->pdo->query("SELECT id, username, nome, cognome, email, ruolo, punti_loyalty, created_at FROM users ORDER BY created_at DESC");
        return $stmt->fetchAll();
    }

    // Singolo utente
    public function getUtente(int $id): ?array {
        $stmt = $this->pdo->prepare("SELECT id, username, nome, cognome, email, ruolo, punti_loyalty, created_at FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    // Aggiungi utente (admin)
    public function aggiungiUtente(string $username, string $nome, string $cognome, string $email, string $password, string $ruolo): bool {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->pdo->prepare("INSERT INTO users (username, nome, cognome, email, password, ruolo) VALUES (?, ?, ?, ?, ?, ?)");
        $ok = $stmt->execute([$username, $nome, $cognome, $email, $hash, $ruolo]);
        if ($ok) {
            $id = $this->pdo->lastInsertId();
            $gruppoId = $this->getGruppoIdByNome($ruolo);
            if ($gruppoId) $this->assegnaGruppo($id, $gruppoId);
        }
        return $ok;
    }

    // Modifica utente (admin)
    public function modificaUtente(int $id, string $username, string $nome, string $cognome, string $email, string $ruolo, ?string $password = null): bool {
        if (!empty($password)) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $this->pdo->prepare("UPDATE users SET username=?, nome=?, cognome=?, email=?, ruolo=?, password=? WHERE id=?");
            return $stmt->execute([$username, $nome, $cognome, $email, $ruolo, $hash, $id]);
        } else {
            $stmt = $this->pdo->prepare("UPDATE users SET username=?, nome=?, cognome=?, email=?, ruolo=? WHERE id=?");
            return $stmt->execute([$username, $nome, $cognome, $email, $ruolo, $id]);
        }
    }

    // Elimina utente (admin)
    public function eliminaUtente(int $id): bool {
        $stmt = $this->pdo->prepare("DELETE FROM users WHERE id = ?");
        return $stmt->execute([$id]);
    }

    // Aggiungi punti loyalty
    public function aggiungiPunti(int $id, int $punti): bool {
        $stmt = $this->pdo->prepare("UPDATE users SET punti_loyalty = punti_loyalty + ? WHERE id = ?");
        return $stmt->execute([$punti, $id]);
    }

    // Assegna gruppo
    private function assegnaGruppo(int $userId, int $gruppoId): void {
        $stmt = $this->pdo->prepare("INSERT IGNORE INTO users_has_groups (users_id, groups_id) VALUES (?, ?)");
        $stmt->execute([$userId, $gruppoId]);
    }

    // Ottieni id gruppo per nome
    private function getGruppoIdByNome(string $nome): ?int {
        $stmt = $this->pdo->prepare("SELECT id FROM user_groups WHERE nome = ?");
        $stmt->execute([$nome]);
        $row = $stmt->fetch();
        return $row ? $row['id'] : null;
    }

    // Verifica accesso al servizio tramite groups
    public function hasAccesso(int $userId, string $servizio): bool {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) FROM users_has_groups uhg
            JOIN services_has_groups shg ON uhg.groups_id = shg.groups_id
            WHERE uhg.users_id = ? AND shg.services_username = ?
        ");
        $stmt->execute([$userId, $servizio]);
        return $stmt->fetchColumn() > 0;
    }
}
?>