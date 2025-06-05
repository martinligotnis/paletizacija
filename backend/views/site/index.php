<?php
use yii\helpers\Html;
$this->title = 'Kontroles Panelis';
?>

<div class="site-index">
    <h1 class="mb-4"><?= Html::encode($this->title) ?></h1>

    <div class="row mb-4">
        <?php $cards = [
            ['title'=>'Pirmā palete','value'=> $firstPalletTime ?? '–','bg'=>'warning'],
            ['title'=>'Paletes Šodien kopā','value'=>$totalToday,       'bg'=>'primary'],
            ['title'=>'SKU','value'=>$uniqueProducts,       'bg'=>'success'],
            ['title'=>'Paletes / stundā','value'=>$palletsPerHour,         'bg'=>'info'],
            ['title'=>'Overall','value'=>$overallOee!==null? "{$overallOee}" : '–', 'bg'=>'secondary'],
        ];?>
        <?php foreach($cards as $c): ?>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card text-white bg-<?= $c['bg'] ?>">
                <div class="card-header"><?= $c['title'] ?></div>
                <div class="card-body">
                    <h2 class="card-title"><?= $c['value'] ?></h2>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card">
                <div class="card-header">Šobrīd tiek ražots</div>
                <div class="card-body">
                    <h3><?= Html::encode($currentProduct) ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-8 mb-4">
            <div class="card">
                <div class="card-header">Šodien saražotās paletes katrā stundā</div>
                <div class="card-body">
                    <canvas id="palletsChart" height="150"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('palletsChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?= json_encode($chartLabels)?>,
            datasets: [{
                label: 'Pallets',
                data: <?= json_encode($chartData)?>,
                borderColor: '#007bff',
                backgroundColor: 'rgba(0,123,255,0.1)',
                fill: true,
                tension: 0.3
            }]
        },
        options: {
            scales: {
                x: { title: { display: true, text: 'Hour of Day' } },
                y: { beginAtZero: true, title: { display: true, text: 'Count' } }
            }
        }
    });
</script>
