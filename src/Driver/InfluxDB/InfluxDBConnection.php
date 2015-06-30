<?php
namespace Corley\DBAL\Driver\InfluxDB;

use InfluxDB\Client;
use InfluxDB\Options;
use InfluxDB\Adapter\GuzzleAdapter;
use Doctrine\DBAL\Driver\Connection;
use Corley\DBAL\Driver\InfluxDB\InfluxDBStatement;

class InfluxDBConnection implements Connection
{
    private $client;

    public function __construct(array $params, $username, $password, array $driverOptions = array())
    {
        $http = new \GuzzleHttp\Client();

        $options = new Options();
        $options->setHost($params["host"]);
        $options->setDatabase($params["dbname"]);
        $options->setUsername($username);
        $options->setPassword($password);
        $options->setPort($params["port"]);

        $adapter = new GuzzleAdapter($http, $options);

        $this->client = new Client($adapter);
    }

    function prepare($prepareString)
    {
        return new InfluxDBStatement($this->client, $prepareString);
    }

    function query()
    {

    }

    function quote($input, $type=\PDO::PARAM_STR)
    {

    }

    function exec($statement)
    {
    }

    function lastInsertId($name = null)
    {

    }

    function beginTransaction()
    {

    }

    function commit()
    {

    }

    function rollBack()
    {

    }

    function errorCode()
    {

    }

    function errorInfo()
    {

    }
}
