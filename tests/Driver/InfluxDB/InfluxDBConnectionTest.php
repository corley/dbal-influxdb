<?php
namespace Corley\DBAL\Driver\InfluxDB;

use Prophecy\Argument;

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

    public function testDirectQuery()
    {
        $stmt = $this->prophesize("Corley\\DBAL\\Driver\\InfluxDB\\InfluxDBStatement");
        $stmt->execute()->willReturn(true);

        $mock = $this->getMockBuilder("Corley\\DBAL\\Driver\\InfluxDB\\InfluxDBConnection")
            ->disableOriginalConstructor()
            ->setMethods(["prepare"])
            ->getMock();

        $mock->expects($this->once())
            ->method("prepare")
            ->will($this->returnValue($stmt->reveal()));

        $stmt = $mock->query("SELECT * FROM cpu");

        $this->assertInstanceOf("Corley\\DBAL\\Driver\\InfluxDB\\InfluxDBStatement", $stmt);
    }

    public function testQuoteWithSingle()
    {
        $mock = $this->getMockBuilder("Corley\\DBAL\\Driver\\InfluxDB\\InfluxDBConnection")
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->assertEquals("'OK'", $mock->quote("OK"));
    }

    public function testExecStatement()
    {
        $stmt = $this->prophesize("Corley\\DBAL\\Driver\\InfluxDB\InfluxDBStatement");
        $stmt->execute()->willReturn(true);
        $stmt->rowCount()->willReturn(5);

        $mock = $this->getMockBuilder("Corley\\DBAL\\Driver\\InfluxDB\\InfluxDBConnection")
            ->disableOriginalConstructor()
            ->setMethods(["query"])
            ->getMockForAbstractClass();

        $mock->expects($this->once())
            ->method("query")
            ->will($this->returnValue($stmt->reveal()));

        $this->assertEquals(5, $mock->exec("OK"));
    }

    /**
     * @expectedException RuntimeException
     */
    public function testExecStatementWithError()
    {
        $stmt = $this->prophesize("Corley\\DBAL\\Driver\\InfluxDB\InfluxDBStatement");
        $stmt->execute()->willReturn(false);

        $mock = $this->getMockBuilder("Corley\\DBAL\\Driver\\InfluxDB\\InfluxDBConnection")
            ->disableOriginalConstructor()
            ->setMethods(["query"])
            ->getMockForAbstractClass();

        $mock->expects($this->once())
            ->method("query")
            ->will($this->returnValue($stmt->reveal()));

        $mock->exec("OK");
    }
}
