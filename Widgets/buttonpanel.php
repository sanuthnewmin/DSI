<?php
// Database configuration
$host = "mysql-e9d20af-projectslt.h.aivencloud.com";
$port = 19831;
$dbname = "slt_sensor_data";
$user = "avnadmin";
$pass = "AVNS_DCuj_-oe9824isjv6kQ";

// ESP32 IP address
$esp32_ip = "192.168.1.39";

// Connect to MySQL
$conn = new mysqli($host, $user, $pass, $dbname, $port);
if ($conn->connect_error) {
    die("DB Connection failed: " . $conn->connect_error);
}

// Handle toggle request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $device = $_POST['device'] ?? '';
    $status = isset($_POST['status']) ? (int)$_POST['status'] : 0;

    $map = ["device2" => 2, "device4" => 4, "device5" => 5];
    if (isset($map[$device])) {
        $pin = $map[$device];
        $endpoint = ($status ? "H" : "L") . $pin;
        $url = "http://$esp32_ip/$endpoint";

        // Call ESP32 endpoint
        $response = @file_get_contents($url);

        $statusText = $status ? 'Online' : 'Offline';
        $stmt = $conn->prepare("UPDATE device_status SET status = ? WHERE device_name = ?");
        $stmt->bind_param("ss", $statusText, $device);
        $stmt->execute();
        $stmt->close();

        echo json_encode(["success" => true, "status" => $statusText, "device" => $device]);
    } else {
        echo json_encode(["success" => false, "error" => "Invalid device"]);
    }
    exit;
}

// Load device statuses
$deviceStatuses = [];
$result = $conn->query("SELECT * FROM device_status");
while ($row = $result->fetch_assoc()) {
    $deviceStatuses[$row['device_name']] = $row['status'];
}
$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #eef2f7;
            text-align: center;
            padding: 30px;
        }
        .device {
            display: inline-block;
            margin: 15px;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            padding: 20px;
            width: 200px;
        }
        .device h3 {
            margin: 10px 0 5px;
        }
        .status {
            margin: 10px 0;
            font-weight: bold;
            font-size: 16px;
            color: #333;
        }
        .icon i {
            font-size: 32px;
            margin-top: 10px;
            transition: color 0.3s ease;
        }
        .switch {
            position: relative;
            display: inline-block;
            width: 50px;
            height: 24px;
        }
        .switch input { display: none; }
        .slider {
            position: absolute;
            cursor: pointer;
            top: 0; left: 0; right: 0; bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 24px;
        }
        .slider:before {
            position: absolute;
            content: "";
            height: 18px;
            width: 18px;
            left: 3px;
            bottom: 3px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }
        input:checked + .slider {
            background-color: #28a745;
        }
        input:checked + .slider:before {
            transform: translateX(26px);
        }
    </style>
</head>
<body>



<?php
$devices = [
    "device2" => "Device 1",
    "device4" => "Device 2",
    "device5" => "Device 3"
];
?>

<?php foreach ($devices as $key => $label): ?>
<div class="device" id="card-<?php echo $key; ?>">
    <h3><?php echo $label; ?></h3>
    <div class="status" id="status-<?php echo $key; ?>">
        <?php echo $deviceStatuses[$key] ?? 'Offline'; ?>
    </div>
    <div class="icon" id="icon-<?php echo $key; ?>">
        <i class="fas fa-plug" style="color: <?php echo ($deviceStatuses[$key] === 'Online') ? 'green' : 'red'; ?>"></i>
    </div>
    <label class="switch">
        <input type="checkbox" <?php if (($deviceStatuses[$key] ?? '') === 'Online') echo 'checked'; ?>
               onchange="toggleDevice('<?php echo $key; ?>', this.checked)">
        <span class="slider"></span>
    </label>
</div>
<?php endforeach; ?>

<script>
function toggleDevice(device, status) {
    const statusText = document.getElementById("status-" + device);
    const icon = document.getElementById("icon-" + device);

    // Update UI instantly
    const newStatus = status ? "Online" : "Offline";
    statusText.innerText = newStatus;
    icon.innerHTML = `<i class="fas fa-plug" style="color: ${status ? 'green' : 'red'};"></i>`;

    fetch("", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `device=${device}&status=${status ? 1 : 0}`
    })
    .then(res => res.json())
    .then(data => {
        if (!data.success) {
            alert("Failed to toggle: " + data.error);
        }
    })
    .catch(err => {
        alert("Error: " + err);
        // Revert UI on error
        const revertStatus = !status;
        statusText.innerText = revertStatus ? "Online" : "Offline";
        icon.innerHTML = `<i class="fas fa-plug" style="color: ${revertStatus ? 'green' : 'red'};"></i>`;
        document.querySelector(`#card-${device} input[type=checkbox]`).checked = revertStatus;
    });
}
</script>

</body>
</html>
