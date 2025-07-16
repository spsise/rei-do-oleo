<?php

require_once 'vendor/autoload.php';

// Set environment before loading Laravel
putenv('APP_ENV=testing');

// Load Laravel application
$app = require_once 'bootstrap/app.php';

// Force load testing environment
$app->loadEnvironmentFrom('.env.testing');
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Clear config cache to ensure fresh loading
$app->make('config')->flush();

// Get database configuration
$config = config('database.connections.mysql');
echo "=== TEST DATABASE CONFIGURATION ===\n";
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
    echo "\n✅ Database connection successful!\n";

    // Check current database
    $stmt = $pdo->query("SELECT DATABASE() as current_db");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Current database: " . $result['current_db'] . "\n";

    // Check if tables exist
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "Tables in database: " . count($tables) . "\n";
    echo "First 5 tables: " . implode(', ', array_slice($tables, 0, 5)) . "\n";

    // Check if this is the test database
    if ($result['current_db'] === 'rei_do_oleo_test') {
        echo "\n✅ Correctly connected to TEST database\n";
    } else {
        echo "\n❌ WARNING: Connected to wrong database! Expected: rei_do_oleo_test, Got: " . $result['current_db'] . "\n";
    }

} catch (PDOException $e) {
    echo "\n❌ Database connection failed: " . $e->getMessage() . "\n";
}

echo "\n=== ENVIRONMENT VARIABLES ===\n";
echo "APP_ENV: " . env('APP_ENV') . "\n";
echo "DB_CONNECTION: " . env('DB_CONNECTION') . "\n";
echo "DB_DATABASE: " . env('DB_DATABASE') . "\n";
echo "DB_HOST: " . env('DB_HOST') . "\n";

// Safety check
if (app()->environment('testing') && env('DB_DATABASE') !== 'rei_do_oleo_test') {
    echo "\n❌ ABORT: Not using test database! Current: " . env('DB_DATABASE') . "\n";
    exit(1);
}
