<?php
/**
 * Database Connection Class for Mikhmon Custom Features
 * Singleton pattern with auto-create database and tables
 */

if (substr($_SERVER["REQUEST_URI"], -12) == "database.php") {
    header("Location:../");
    exit;
}

class Database {
    private static $db = null;
    
    // Database configuration
    private static $host   = '127.0.0.1';
    private static $user   = 'root';
    private static $pass   = '';
    private static $dbname = 'mikhmon_agent';

    /**
     * Get PDO connection instance (singleton)
     * @return PDO
     */
    public static function getConnection() {
        if (self::$db === null) {
            try {
                // First connect without database to create it if needed
                $dsn = "mysql:host=" . self::$host . ";charset=utf8mb4";
                $pdo = new PDO($dsn, self::$user, self::$pass, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]);

                // Create database if not exists
                $pdo->exec("CREATE DATABASE IF NOT EXISTS `" . self::$dbname . "` 
                            CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");

                // Connect to the database
                $dsn = "mysql:host=" . self::$host . ";dbname=" . self::$dbname . ";charset=utf8mb4";
                self::$db = new PDO($dsn, self::$user, self::$pass, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]);

                // Run schema if tables don't exist
                self::initSchema();

            } catch (PDOException $e) {
                error_log("Mikhmon DB Error: " . $e->getMessage());
                return null;
            }
        }
        return self::$db;
    }

    /**
     * Initialize database schema from schema.sql
     */
    private static function initSchema() {
        try {
            // Check if agents table exists (indicator that schema has been run)
            $stmt = self::$db->query("SHOW TABLES LIKE 'agents'");
            if ($stmt->rowCount() == 0) {
                $schemaFile = __DIR__ . '/schema.sql';
                if (file_exists($schemaFile)) {
                    $sql = file_get_contents($schemaFile);
                    self::$db->exec($sql);
                }
            }
            
            // Check billing table specifically
            $stmt = self::$db->query("SHOW TABLES LIKE 'billing'");
            if ($stmt->rowCount() == 0) {
                self::$db->exec("CREATE TABLE IF NOT EXISTS billing (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    username VARCHAR(100) NOT NULL,
                    phone VARCHAR(20) NOT NULL,
                    amount INT DEFAULT 0,
                    due_date INT NOT NULL,
                    description TEXT,
                    is_paid TINYINT DEFAULT 0,
                    last_reminded_at DATETIME NULL,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
            }
        } catch (PDOException $e) {
            error_log("Mikhmon Schema Error: " . $e->getMessage());
        }
    }

    /**
     * Get a setting value by key
     * @param string $key
     * @param string $default
     * @return string
     */
    public static function getSetting($key, $default = '') {
        try {
            $db = self::getConnection();
            if ($db === null) return $default;
            
            $stmt = $db->prepare("SELECT value FROM settings WHERE `key` = ?");
            $stmt->execute([$key]);
            $result = $stmt->fetch();
            return $result ? $result['value'] : $default;
        } catch (PDOException $e) {
            return $default;
        }
    }

    /**
     * Set a setting value by key
     * @param string $key
     * @param string $value
     * @return bool
     */
    public static function setSetting($key, $value) {
        try {
            $db = self::getConnection();
            if ($db === null) return false;
            
            $stmt = $db->prepare("INSERT INTO settings (`key`, value) VALUES (?, ?) 
                                  ON DUPLICATE KEY UPDATE value = ?");
            $stmt->execute([$key, $value, $value]);
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Get multiple settings by key prefix
     * @param string $prefix
     * @return array
     */
    public static function getSettingsByPrefix($prefix) {
        try {
            $db = self::getConnection();
            if ($db === null) return [];
            
            $stmt = $db->prepare("SELECT `key`, value FROM settings WHERE `key` LIKE ?");
            $stmt->execute([$prefix . '%']);
            $results = [];
            while ($row = $stmt->fetch()) {
                $results[$row['key']] = $row['value'];
            }
            return $results;
        } catch (PDOException $e) {
            return [];
        }
    }
}
