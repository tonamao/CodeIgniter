<?php
use PHPUnit\Framework\TestCase;
use PHPUnit\DbUnit\TestCaseTrait;

abstract class DatabaseTester extends TestCase
{
    use TestCaseTrait;
    static private $pdo = null;
    private $conn = null;

    final public function getConnection()
    {
        if ($this->conn === null) {
            if (self::$pdo == null) {
                $dsn = sprintf("mysql:dbname=%s;host=%s;%s", $_ENV['MYSQL_DATABASE'], $_ENV['DATABASE_HOST'], 'charset=utf8');
                self::$pdo = new PDO( $dsn, $_ENV['MYSQL_USER'], $_ENV['MYSQL_PASSWORD'] );
            }
            $this->conn = $this->createDefaultDBConnection(self::$pdo, $_ENV['MYSQL_DATABASE']);
        }
        return $this->conn;
    }
}
