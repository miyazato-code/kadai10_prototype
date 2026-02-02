<?php
// project/www/next/submit.php


// パスを project/CONFIG/config.php に合わせる
require_once __DIR__ . '/../../CONFIG/config.php';

$db = get_db_connection();

// 入力データの取得とバリデーション
$email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
$variant = htmlspecialchars($_POST['variant'] ?? 'default');
$referred_by_code = htmlspecialchars($_POST['referred_by'] ?? '');

if (!$email) {
    die('Invalid email address.');
}

// 1. 重複チェック
$stmt = $db->prepare("SELECT referral_code FROM signups WHERE email = :email");
$stmt->execute([':email' => $email]);
$existing_user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($existing_user) {
    header('Location: thanks.php?ref=' . $existing_user['referral_code']);
    exit;
}

// 2. ビジネスドメイン判定
$personal_isps = ['gmail.com', 'yahoo.co.jp', 'outlook.com', 'icloud.com'];
$domain = substr(strrchr($email, "@"), 1);
$is_business = in_array($domain, $personal_isps) ? 0 : 1;

// 3. ユニークな紹介コード生成
$referral_code = bin2hex(random_bytes(5));

// 4. MySQLへの登録実行
try {
    $stmt = $db->prepare(
        "INSERT INTO signups (email, variant, is_business, referral_code, referred_by) 
         VALUES (:email, :variant, :is_business, :referral_code, :referred_by)"
    );
    $stmt->execute([
        ':email' => $email,
        ':variant' => $variant,
        ':is_business' => $is_business,
        ':referral_code' => $referral_code,
        ':referred_by' => $referred_by_code
    ]);
    $new_user_id = $db->lastInsertId();

    // 紹介元（リファラル）の記録
    if ($referred_by_code) {
        $stmt = $db->prepare("SELECT id FROM signups WHERE referral_code = :code");
        $stmt->execute([':code' => $referred_by_code]);
        $referrer = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($referrer && $new_user_id) {
            $ref_stmt = $db->prepare("INSERT INTO referrals (referrer_id, new_user_id) VALUES (?, ?)");
            $ref_stmt->execute([$referrer['id'], $new_user_id]);
        }
    }
} catch (Exception $e) {
    die("Database Error: " . $e->getMessage());
}

// 5. メール送信（失敗してもリダイレクトを優先させるため @ を使用）
$subject = 'Welcome to the Waitlist';
$message = "Thank you for joining. Your referral link: https://{$_SERVER['HTTP_HOST']}/index.php?ref=$referral_code";
$headers = 'From: noreply@yourdomain.com';

// エラーを抑制し、処理を続行
@mb_send_mail($email, $subject, $message, $headers);

// 6. 確実にサンクスページへ
header('Location: thanks.php?ref=' . $referral_code);
exit;