<?php
require_once __DIR__ . '/../config/conf.php';

class Prenotazione {
    private $pdo;

    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
    }

    // Tutte le prenotazioni
    public function getPrenotazioni(): array {
        $stmt = $this->pdo->query("
            SELECT p.*, t.numero AS numero_tavolo,
                   u.nome AS nome_utente, u.cognome AS cognome_utente
            FROM prenotazioni p
            LEFT JOIN tavoli t ON p.tavoli_id = t.id
            LEFT JOIN users u ON p.users_id = u.id
            ORDER BY p.data DESC, p.ora ASC
        ");
        return $stmt->fetchAll();
    }

    // Singola prenotazione
    public function getPrenotazione(int $id): ?array {
        $stmt = $this->pdo->prepare("
            SELECT p.*, t.numero AS numero_tavolo
            FROM prenotazioni p
            LEFT JOIN tavoli t ON p.tavoli_id = t.id
            WHERE p.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    // Prenotazioni di un utente
    public function getPrenotazioniUtente(int $usersId): array {
        $stmt = $this->pdo->prepare("
            SELECT p.*, t.numero AS numero_tavolo
            FROM prenotazioni p
            LEFT JOIN tavoli t ON p.tavoli_id = t.id
            WHERE p.users_id = ?
            ORDER BY p.data DESC, p.ora ASC
        ");
        $stmt->execute([$usersId]);
        return $stmt->fetchAll();
    }

    // Aggiungi prenotazione
    public function aggiungiPrenotazione(
        string $nome,
        string $telefono,
        string $data,
        string $ora,
        int $persone,
        string $note = '',
        ?int $usersId = null,
        ?int $tavoliId = null
    ): bool {
        $stmt = $this->pdo->prepare("
            INSERT INTO prenotazioni (nome, telefono, data, ora, persone, note, users_id, tavoli_id, stato)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'in_attesa')
        ");
        $ok = $stmt->execute([$nome, $telefono, $data, $ora, $persone, $note, $usersId, $tavoliId]);
        if ($ok && $tavoliId) {
            $stmtT = $this->pdo->prepare("UPDATE tavoli SET stato = 'prenotato' WHERE id = ?");
            $stmtT->execute([$tavoliId]);
        }
        return $ok;
    }

    // Modifica prenotazione
    public function modificaPrenotazione(
        int $id,
        string $nome,
        string $telefono,
        string $data,
        string $ora,
        int $persone,
        string $note = '',
        ?int $tavoliId = null
    ): bool {
        $stmt = $this->pdo->prepare("
            UPDATE prenotazioni 
            SET nome=?, telefono=?, data=?, ora=?, persone=?, note=?, tavoli_id=?
            WHERE id=?
        ");
        return $stmt->execute([$nome, $telefono, $data, $ora, $persone, $note, $tavoliId, $id]);
    }

    // Elimina prenotazione
    public function eliminaPrenotazione(int $id): bool {
        // Libera il tavolo prima di eliminare
        $pren = $this->getPrenotazione($id);
        if ($pren && $pren['tavoli_id']) {
            $stmt = $this->pdo->prepare("UPDATE tavoli SET stato = 'libero' WHERE id = ?");
            $stmt->execute([$pren['tavoli_id']]);
        }
        $stmt = $this->pdo->prepare("DELETE FROM prenotazioni WHERE id = ?");
        return $stmt->execute([$id]);
    }

    // Assegna tavolo a prenotazione
    public function assegnaTavolo(int $id, int $tavoliId): bool {
        $stmt = $this->pdo->prepare("UPDATE prenotazioni SET tavoli_id = ?, stato = 'confermata' WHERE id = ?");
        $ok = $stmt->execute([$tavoliId, $id]);
        if ($ok) {
            $stmtT = $this->pdo->prepare("UPDATE tavoli SET stato = 'prenotato' WHERE id = ?");
            $stmtT->execute([$tavoliId]);
        }
        return $ok;
    }

    // Cambia stato prenotazione
    public function cambiaStato(int $id, string $stato): bool {
        $stmt = $this->pdo->prepare("UPDATE prenotazioni SET stato = ? WHERE id = ?");
        return $stmt->execute([$stato, $id]);
    }

    // Tavoli disponibili per data, ora e persone
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

    // Prenotazioni di oggi
    public function getPrenotazioniOggi(): array {
        $stmt = $this->pdo->query("
            SELECT p.*, t.numero AS numero_tavolo
            FROM prenotazioni p
            LEFT JOIN tavoli t ON p.tavoli_id = t.id
            WHERE p.data = CURDATE()
            ORDER BY p.ora ASC
        ");
        return $stmt->fetchAll();
    }
}
?>