<?php
session_start();
require_once '../php/config/conf.php';
require_once '../php/class/Coupon.php';
require_once '../php/includes/auth.php';

header('Content-Type: application/json');

if (!isLoggato() || getRuolo() !== 'cameriere') {
    echo json_encode(['valido' => false]);
    exit;
}

$codice = strtoupper(trim($_GET['codice'] ?? ''));
if (!$codice) { echo json_encode(['valido' => false]); exit; }

$couponObj = new Coupon();
$coupon = $couponObj->getCouponValido($codice);

if ($coupon) {
    echo json_encode(['valido' => true, 'sconto' => $coupon['sconto_percentuale'], 'id' => $coupon['id']]);
} else {
    echo json_encode(['valido' => false]);
}
?>