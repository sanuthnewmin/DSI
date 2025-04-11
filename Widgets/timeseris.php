<?php
// Database connection
$host = "mysql-e9d20af-projectslt.h.aivencloud.com";
$port = 19831;
$dbname = "slt_sensor_data";
$username = "avnadmin";
$password = "AVNS_DCuj_-oe9824isjv6kQ";
$conn = new mysqli($host, $username, $password, $dbname, $port);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch latest data
$queries = [
    'device1' => "SELECT * FROM sensor_data ORDER BY timestamp DESC LIMIT 1",
    'device2' => "SELECT * FROM device2_sensor_data ORDER BY timestamp DESC LIMIT 1",
    'device3' => "SELECT * FROM device3_sensor_data ORDER BY timestamp DESC LIMIT 1"
];

$results = [];
foreach ($queries as $device => $query) {
    $result = $conn->query($query);
    if ($result && $row = $result->fetch_assoc()) {
        $results[$device] = $row;
    } else {
        $results[$device] = null;
    }
}

header('Content-Type: application/json');
if (isset($_GET['json'])) {
    echo json_encode($results);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Device Sensor Dashboard</title>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #f4f4f4;
      margin: 0;
      padding: 0;
    }
    .container {
      display: flex;
      flex-wrap: wrap;
      justify-content: center;
      gap: 30px;
      margin: 30px;
    }
    .card {
      background: white;
      padding: 20px;
      border-radius: 10px;
      box-shadow: 0 0 15px rgba(0,0,0,0.1);
      width: 320px;
    }
    .card h3 {
      text-align: center;
      margin-bottom: 20px;
      color: #333;
    }
    .data-row {
      display: flex;
      justify-content: space-between;
      margin-bottom: 10px;
    }
    .data-row span {
      font-weight: bold;
    }
    .chart-container {
      margin-top: 10px;
      height: 150px;
    }
  </style>
</head>
<body>

<div class="container">
  <!-- Device Cards -->
  <?php for ($i = 1; $i <= 3; $i++): ?>
    <div class="card">
      <h3>Device (0<?= $i ?>) Sensor Data</h3>

      <?php foreach (['k_type_temp' => 'K-Type Temp', 'ir_temp' => 'IR Temp', 'current_sensor_1' => 'Current Sensor 1', 'current_sensor_2' => 'Current Sensor 2'] as $key => $label): ?>
        <div class="data-row">
          <span><?= $label ?>:</span>
          <div id="device<?= $i ?>_<?= $key ?>"><?= $results["device$i"][$key] ?? 'Loading...' ?></div>
        </div>
        <div class="chart-container">
          <canvas id="chart_device<?= $i ?>_<?= $key ?>"></canvas>
        </div>
      <?php endforeach; ?>

    </div>
  <?php endfor; ?>
</div>

<script>
  const charts = {};

  function createChart(ctxId) {
    const ctx = document.getElementById(ctxId).getContext('2d');
    return new Chart(ctx, {
      type: 'line',
      data: {
        labels: [],
        datasets: [{
          label: '',
          data: [],
          borderColor: 'rgba(75, 192, 192, 1)',
          tension: 0.3,
          fill: false,
          pointRadius: 2
        }]
      },
      options: {
        animation: false,
        responsive: true,
        plugins: {
          legend: { display: false }
        },
        scales: {
          x: { display: false },
          y: {
            beginAtZero: false,
            ticks: { precision: 2 }
          }
        }
      }
    });
  }

  function initCharts() {
    for (let i = 1; i <= 3; i++) {
      for (const sensor of ['k_type_temp', 'ir_temp', 'current_sensor_1', 'current_sensor_2']) {
        const chartId = `chart_device${i}_${sensor}`;
        charts[chartId] = createChart(chartId);
      }
    }
  }

  function fetchAndUpdate() {
    fetch(window.location.href + '?json=true')
      .then(res => res.json())
      .then(data => {
        const time = new Date().toLocaleTimeString();

        for (let i = 1; i <= 3; i++) {
          const device = data[`device${i}`];
          if (!device) continue;

          for (const key of ['k_type_temp', 'ir_temp', 'current_sensor_1', 'current_sensor_2']) {
            const value = device[key] ?? 'N/A';
            const elementId = `device${i}_${key}`;
            document.getElementById(elementId).innerText = value;

            const chartId = `chart_device${i}_${key}`;
            const chart = charts[chartId];
            if (chart) {
              if (chart.data.labels.length >= 10) {
                chart.data.labels.shift();
                chart.data.datasets[0].data.shift();
              }
              chart.data.labels.push(time);
              chart.data.datasets[0].data.push(parseFloat(value));
              chart.update();
            }
          }
        }
      });
  }

  initCharts();
  fetchAndUpdate();
  setInterval(fetchAndUpdate, 5000);
</script>
</body>
</html>
