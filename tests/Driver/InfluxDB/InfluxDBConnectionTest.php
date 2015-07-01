<?php
namespace Corley\DBAL\Driver\InfluxDB;

class InfluxDBConnectionTest extends \PHPUnit_Framework_TestCase
{
    public function testConnectionManagerWithParams()
    {
        $config = new \Doctrine\DBAL\Configuration();
        $connectionParams = array(
            'dbname' => 'mydb',
            'user' => 'root',
            'password' => 'root',
            'host' => 'localhost',
            'port' => 8086,
            "driverClass" => "Corley\\DBAL\\Driver\\InfluxDB",
        );
        $conn = \Doctrine\DBAL\DriverManager::getConnection($connectionParams, $config);

        $this->assertInstanceOf("Doctrine\DBAL\Connection", $conn);
        $this->assertInstanceOf("Corley\DBAL\Driver\InfluxDB", $conn->getDriver());

        $this->assertEquals(8086, $conn->getPort());
        $this->assertEquals("localhost", $conn->getHost());

        $this->assertEquals("mydb", $conn->getDatabase());
    }

    public function testCreateAStatement()
    {
        $conn = new InfluxDBConnection([
            "host" => "locahost",
            "port" => 8086,
            "dbname" => "mydb",
        ], "root", "root", []);

        $stmt = $conn->prepare("SELECT * FROM cpu");
        $this->assertInstanceOf("Corley\\DBAL\\Driver\\InfluxDB\\InfluxDBStatement", $stmt);
    }
}
