<?php

require_once 'vendor/autoload.php';

// Load Laravel application
$app = require_once 'bootstrap/app.php';

// Force load testing environment
$app->loadEnvironmentFrom('.env.testing');
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Get database configuration
$config = config('database.connections.mysql');
echo "Database Configuration:\n";
echo "Host: " . $config['host'] . "\n";
echo "Port: " . $config['port'] . "\n";
echo "Database: " . $config['database'] . "\n";
echo "Username: " . $config['username'] . "\n";
echo "Password: " . ($config['password'] ? '***' : 'null') . "\n";

// Test database connection
try {
    $pdo = new PDO(
        "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']}",
        $config['username'],
        $config['password']
    );
    echo "\nDatabase connection successful!\n";

    // Check current database
    $stmt = $pdo->query("SELECT DATABASE() as current_db");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Current database: " . $result['current_db'] . "\n";

    // Check if tables exist
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "Tables in database: " . count($tables) . "\n";
    echo "First 5 tables: " . implode(', ', array_slice($tables, 0, 5)) . "\n";

} catch (PDOException $e) {
    echo "Database connection failed: " . $e->getMessage() . "\n";
}
