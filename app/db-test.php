<?php

$host = 'db';
$db   = 'LandingPageDB';
$user = 'Administrator';
$pass = 'AdministratorPass';


try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$db;charset=utf8mb4",
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]
    );

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "<h1>Database connection successful</h1>";

    $stmt = $pdo->query("SELECT NOW() AS time");
    $row = $stmt->fetch();

    echo "<p>Database time: {$row['time']}</p>";

    $sql = "SHOW TABLES";
  $result = $pdo->query($sql);

  if ($result->rowCount() > 0) {
    echo "<h2>Tables in the database:</h2>";
    echo "<ul>";
    // Output the names of the tables
    while ($row = $result->fetch(PDO::FETCH_NUM)) {
      echo "<li>" . $row[0] . "</li>"; // $row[0] contains the table name
    }
    echo "</ul>";
  } else {
    echo "No tables found in the database.";
  }

} catch (PDOException $e) {
    echo "<h1>Database connection failed</h1>";
    echo "<pre>" . $e->getMessage() . "</pre>";
}

// Close connection
$pdo = null;