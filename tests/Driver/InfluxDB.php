<?php
namespace Corley\DBAL\Driver;

class InfluxDBTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateConnection()
    {
        $driver = new InfluxDB();
        $conn = $driver->connect([
            "host" => "",
            "dbname" => "",
            "port" => 8086,
            "user" => "root",
            "password" => "root",
        ], "root", "root", []);

        $this->assertInstanceOf("Corley\\DBAL\\Driver\\InfluxDB\\InfluxDBConnection", $conn);
        $this->assertInstanceOf("Corley\\DBAL\\Platform\\InfluxDBPlatform", $driver->getDatabasePlatform());
    }

    public function testInfluxDBName()
    {
        $driver = new InfluxDB();
        $this->assertEquals("influxdb", $driver->getName());
    }
}
