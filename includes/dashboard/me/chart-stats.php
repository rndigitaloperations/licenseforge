<?php
header('Content-Type: application/javascript');
include $_SERVER['DOCUMENT_ROOT'] . '/includes/sessioncheck.php';
$config = include $_SERVER['DOCUMENT_ROOT'] . '/config.php';
require $_SERVER['DOCUMENT_ROOT'] . '/vendor/db.php';

$userId = $_SESSION['user_id'] ?? null;

$stmt = $pdo->prepare("SELECT 
    COUNT(*) as total, 
    SUM(status = 'valid') as valid, 
    SUM(status = 'invalid') as invalid, 
    SUM(status = 'suspended') as suspended 
    FROM licenses WHERE user_id = ?");
$stmt->execute([$userId]);
$stats = $stmt->fetch(PDO::FETCH_ASSOC);

$query = "
    SELECT 
        DATE(created_at) AS day,
        status,
        COUNT(*) AS count
    FROM licenses
    WHERE user_id = ?
    AND created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
    GROUP BY day, status
    ORDER BY day ASC
";
$stmt2 = $pdo->prepare($query);
$stmt2->execute([$userId]);
$rows = $stmt2->fetchAll(PDO::FETCH_ASSOC);

$dates = [];
$statusData = [
    'valid' => [],
    'invalid' => [],
    'suspended' => []
];

for ($i = 29; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $dates[] = $date;
    foreach ($statusData as $status => &$arr) {
        $arr[$date] = 0;
    }
}
unset($arr);

foreach ($rows as $row) {
    $status = $row['status'];
    $day = $row['day'];
    $count = (int)$row['count'];
    if (isset($statusData[$status][$day])) {
        $statusData[$status][$day] = $count;
    }
}
?>

const ctx = document.getElementById('licensesChart').getContext('2d');
const data = {
    labels: <?= json_encode($dates) ?>,
    datasets: [
        {
            label: 'Valid',
            data: <?= json_encode(array_values($statusData['valid'])) ?>,
            borderColor: 'rgba(34,197,94,1)',
            backgroundColor: 'rgba(34,197,94,0.2)',
            fill: true,
            tension: 0.3,
            pointRadius: 2,
        },
        {
            label: 'Invalid',
            data: <?= json_encode(array_values($statusData['invalid'])) ?>,
            borderColor: 'rgba(239,68,68,1)',
            backgroundColor: 'rgba(239,68,68,0.2)',
            fill: true,
            tension: 0.3,
            pointRadius: 2,
        },
        {
            label: 'Suspended',
            data: <?= json_encode(array_values($statusData['suspended'])) ?>,
            borderColor: 'rgba(202,138,4,1)',
            backgroundColor: 'rgba(202,138,4,0.2)',
            fill: true,
            tension: 0.3,
            pointRadius: 2,
        },
    ]
};
const config = {
    type: 'line',
    data: data,
    options: {
        responsive: true,
        interaction: {
            mode: 'nearest',
            axis: 'x',
            intersect: false
        },
        scales: {
            x: {
                ticks: {
                    maxRotation: 45,
                    minRotation: 45,
                    autoSkip: true,
                    maxTicksLimit: 10
                },
                grid: {
                    display: false
                }
            },
            y: {
                beginAtZero: true,
                grid: {
                    borderDash: [5, 5]
                },
                title: {
                    display: true,
                    text: 'Number of Licenses'
                }
            }
        },
        plugins: {
            legend: {
                position: 'top',
                labels: {
                    boxWidth: 20,
                    padding: 15
                }
            },
            tooltip: {
                mode: 'index',
                intersect: false
            }
        }
    }
};
new Chart(ctx, config);