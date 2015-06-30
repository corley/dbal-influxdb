<?php
namespace Corley\DBAL\Driver;

use Doctrine\DBAL\VersionAwarePlatformDriver;
use Doctrine\DBAL\Driver\ExceptionConverterDriver;
use Doctrine\DBAL\Driver;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\DriverException;
use Corley\DBAL\Platform\InfluxDBPlatform;
use Corley\DBAL\Driver\InfluxDB\InfluxDBConnection;

class InfluxDB implements Driver, ExceptionConverterDriver, VersionAwarePlatformDriver
{
    public function connect(array $params, $username = null, $password = null, array $driverOptions = array())
    {
        $conn = new InfluxDBConnection($params, $username, $password, $driverOptions);
        return $conn;
    }

    public function getDatabasePlatform()
    {
        return new InfluxDBPlatform();
    }

    public function getSchemaManager(Connection $conn)
    {

    }

    public function getName()
    {
        return "influxdb";
    }

    public function getDatabase(Connection $conn)
    {

    }

    public function convertException($message, DriverException $exception)
    {

    }

    public function createDatabasePlatformForVersion($version)
    {
    }
}
