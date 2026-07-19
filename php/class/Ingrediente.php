<?php
require_once __DIR__ . '/../config/conf.php';

class Ingrediente {
    private $pdo;

    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
    }

    // Tutti gli ingredienti
    public function getIngredienti(): array {
        $stmt = $this->pdo->query("SELECT * FROM ingredienti ORDER BY nome ASC");
        return $stmt->fetchAll();
    }

    // Ingredienti sotto soglia minima
    public function getIngredientSottoSoglia(): array {
        $stmt = $this->pdo->query("
            SELECT * FROM ingredienti 
            WHERE quantita_magazzino <= soglia_minima
            ORDER BY nome ASC
        ");
        return $stmt->fetchAll();
    }

    // Singolo ingrediente
    public function getIngrediente(int $id): ?array {
        $stmt = $this->pdo->prepare("SELECT * FROM ingredienti WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    // Aggiungi ingrediente
    public function aggiungiIngrediente(string $nome, float $quantita, string $unitaMisura, float $sogliaMinima): bool {
        $stmt = $this->pdo->prepare("INSERT INTO ingredienti (nome, quantita_magazzino, unita_misura, soglia_minima) VALUES (?, ?, ?, ?)");
        return $stmt->execute([$nome, $quantita, $unitaMisura, $sogliaMinima]);
    }

    // Modifica ingrediente
    public function modificaIngrediente(int $id, string $nome, float $quantita, string $unitaMisura, float $sogliaMinima): bool {
        $stmt = $this->pdo->prepare("UPDATE ingredienti SET nome=?, quantita_magazzino=?, unita_misura=?, soglia_minima=? WHERE id=?");
        return $stmt->execute([$nome, $quantita, $unitaMisura, $sogliaMinima, $id]);
    }

    // Aggiorna quantità magazzino
    public function aggiornaQuantita(int $id, float $quantita): bool {
        $stmt = $this->pdo->prepare("UPDATE ingredienti SET quantita_magazzino = ? WHERE id = ?");
        return $stmt->execute([$quantita, $id]);
    }

    // Elimina ingrediente
    public function eliminaIngrediente(int $id): bool {
        $stmt = $this->pdo->prepare("DELETE FROM ingredienti WHERE id = ?");
        return $stmt->execute([$id]);
    }

    // Allergeni di un ingrediente
    public function getAllergeni(int $id): array {
        $stmt = $this->pdo->prepare("
            SELECT a.* FROM allergeni a
            JOIN ingredienti_has_allergeni ia ON a.id = ia.allergeni_id
            WHERE ia.ingredienti_id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetchAll();
    }

    // Tutti gli allergeni
    public function getAllAllergeni(): array {
        $stmt = $this->pdo->query("SELECT * FROM allergeni ORDER BY nome ASC");
        return $stmt->fetchAll();
    }

    // Aggiungi allergene
    public function aggiungiAllergene(string $nome, string $icona = ''): bool {
        $stmt = $this->pdo->prepare("INSERT INTO allergeni (nome, icona) VALUES (?, ?)");
        return $stmt->execute([$nome, $icona]);
    }

    // Elimina allergene
    public function eliminaAllergene(int $id): bool {
        $stmt = $this->pdo->prepare("DELETE FROM allergeni WHERE id = ?");
        return $stmt->execute([$id]);
    }

    // Associa allergene a ingrediente
    public function associaAllergene(int $ingredienteId, int $allergeneId): bool {
        $stmt = $this->pdo->prepare("INSERT IGNORE INTO ingredienti_has_allergeni (ingredienti_id, allergeni_id) VALUES (?, ?)");
        return $stmt->execute([$ingredienteId, $allergeneId]);
    }
}
?>