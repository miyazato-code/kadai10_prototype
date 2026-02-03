<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// 【修正1】パスを絶対パス指定にして読み込みミスを防ぐ
require_once __DIR__ . '/../../config/config.php';

$referral_code = htmlspecialchars($_GET['ref'] ?? '');

if (!$referral_code) {
    header('Location: index.php');
    exit;
}

try {
    $db = get_db_connection();
} catch (Exception $e) {
    echo "Database error: " . $e->getMessage();
    exit;
}

$stmt = $db->prepare("SELECT id, email, created_at, industry FROM signups WHERE referral_code = :referral_code");
$stmt->execute([':referral_code' => $referral_code]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    header('Location: index.php');
    exit;
}

if (empty($user['industry'])) {
    ?>
    <!DOCTYPE html>
    <html lang="ja">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Quick Survey | Next-Gen Marketing Project</title>
        <script src="https://cdn.tailwindcss.com"></script>
        <style>
            body { font-family: 'Inter', sans-serif; background: #050505; color: #fff; }
        </style>
    </head>
    <body class="antialiased flex items-center justify-center min-h-screen">
        <div class="max-w-md mx-auto p-8 bg-white/5 border border-white/10 rounded-2xl">
            <h1 class="text-2xl font-bold mb-4 text-center">どの業界ですか？</h1>
            <form action="update_industry.php" method="POST">
                <input type="hidden" name="ref" value="<?php echo $referral_code; ?>">
                <select name="industry" class="w-full bg-white/5 border border-white/10 p-4 rounded-xl mb-4 text-white" required>
                    <option value="" class="text-black">選択してください</option>
                    <optgroup label="製造業" class="text-black">
                        <option value="food" class="text-black">食料品（飲料、タバコ、飼料を含む）</option>
                        <option value="textile" class="text-black">繊維</option>
                        <option value="wood_paper" class="text-black">木材・紙パルプ（家具を含む）</option>
                        <option value="chemical" class="text-black">化学</option>
                        <option value="petroleum_coal" class="text-black">石油・石炭製品</option>
                        <option value="ceramics_stone" class="text-black">窯業・土石製品（ガラス、セメント等）</option>
                        <option value="steel" class="text-black">鉄鋼</option>
                        <option value="non_ferrous" class="text-black">非鉄金属</option>
                        <option value="metal_products" class="text-black">金属製品</option>
                        <option value="machinery" class="text-black">機械（はん用・生産用・業務用、電気機械、輸送用、精密機械などを含む）</option>
                    </optgroup>
                    <optgroup label="非製造業" class="text-black">
                        <option value="construction" class="text-black">建設</option>
                        <option value="real_estate" class="text-black">不動産</option>
                        <option value="rental" class="text-black">物品賃貸</option>
                        <option value="wholesale" class="text-black">卸売</option>
                        <option value="retail" class="text-black">小売</option>
                        <option value="transport_post" class="text-black">運輸・郵便</option>
                        <option value="information_communication" class="text-black">情報通信</option>
                        <option value="electricity_gas" class="text-black">電気・ガス</option>
                        <option value="business_services" class="text-black">対事業所サービス（広告、情報サービス、廃棄物処理等）</option>
                        <option value="personal_services" class="text-black">対個人サービス（宿泊、飲食、娯楽等）</option>
                        <option value="other_non_manufacturing" class="text-black">その他非製造業（農林水産、鉱業等を含む）</option>
                    </optgroup>
                </select>
                <button type="submit" class="w-full bg-blue-600 text-white font-bold py-4 rounded-xl hover:bg-blue-700">送信</button>
            </form>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// 統計データの取得
$total_signups = $db->query("SELECT COUNT(id) FROM signups")->fetchColumn();
$ref_stmt = $db->prepare("SELECT COUNT(*) FROM referrals WHERE referrer_id = ?");
$ref_stmt->execute([$user['id']]);
$referral_count = $ref_stmt->fetchColumn();

// ランク計算
$rank_query = $db->prepare("
    SELECT COUNT(*) + 1 as `user_rank` FROM signups s 
    LEFT JOIN (SELECT referrer_id, COUNT(*) as `ref_count` FROM referrals GROUP BY referrer_id) r ON s.id = r.referrer_id 
    WHERE (COALESCE(r.`ref_count`, 0) > ? OR (COALESCE(r.`ref_count`, 0) = ? AND s.created_at < ?))
");
$rank_query->execute([$referral_count, $referral_count, $user['created_at']]);
$user_rank_data = $rank_query->fetch(PDO::FETCH_ASSOC);
$user_rank = $user_rank_data ? $user_rank_data['user_rank'] : 0;

$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];
// パス計算の修正
$current_dir = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
$referral_link = "{$protocol}://{$host}{$current_dir}/index.php?ref={$referral_code}";
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thank You! | Next-Gen Marketing Project</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Inter', sans-serif; background: #050505; color: #fff; }
        .gradient-text { background: linear-gradient(90deg, #fff, #888); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .highlight-box { background: rgba(56, 189, 248, 0.1); border-radius: 16px; }
    </style>
</head>
<body class="antialiased">
    <section class="max-w-4xl mx-auto pt-24 pb-20 px-6 text-center">
        <h1 class="text-6xl md:text-7xl font-black mb-6 tracking-tighter gradient-text">You're on the list!</h1>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-center mb-12">
            <div class="highlight-box p-6">
                <div class="text-gray-400 text-sm font-bold uppercase tracking-wider">Your Rank</div>
                <div class="text-4xl font-black text-white">#<?php echo $user_rank; ?></div>
            </div>
            <div class="highlight-box p-6">
                <div class="text-gray-400 text-sm font-bold uppercase tracking-wider">Total Users</div>
                <div class="text-4xl font-black text-white"><?php echo $total_signups; ?></div>
            </div>
            <div class="highlight-box p-6">
                <div class="text-gray-400 text-sm font-bold uppercase tracking-wider">Your Referrals</div>
                <div class="text-4xl font-black text-white"><?php echo $referral_count; ?></div>
            </div>
        </div>
        <div class="p-8 rounded-2xl bg-white/5 border border-white/10">
            <h3 class="text-2xl font-bold mb-4 text-blue-400">Move up the list!</h3>
            <div class="flex items-center gap-4">
                <input type="text" id="referral-link" value="<?php echo $referral_link; ?>" class="flex-1 bg-white/5 border border-white/10 p-4 rounded-xl outline-none" readonly>
                <button onclick="copyLink()" class="bg-blue-600 text-white font-bold px-8 py-4 rounded-xl hover:bg-blue-700">Copy</button>
            </div>
        </div>
        <div class="mt-12">
            <a href="index.php" class="inline-flex items-center text-gray-400 hover:text-white transition-colors">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to Home
            </a>
        </div>
    </section>
        <script>
            async function copyLink() {
                const el = document.getElementById('referral-link');
                try {
                    await navigator.clipboard.writeText(el.value);
                    alert("Copied!");
                } catch (err) {
                    console.error('Failed to copy: ', err);
                }
            }
        </script>
</body>
</html>