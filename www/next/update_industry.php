<?php
require_once __DIR__ . '/../../config/config.php';

$referral_code = htmlspecialchars($_POST['ref'] ?? '');
$industry = htmlspecialchars($_POST['industry'] ?? '');

if (!$referral_code || !$industry) {
    header('Location: index.php');
    exit;
}

$db = get_db_connection();

$stmt = $db->prepare("UPDATE signups SET industry = :industry WHERE referral_code = :referral_code");
$stmt->execute([':industry' => $industry, ':referral_code' => $referral_code]);

header('Location: thanks.php?ref=' . $referral_code);
exit;
?>