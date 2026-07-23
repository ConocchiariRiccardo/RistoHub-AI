<?php
require_once __DIR__ . '/../config/conf.php';

class Coupon {
    private $pdo;

    const SOGLIE = [
        500 => 20,
        200 => 10,
        100 => 5,
    ];

    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
    }

    public function getCouponUtente(int $usersId): array {
        $stmt = $this->pdo->prepare("SELECT * FROM coupon WHERE users_id = ? ORDER BY created_at DESC");
        $stmt->execute([$usersId]);
        return $stmt->fetchAll();
    }

    public function generaCoupon(int $usersId, int $punti, int $sogliaScelta = 0): ?array {
        if (!isset(self::SOGLIE[$sogliaScelta]) || $punti < $sogliaScelta) return null;

        $sconto = self::SOGLIE[$sogliaScelta];
        $codice = strtoupper('RH-' . substr(md5(uniqid($usersId, true)), 0, 8));

        $stmt = $this->pdo->prepare("INSERT INTO coupon (users_id, codice, sconto_percentuale) VALUES (?, ?, ?)");
        $stmt->execute([$usersId, $codice, $sconto]);

        $stmtP = $this->pdo->prepare("UPDATE users SET punti_loyalty = punti_loyalty - ? WHERE id = ?");
        $stmtP->execute([$sogliaScelta, $usersId]);

        return ['codice' => $codice, 'sconto' => $sconto, 'punti_scalati' => $sogliaScelta];
    }

    public function usaCoupon(string $codice, int $usersId): bool {
        $stmt = $this->pdo->prepare("UPDATE coupon SET usato = 1 WHERE codice = ? AND users_id = ? AND usato = 0");
        $stmt->execute([$codice, $usersId]);
        return $stmt->rowCount() > 0;
    }

    public function getCouponValido(string $codice): ?array {
        $stmt = $this->pdo->prepare("SELECT * FROM coupon WHERE codice = ? AND usato = 0");
        $stmt->execute([$codice]);
        return $stmt->fetch() ?: null;
    }
}
?>