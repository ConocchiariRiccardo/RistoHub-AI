<?php
require_once __DIR__ . '/../config/conf.php';

class Piatto {
    private $pdo;

    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
    }

    // Tutti i piatti con categoria
    public function getPiatti(): array {
        $stmt = $this->pdo->query("
            SELECT p.*, c.nome AS categoria_nome 
            FROM piatti p
            LEFT JOIN categorie c ON p.categorie_id = c.id
            ORDER BY c.ordine ASC, p.nome ASC
        ");
        return $stmt->fetchAll();
    }

    // Piatti disponibili per il menu pubblico
    public function getMenu(): array {
        $stmt = $this->pdo->query("
            SELECT p.*, c.nome AS categoria_nome 
            FROM piatti p
            LEFT JOIN categorie c ON p.categorie_id = c.id
            WHERE p.disponibile = 1
            ORDER BY c.ordine ASC, p.nome ASC
        ");
        return $stmt->fetchAll();
    }

    // Singolo piatto
    public function getPiatto(int $id): ?array {
        $stmt = $this->pdo->prepare("
            SELECT p.*, c.nome AS categoria_nome 
            FROM piatti p
            LEFT JOIN categorie c ON p.categorie_id = c.id
            WHERE p.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    // Allergeni di un piatto
    public function getAllergeni(int $idPiatto): array {
        $stmt = $this->pdo->prepare("
            SELECT DISTINCT a.nome, a.icona
            FROM allergeni a
            JOIN ingredienti_has_allergeni ia ON a.id = ia.allergeni_id
            JOIN piatti_has_ingredienti pi ON ia.ingredienti_id = pi.ingredienti_id
            WHERE pi.piatti_id = ?
        ");
        $stmt->execute([$idPiatto]);
        return $stmt->fetchAll();
    }

    // Ingredienti di un piatto
    public function getIngredienti(int $idPiatto): array {
        $stmt = $this->pdo->prepare("
            SELECT i.*, pi.quantita
            FROM ingredienti i
            JOIN piatti_has_ingredienti pi ON i.id = pi.ingredienti_id
            WHERE pi.piatti_id = ?
        ");
        $stmt->execute([$idPiatto]);
        return $stmt->fetchAll();
    }

    // Aggiungi piatto
    public function aggiungiPiatto(string $nome, string $descrizione, float $prezzo, int $categorie_id, string $img, int $disponibile = 1): bool {
        $stmt = $this->pdo->prepare("INSERT INTO piatti (nome, descrizione, prezzo, categorie_id, img, disponibile) VALUES (?, ?, ?, ?, ?, ?)");
        return $stmt->execute([$nome, $descrizione, $prezzo, $categorie_id, $img, $disponibile]);
    }

    // Modifica piatto
    public function modificaPiatto(int $id, string $nome, string $descrizione, float $prezzo, int $categorie_id, string $img, int $disponibile): bool {
        $stmt = $this->pdo->prepare("UPDATE piatti SET nome=?, descrizione=?, prezzo=?, categorie_id=?, img=?, disponibile=? WHERE id=?");
        return $stmt->execute([$nome, $descrizione, $prezzo, $categorie_id, $img, $disponibile, $id]);
    }

    // Elimina piatto
    public function eliminaPiatto(int $id): bool {
        $stmt = $this->pdo->prepare("DELETE FROM piatti WHERE id = ?");
        return $stmt->execute([$id]);
    }

    // Tutte le categorie
    public function getCategorie(): array {
        $stmt = $this->pdo->query("SELECT * FROM categorie ORDER BY ordine ASC");
        return $stmt->fetchAll();
    }

    // Aggiungi categoria
    public function aggiungiCategoria(string $nome, string $descrizione, int $ordine): bool {
        $stmt = $this->pdo->prepare("INSERT INTO categorie (nome, descrizione, ordine) VALUES (?, ?, ?)");
        return $stmt->execute([$nome, $descrizione, $ordine]);
    }

    // Elimina categoria
    public function eliminaCategoria(int $id): bool {
        $stmt = $this->pdo->prepare("DELETE FROM categorie WHERE id = ?");
        return $stmt->execute([$id]);
    }

    // Piatti per categoria
    public function getPiattiPerCategoria(): array {
        $menu = $this->getMenu();
        $result = [];
        foreach ($menu as $p) {
            $result[$p['categoria_nome']][] = $p;
        }
        return $result;
    }
}
?>