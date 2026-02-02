<?php
// 1. データベース接続
$db = new PDO('sqlite:leads.db');

// 2. 紹介IDの取得
$referral_id = $_GET['ref'] ?? '';

if ($referral_id) {
    // 3. 紹介者の referral_count をインクリメント
    $stmt = $db->prepare("UPDATE leads SET referral_count = referral_count + 1 WHERE referral_id = ?");
    $stmt->execute([$referral_id]);
}

// 4. ランディングページへリダイレクト
header('Location: index.html');
exit;
?>