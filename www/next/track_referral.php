<?php
$db = get_db_connection();
$referral_code = $_GET['ref'] ?? '';

if ($referral_code) {
    // 1. まずその紹介コードが存在するか確認
    $stmt = $db->prepare("SELECT id FROM signups WHERE referral_code = ?");
    $stmt->execute([$referral_code]);
    $referrer = $stmt->fetch();

    if ($referrer) {
        // index.php へのリダイレクト時に ref を引き継ぐことで、
        // signup 時の referred_by 登録を確実にする
        header('Location: index.php?ref=' . urlencode($referral_code));
        exit;
    }
}

header('Location: index.php');
exit;