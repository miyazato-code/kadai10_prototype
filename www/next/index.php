<?php
// A/B Test setup based on User IP
$user_ip = $_SERVER['REMOTE_ADDR'];
$visitor_id = md5($user_ip . $_SERVER['HTTP_USER_AGENT']); // More unique visitor ID

$variations = [
    'decision_support' => [
        'title' => "意思決定の質を高める、<br>次世代の分析基盤。",
        'subtitle' => "分断されたマーケティングデータを統合し、LTV（顧客生涯価値）の最大化へ。再現性のある成長を、すべてのチームに。",
    ],
    'roas_maximization' => [
        'title' => "ROASを最大化する、<br>自動広告最適化エンジン。",
        'subtitle' => "AIがリアルタイムに予算配分を最適化。無駄な広告費を削減し、コンバージョンを加速させます。",
    ]
];

$variant_keys = array_keys($variations);
$chosen_variant_key = $variant_keys[hexdec(substr(md5($user_ip), 0, 8)) % count($variant_keys)];
$chosen_variation = $variations[$chosen_variant_key];
$referred_by = htmlspecialchars($_GET['ref'] ?? '');


?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Next-Gen Marketing Project | Alpha</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', 'YuGothic', sans-serif; background: #050505; color: #fff; }
        .bento-card { background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 24px; transition: all 0.3s ease; }
        .bento-card:hover { border-color: rgba(255, 255, 255, 0.3); background: rgba(255, 255, 255, 0.05); }
        .gradient-text { background: linear-gradient(90deg, #fff, #888); -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent; }
    </style>
</head>
<body class="antialiased">

    <section class="max-w-6xl mx-auto pt-32 pb-20 px-6 text-center">
        <div class="inline-block px-4 py-1.5 mb-6 text-xs font-bold tracking-widest text-blue-400 border border-blue-400/30 rounded-full bg-blue-400/10 uppercase">
            Stealth Project 2026
        </div>
        <h1 id="hero-title" class="text-6xl md:text-8xl font-black mb-8 tracking-tighter gradient-text">
            <?php echo $chosen_variation['title']; ?>
        </h1>
        <p id="hero-subtitle" class="text-xl text-gray-400 max-w-2xl mx-auto leading-relaxed">
            <?php echo $chosen_variation['subtitle']; ?>
        </p>
    </section>

    <section class="max-w-6xl mx-auto px-6 grid grid-cols-1 md:grid-cols-3 gap-6 mb-32">
        <div class="bento-card p-8 md:col-span-2" onclick="trackClick('feature_data_integrity')">
            <h3 class="text-2xl font-bold mb-4">01. Data Integrity</h3>
            <p class="text-gray-500">分断されたチャネル情報を単一の真実（Source of Truth）へ。LTV計測の不確実性を排除します。</p>
        </div>
        <div class="bento-card p-8" onclick="trackClick('feature_velocity')">
            <h3 class="text-2xl font-bold mb-4">02. Velocity</h3>
            <p class="text-gray-500">分析に費やす時間を、実行の回数へと転換する。</p>
        </div>
        <div class="bento-card p-8" onclick="trackClick('feature_logic')">
            <h3 class="text-2xl font-bold mb-4">03. Logic</h3>
            <p class="text-gray-500">直感ではなく、第1原理に基づいた戦略構築。</p>
        </div>
        <div class="bento-card p-8 md:col-span-2 bg-blue-600/10 border-blue-500/20">
            <h3 class="text-2xl font-bold mb-4 text-blue-400">Join the Waitlist</h3>
            <form id="waitlist-form" action="submit.php" method="POST" class="flex flex-col md:flex-row gap-4">
                <input type="email" name="email" placeholder="Email Address" class="flex-1 bg-white/5 border border-white/10 p-4 rounded-xl outline-none focus:border-blue-500 transition-all" required>
                <input type="hidden" name="variant" id="variant-input" value="<?php echo $chosen_variant_key; ?>">
                <input type="hidden" name="referred_by" value="<?php echo $referred_by; ?>">
                <button type="submit" class="bg-white text-black font-bold px-8 py-4 rounded-xl hover:bg-gray-200 transition-all" onclick="trackClick('signup_button')">Access Beta</button>
            </form>
        </div>
    </section>

    <script>
        const visitorId = '<?php echo $visitor_id; ?>';
        const variant = '<?php echo $chosen_variant_key; ?>';
        let startTime = Date.now();

        window.onbeforeunload = function() {
            let duration = Math.round((Date.now() - startTime) / 1000); // in seconds
            const data = {
                visitor_id: visitorId,
                event_type: 'stay',
                target_feature: 'page',
                value: duration,
                variant: variant
            };
            navigator.sendBeacon('tracker.php', JSON.stringify(data));
        };

        function trackClick(featureName) {
            const data = {
                visitor_id: visitorId,
                event_type: 'click',
                target_feature: featureName,
                variant: variant
            };
            fetch('tracker.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            }).catch(error => console.error('Tracking Error:', error));
        }
    </script>

</body>
</html>
