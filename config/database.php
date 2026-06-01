<?php

define('DB_HOST', 'localhost');
define('DB_NAME', 'equalizer');
define('DB_USER', 'root');
define('DB_PASS', ''); 

// define('DB_HOST', 'localhost');
// define('DB_NAME', 'daysofwa_dominance');
// define('DB_USER', 'daysofwa_creator');
// define('DB_PASS', 'x[&qA~K-iRK[a2m2'); 

function getDBConnection() {
    static $pdo = null;
    
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            
            error_log("Database connection failed: " . $e->getMessage());
            die("⚠️ Koneksi database gagal. Hubungi admin.");
        }
    }
    
    return $pdo;
}


function dbQuery($sql, $params = []) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt;
}


function dbFetch($sql, $params = []) {
    $stmt = dbQuery($sql, $params);
    return $stmt->fetch();
}


function dbFetchAll($sql, $params = []) {
    $stmt = dbQuery($sql, $params);
    return $stmt->fetchAll();
}
?>