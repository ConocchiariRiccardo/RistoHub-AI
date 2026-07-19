<?php
require_once __DIR__ . '/../config/conf.php';

class Recensione {
    private $pdo;

    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
    }

    // Tutte le recensioni
    public function getRecensioni(): array {
        $stmt = $this->pdo->query("
            SELECT r.*, u.username, u.nome, u.cognome
            FROM recensioni r
            LEFT JOIN users u ON r.users_id = u.id
            ORDER BY r.created_at DESC
        ");
        return $stmt->fetchAll();
    }

    // Ultime N recensioni (per slider homepage)
    public function getUltimeRecensioni(int $limit = 5): array {
        $stmt = $this->pdo->prepare("
            SELECT r.*, u.username, u.nome
            FROM recensioni r
            LEFT JOIN users u ON r.users_id = u.id
            ORDER BY r.created_at DESC
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }

    // Media voti
    public function getMediaVoti(): float {
        $stmt = $this->pdo->query("SELECT COALESCE(AVG(voto), 0) FROM recensioni");
        return round((float)$stmt->fetchColumn(), 1);
    }

    // Aggiungi recensione
    public function aggiungiRecensione(?int $usersId, int $voto, string $commento): bool {
        if ($voto < 1 || $voto > 5) return false;
        $stmt = $this->pdo->prepare("INSERT INTO recensioni (users_id, voto, commento) VALUES (?, ?, ?)");
        return $stmt->execute([$usersId, $voto, $commento]);
    }

    // Elimina recensione
    public function eliminaRecensione(int $id): bool {
        $stmt = $this->pdo->prepare("DELETE FROM recensioni WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
?>