<?php
error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
ini_set('display_errors', 0);

session_start();
require_once '../template2.inc.php';
require_once '../php/config/conf.php';
require_once '../php/class/Prenotazione.php';
require_once '../php/class/Recensione.php';
require_once '../php/includes/auth.php';
require_once '../php/class/Coupon.php';


requireRuolo('cliente');

$couponObj = new Coupon();
$msgCoupon = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['genera_coupon'])) {
    $puntiAttuali = $_SESSION['punti'] ?? 0;
    $puntiScelti  = intval($_POST['soglia_scelta'] ?? 0);
    $risultato = $couponObj->generaCoupon(getUserId(), $puntiAttuali, $puntiScelti);
    if ($risultato) {
        $_SESSION['punti'] -= $risultato['punti_scalati'];
        $msgCoupon = "<p class='msg-successo'>🎉 Coupon generato: <strong>" . $risultato['codice'] . "</strong> — {$risultato['sconto']}% di sconto! Hai usato {$risultato['punti_scalati']} punti.</p>";
    } else {
        $msgCoupon = "<p class='msg-errore'>Punti insufficienti o soglia non valida.</p>";
    }
}

$mieiCoupon = $couponObj->getCouponUtente(getUserId());


$prenotazioneObj = new Prenotazione();
$recensioneObj   = new Recensione();

$miePrenotazioni = $prenotazioneObj->getPrenotazioniUtente(getUserId());

// HTML prenotazioni
$htmlPrenotazioni = "";
if (!empty($miePrenotazioni)) {
    foreach ($miePrenotazioni as $p) {
        $stato = match($p['stato']) {
            'confermata'  => "<span class='badge badge-green'>Confermata</span>",
            'annullata'   => "<span class='badge badge-red'>Annullata</span>",
            default       => "<span class='badge badge-yellow'>In attesa</span>",
        };
        $tavolo = $p['numero_tavolo'] ? "Tavolo " . $p['numero_tavolo'] : "Non assegnato";
        $htmlPrenotazioni .= "<div class='card prenotazione-card'>";
        $htmlPrenotazioni .= "<div class='card-body'>";
        $htmlPrenotazioni .= "<div class='card-title-row'>";
        $htmlPrenotazioni .= "<h3 class='card-title'>📅 " . htmlspecialchars($p['data']) . " alle " . htmlspecialchars($p['ora']) . "</h3>";
        $htmlPrenotazioni .= $stato;
        $htmlPrenotazioni .= "</div>";
        $htmlPrenotazioni .= "<p class='card-text'>👥 " . htmlspecialchars($p['persone']) . " persone</p>";
        $htmlPrenotazioni .= "<p class='card-text'>🪑 " . $tavolo . "</p>";
        if ($p['note']) {
            $htmlPrenotazioni .= "<p class='card-text'>📝 " . htmlspecialchars($p['note']) . "</p>";
        }
        $htmlPrenotazioni .= "</div></div>";
    }
} else {
    $htmlPrenotazioni = "<p class='empty-msg'>Non hai ancora effettuato prenotazioni.</p>";
}

$soglie = [500 => '20%', 200 => '10%', 100 => '5%'];
$puntiSessione = $_SESSION['punti'] ?? 0;

$htmlSoglie = "<form method='post'>";
foreach ($soglie as $pt => $sconto) {
    $raggiunto = $puntiSessione >= $pt;
    $disabled  = $raggiunto ? '' : 'disabled';
    $style     = $raggiunto ? 'cursor:pointer;' : 'opacity:.45; cursor:not-allowed;';
    $htmlSoglie .= "
    <label style='display:flex; align-items:center; gap:12px; padding:12px 16px;
                  border:1.5px solid var(--border); border-radius:var(--radius-sm);
                  margin-bottom:8px; {$style}'>
        <input type='radio' name='soglia_scelta' value='{$pt}' {$disabled}>
        <span class='soglia-punti'>{$pt} pt</span>
        <span class='soglia-sconto'>{$sconto} sconto</span>
        " . ($raggiunto ? "<span class='soglia-status'>✓ Disponibile</span>" : "<span class='soglia-status soglia-locked'>🔒</span>") . "
    </label>";
}
$htmlSoglie .= "
    <button type='submit' name='genera_coupon' class='btn-primary' style='margin-top:8px;'
            onclick=\"return confirm('Confermi il riscatto?')\">
        Genera coupon selezionato
    </button>
</form>";

$htmlCoupon = '';
if (!empty($mieiCoupon)) {
    foreach ($mieiCoupon as $c) {
        $usato = $c['usato'] ? 'coupon-usato' : 'coupon-attivo';
        $label = $c['usato'] ? 'Usato' : 'Attivo';
        $htmlCoupon .= "<div class='coupon-card {$usato}'>";
        $htmlCoupon .= "<div class='coupon-codice'>{$c['codice']}</div>";
        $htmlCoupon .= "<div class='coupon-sconto'>{$c['sconto_percentuale']}% OFF</div>";
        $htmlCoupon .= "<div class='coupon-label'>{$label}</div>";
        $htmlCoupon .= "</div>";
    }
} else {
    $htmlCoupon = "<p class='empty-msg'>Nessun coupon generato ancora.</p>";
}

$t = new Template("../templates/utente");
$t->setContent("nome", htmlspecialchars($_SESSION['nome']));
$t->setContent("cognome", htmlspecialchars($_SESSION['cognome']));
$t->setContent("email", htmlspecialchars($_SESSION['email']));
$t->setContent("username", htmlspecialchars($_SESSION['username']));
$t->setContent("punti", htmlspecialchars($_SESSION['punti'] ?? 0));
$t->setContent("prenotazioni", $htmlPrenotazioni);
$t->setContent("msg_coupon", $msgCoupon);
$t->setContent("soglie", $htmlSoglie);
$t->setContent("coupon_list", $htmlCoupon);
$t->setContent("punti_attuali", $puntiSessione);
$t->close();
?>