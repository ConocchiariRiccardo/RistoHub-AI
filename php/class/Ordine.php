<?php
require_once __DIR__ . '/../config/conf.php';

class Ordine {
    private $pdo;

    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
    }

    // Crea nuovo ordine
    public function creaOrdine(int $tavoliId, array $piatti, ?string $note = null): int {
        if (!isset($_SESSION['user_id'])) return 0;

        $this->pdo->beginTransaction();
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO ordini (tavoli_id, users_id, stato, note) 
                VALUES (?, ?, 'inviato', ?)
            ");
            $stmt->execute([$tavoliId, $_SESSION['user_id'], $note]);
            $idOrdine = (int)$this->pdo->lastInsertId();

            foreach ($piatti as $p) {
                $idPiatto = intval($p['id']);
                $qty = intval($p['qty']);

                // Prendi il prezzo dal db per sicurezza
                $stmtP = $this->pdo->prepare("SELECT prezzo FROM piatti WHERE id = ?");
                $stmtP->execute([$idPiatto]);
                $piatto = $stmtP->fetch();
                if (!$piatto) continue;

                $stmtI = $this->pdo->prepare("
                    INSERT INTO ordini_has_piatti (ordini_id, piatti_id, quantita, prezzo_unitario) 
                    VALUES (?, ?, ?, ?)
                ");
                $stmtI->execute([$idOrdine, $idPiatto, $qty, $piatto['prezzo']]);
            }

            // Calcola e aggiorna totale
            $this->aggiornaTotale($idOrdine);
            $this->pdo->commit();
            return $idOrdine;

        } catch (Exception $e) {
            $this->pdo->rollBack();
            return 0;
        }
    }

    // Aggiorna totale ordine
    private function aggiornaTotale(int $idOrdine): void {
        $stmt = $this->pdo->prepare("
            UPDATE ordini SET totale = (
                SELECT SUM(quantita * prezzo_unitario) 
                FROM ordini_has_piatti 
                WHERE ordini_id = ?
            ) WHERE id = ?
        ");
        $stmt->execute([$idOrdine, $idOrdine]);
    }

    // Ottieni ordine da tavolo (attivo)
    public function getOrdineAttivoByTavolo(int $tavoliId): ?array {
        $stmt = $this->pdo->prepare("
            SELECT * FROM ordini 
            WHERE tavoli_id = ? AND stato != 'completato' 
            ORDER BY created_at DESC LIMIT 1
        ");
        $stmt->execute([$tavoliId]);
        return $stmt->fetch() ?: null;
    }

    // Piatti di un ordine
    public function getPiattiOrdine(int $idOrdine): array {
        $stmt = $this->pdo->prepare("
            SELECT p.id, p.nome, p.img, op.quantita, op.prezzo_unitario,
                   (op.quantita * op.prezzo_unitario) AS subtotale
            FROM ordini_has_piatti op
            JOIN piatti p ON op.piatti_id = p.id
            WHERE op.ordini_id = ?
        ");
        $stmt->execute([$idOrdine]);
        return $stmt->fetchAll();
    }

    // Tutti gli ordini attivi
    public function getOrdiniAttivi(): array {
        $stmt = $this->pdo->query("
            SELECT o.*, t.numero AS numero_tavolo
            FROM ordini o
            JOIN tavoli t ON o.tavoli_id = t.id
            WHERE o.stato != 'completato'
            ORDER BY o.created_at ASC
        ");
        return $stmt->fetchAll();
    }

    // Ordini pronti da consegnare
    public function getOrdiniPronti(): array {
        $stmt = $this->pdo->query("
            SELECT o.*, t.numero AS numero_tavolo
            FROM ordini o
            JOIN tavoli t ON o.tavoli_id = t.id
            WHERE o.stato = 'pronto'
            ORDER BY o.created_at ASC
        ");
        return $stmt->fetchAll();
    }

    // Tutti gli ordini (admin)
    public function getTuttiOrdini(): array {
        $stmt = $this->pdo->query("
            SELECT o.*, t.numero AS numero_tavolo,
                   u.nome AS nome_cameriere
            FROM ordini o
            JOIN tavoli t ON o.tavoli_id = t.id
            LEFT JOIN users u ON o.users_id = u.id
            ORDER BY o.created_at DESC
        ");
        return $stmt->fetchAll();
    }

    // Cambia stato ordine
    public function cambiaStato(int $id, string $stato): bool {
        $stmt = $this->pdo->prepare("UPDATE ordini SET stato = ? WHERE id = ?");
        return $stmt->execute([$stato, $id]);
    }

    // Chiudi ordine e aggiungi punti loyalty
    public function chiudiOrdine(int $id, float $totale): bool {
        $stmt = $this->pdo->prepare("
            UPDATE ordini SET stato = 'completato', totale = ? WHERE id = ?
        ");
        $ok = $stmt->execute([$totale, $id]);

        if ($ok) {
            // Aggiungi punti loyalty (1 punto ogni euro speso)
            $stmtO = $this->pdo->prepare("SELECT users_id FROM ordini WHERE id = ?");
            $stmtO->execute([$id]);
            $ordine = $stmtO->fetch();
            if ($ordine && $ordine['users_id']) {
                $punti = (int)floor($totale);
                $stmtP = $this->pdo->prepare("UPDATE users SET punti_loyalty = punti_loyalty + ? WHERE id = ?");
                $stmtP->execute([$punti, $ordine['users_id']]);
            }
        }
        return $ok;
    }

    // Statistiche incassi
    public function getIncassoOggi(): float {
        $stmt = $this->pdo->query("
            SELECT COALESCE(SUM(totale), 0) FROM ordini 
            WHERE stato = 'completato' AND DATE(created_at) = CURDATE()
        ");
        return (float)$stmt->fetchColumn();
    }

    public function getIncassoMese(): float {
        $stmt = $this->pdo->query("
            SELECT COALESCE(SUM(totale), 0) FROM ordini 
            WHERE stato = 'completato' 
            AND MONTH(created_at) = MONTH(CURDATE())
            AND YEAR(created_at) = YEAR(CURDATE())
        ");
        return (float)$stmt->fetchColumn();
    }

    public function getIncassoTotale(): float {
        $stmt = $this->pdo->query("
            SELECT COALESCE(SUM(totale), 0) FROM ordini WHERE stato = 'completato'
        ");
        return (float)$stmt->fetchColumn();
    }

    public function getTopPiatti(int $limit = 5): array {
        $stmt = $this->pdo->prepare("
            SELECT p.nome, SUM(op.quantita) AS totale_ordinato
            FROM ordini_has_piatti op
            JOIN piatti p ON op.piatti_id = p.id
            JOIN ordini o ON op.ordini_id = o.id
            WHERE o.stato = 'completato'
            GROUP BY p.id, p.nome
            ORDER BY totale_ordinato DESC
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }
}
?>