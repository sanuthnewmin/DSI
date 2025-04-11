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

// Fetch the latest sensor data for each device
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

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Device Sensor Dashboard</title>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />
  <style>
    /* Same CSS as before */
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }
    body {
      font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
      background: #f4f6f9;
      padding: 40px;
    }
    .dashboard {
      display: flex;
      justify-content: center;
      gap: 30px;
      flex-wrap: wrap;
    }
    .card {
      background-color: #ffffff;
      border-radius: 15px;
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
      padding: 25px 30px;
      width: 320px;
      transition: transform 0.2s ease-in-out;
    }
    .card:hover {
      transform: translateY(-5px);
    }
    .card h3 {
      text-align: center;
      margin-bottom: 25px;
      color: #2c3e50;
      font-size: 20px;
    }
    .data-row {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 10px 0;
      border-bottom: 1px solid #ecf0f1;
      font-size: 15px;
    }
    .data-row:last-child {
      border-bottom: none;
    }
    .icon {
      margin-right: 8px;
      font-size: 16px;
      vertical-align: middle;
    }
    .data-label {
      display: flex;
      align-items: center;
      color: #34495e;
      font-weight: 500;
    }
    .data-value {
      font-weight: bold;
      color: #2d3436;
    }
    .loading::after {
      content: ' .';
      animation: dots 1s steps(3, end) infinite;
    }
    @keyframes dots {
      0%, 20% { content: ' .'; }
      40% { content: ' ..'; }
      60% { content: ' ...'; }
      100% { content: ' .'; }
    }
  </style>
</head>
<body>

  <div class="dashboard">
    <?php for ($i = 1; $i <= 3; $i++): 
      $device = "device{$i}";
      $data = $results[$device];
    ?>
    <div class="card">
      <h3>Device (0<?= $i ?>) Sensor Data</h3>
      <div class="data-row">
        <span class="data-label"><i class="fas fa-thermometer-half icon" style="color: #e74c3c;"></i>K-Type Temp</span>
        <div class="data-value"><?= $data['k_type_temp'] ?? 'Loading...' ?></div>
      </div>
      <div class="data-row">
        <span class="data-label"><i class="fas fa-temperature-high icon" style="color: #e67e22;"></i>IR Temp</span>
        <div class="data-value"><?= $data['ir_temp'] ?? 'Loading...' ?></div>
      </div>
      <div class="data-row">
        <span class="data-label"><i class="fas fa-plug icon" style="color: #3498db;"></i>Current 1</span>
        <div class="data-value"><?= $data['current_sensor_1'] ?? 'Loading...' ?></div>
      </div>
      <div class="data-row">
        <span class="data-label"><i class="fas fa-plug icon" style="color: #2980b9;"></i>Current 2</span>
        <div class="data-value"><?= $data['current_sensor_2'] ?? 'Loading...' ?></div>
      </div>
    </div>
    <?php endfor; ?>
  </div>

  <script>
    document.querySelectorAll('.data-value').forEach(el => {
      if (el.innerText.includes('Loading')) {
        el.classList.add('loading');
      }
    });

     // Refresh the page every 5 seconds (5000 milliseconds)
  setTimeout(() => {
    location.reload();
  }, 5000);
  </script>

</body>
</html>
