<?php
$host = "mysql-e9d20af-projectslt.h.aivencloud.com";
$port = 19831;
$dbname = "slt_sensor_data";
$username = "avnadmin";
$password = "AVNS_DCuj_-oe9824isjv6kQ";

// Connect to MySQL on Aiven
$conn = new mysqli($host, $username, $password, $dbname, $port);

if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

$sensor_name = $_POST['sensor_name'];
$value = $_POST['value'];

// Insert into your table (ensure it exists)
$sql = "INSERT INTO sensor_readings (sensor_name, value) VALUES (?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sd", $sensor_name, $value);

if ($stmt->execute()) {
  echo "Data inserted successfully!";
} else {
  echo "Insert failed: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
