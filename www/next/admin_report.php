<?php
require_once __DIR__ . '/../../config/config.php';

$db = get_db_connection();

// 1. バリアント別CVR (Interactions / Signups)
$report = $db->query("
    SELECT 
        variant, 
        COUNT(*) as total_signups,
        SUM(is_business) as business_leads
    FROM signups 
    GROUP BY variant
")->fetchAll(PDO::FETCH_ASSOC);

// 2. バイラル係数 K-Factor の近似値
$k_factor = $db->query("
    SELECT COUNT(r.id) / COUNT(s.id) 
    FROM signups s LEFT JOIN referrals r ON s.id = r.referrer_id
")->fetchColumn();

// 3. Industry analysis
$industry_report = $db->query("
    SELECT 
        industry, 
        COUNT(*) as count
    FROM signups 
    WHERE industry IS NOT NULL AND industry != ''
    GROUP BY industry
    ORDER BY count DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>
<div class="p-6 bg-black text-white">
    <h1 class="text-2xl font-bold">Market Validation Report</h1>
    <p>Viral Factor K: <?= round($k_factor, 2) ?></p>
    <table>
        <?php foreach($report as $row): ?>
            <tr>
                <td><?= $row['variant'] ?></td>
                <td>Signups: <?= $row['total_signups'] ?></td>
                <td>B2B Rate: <?= round(($row['business_leads']/$row['total_signups'])*100, 1) ?>%</td>
            </tr>
        <?php endforeach; ?>
    </table>
    <h2 class="text-xl font-bold mt-6">Industry Insights</h2>
    <table>
        <?php foreach($industry_report as $row): ?>
            <tr>
                <td><?= $row['industry'] ?></td>
                <td>Count: <?= $row['count'] ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>