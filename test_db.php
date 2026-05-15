<?php
$mysqli = new mysqli("127.0.0.1", "root", "root", "mardiana_koperasi");
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}
$result = $mysqli->query("SELECT * FROM jns_simpan");
while($row = $result->fetch_assoc()) {
    echo $row['id'] . " - " . $row['jns_simpan'] . "\n";
}
?>
