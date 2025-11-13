<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Query untuk statistik
// 1. Total Data Dibuat (Tidak berubah)
$stmt_total_articles = $conn->prepare("SELECT COUNT(*) FROM articles WHERE user_id = :user_id");
$stmt_total_articles->execute(['user_id' => $user_id]);
$total_articles = $stmt_total_articles->fetchColumn();

// 2. Total File Dilampirkan (Tidak berubah)
$stmt_total_files = $conn->prepare("
    SELECT COUNT(af.id) 
    FROM article_files af
    JOIN articles a ON af.article_id = a.id
    WHERE a.user_id = :user_id
");
$stmt_total_files->execute(['user_id' => $user_id]);
$total_files = $stmt_total_files->fetchColumn();

// 3. Data untuk Grafik (Jumlah data per hari dalam 30 hari terakhir) - PERUBAHAN
$stmt_chart = $conn->prepare("
    SELECT 
        DATE(created_at) as activity_date,
        COUNT(id) as total
    FROM articles
    WHERE user_id = :user_id AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) -- DIUBAH DARI 7
    GROUP BY DATE(created_at)
    ORDER BY activity_date ASC
");
$stmt_chart->execute(['user_id' => $user_id]);
$chart_data_raw = $stmt_chart->fetchAll(PDO::FETCH_KEY_PAIR); // Fetch as 'activity_date' => 'total'

// Siapkan data untuk 30 hari terakhir, termasuk hari dengan 0 data - LOGIKA BARU
$chart_labels = [];
$chart_values = [];
for ($i = 29; $i >= 0; $i--) { // DIUBAH DARI 6
    $date = date('Y-m-d', strtotime("-$i days"));
    $chart_labels[] = date('d M', strtotime($date)); // Format label diubah agar lebih ringkas (e.g., "30 Oct")
    $chart_values[] = isset($chart_data_raw[$date]) ? (int)$chart_data_raw[$date] : 0;
}

$chart_labels_json = json_encode($chart_labels);
$chart_values_json = json_encode($chart_values);


$page_title = 'Statistik Data';
include 'header.php';
?>

<h1 class="mb-4">Statistik Data Anda</h1>

<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card text-center h-100">
            <div class="card-body">
                <i class="bi bi-journal-text" style="font-size: 3rem;"></i>
                <h5 class="card-title mt-2">Total Data Dibuat</h5>
                <p class="display-4 fw-bold"><?= htmlspecialchars($total_articles) ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-6 mb-4">
        <div class="card text-center h-100">
            <div class="card-body">
                <i class="bi bi-paperclip" style="font-size: 3rem;"></i>
                <h5 class="card-title mt-2">Total File Dilampirkan</h5>
                <p class="display-4 fw-bold"><?= htmlspecialchars($total_files) ?></p>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        Aktivitas Penambahan Data (30 Hari Terakhir) </div>
    <div class="card-body">
        <canvas id="myChart"></canvas>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('myChart');

    new Chart(ctx, {
        type: 'line', 
        data: {
            labels: <?= $chart_labels_json ?>,
            datasets: [{
                label: 'Jumlah Data Dibuat',
                data: <?= $chart_values_json ?>,
                backgroundColor: 'rgba(13, 110, 253, 0.1)', 
                borderColor: 'rgba(13, 110, 253, 1)',
                borderWidth: 2,
                fill: true, 
                tension: 0.1 
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1, 
                        callback: function(value) {if (value % 1 === 0) {return value;}}
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });
</script>

<?php
include 'footer.php';
?>