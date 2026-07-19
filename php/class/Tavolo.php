<?php
require_once __DIR__ . '/../config/conf.php';

class Tavolo {
    private $pdo;

    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
    }

    // Tutti i tavoli
    public function getTavoli(): array {
        $stmt = $this->pdo->query("SELECT * FROM tavoli ORDER BY numero ASC");
        return $stmt->fetchAll();
    }

    // Singolo tavolo
    public function getTavolo(int $id): ?array {
        $stmt = $this->pdo->prepare("SELECT * FROM tavoli WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    // Tavoli liberi con capacità sufficiente
    public function getTavoliLiberi(int $persone = 1): array {
        $stmt = $this->pdo->prepare("SELECT * FROM tavoli WHERE stato = 'libero' AND capacita_max >= ? ORDER BY capacita_max ASC");
        $stmt->execute([$persone]);
        return $stmt->fetchAll();
    }

    // Tavoli disponibili per data e ora (esclude quelli già prenotati)
    public function getTavoliDisponibili(int $persone, string $data, string $ora): array {
        $stmt = $this->pdo->prepare("
            SELECT t.* FROM tavoli t
            WHERE t.capacita_max >= ?
            AND t.id NOT IN (
                SELECT p.tavoli_id FROM prenotazioni p
                WHERE p.data = ?
                AND p.ora = ?
                AND p.tavoli_id IS NOT NULL
                AND p.stato != 'annullata'
            )
            ORDER BY t.capacita_max ASC
        ");
        $stmt->execute([$persone, $data, $ora]);
        return $stmt->fetchAll();
    }

    // Aggiorna stato tavolo
    public function setStato(int $id, string $stato): bool {
        $stmt = $this->pdo->prepare("UPDATE tavoli SET stato = ? WHERE id = ?");
        return $stmt->execute([$stato, $id]);
    }

    // Aggiungi tavolo
    public function aggiungiTavolo(int $numero, int $capacita_max, string $posizione = '', string $stato = 'libero'): bool {
        $stmt = $this->pdo->prepare("INSERT INTO tavoli (numero, capacita_max, posizione, stato) VALUES (?, ?, ?, ?)");
        return $stmt->execute([$numero, $capacita_max, $posizione, $stato]);
    }

    // Modifica tavolo
    public function modificaTavolo(int $id, int $numero, int $capacita_max, string $posizione, string $stato): bool {
        $stmt = $this->pdo->prepare("UPDATE tavoli SET numero=?, capacita_max=?, posizione=?, stato=? WHERE id=?");
        return $stmt->execute([$numero, $capacita_max, $posizione, $stato, $id]);
    }

    // Elimina tavolo
    public function eliminaTavolo(int $id): bool {
        $stmt = $this->pdo->prepare("DELETE FROM tavoli WHERE id = ?");
        return $stmt->execute([$id]);
    }

    // Statistiche tavoli
    public function getStatistiche(): array {
        $stmt = $this->pdo->query("
            SELECT 
                COUNT(*) as totale,
                SUM(stato = 'libero') as liberi,
                SUM(stato = 'occupato') as occupati,
                SUM(stato = 'prenotato') as prenotati
            FROM tavoli
        ");
        return $stmt->fetch();
    }
}

?>