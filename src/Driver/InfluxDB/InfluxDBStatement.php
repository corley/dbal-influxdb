<?php
namespace Corley\DBAL\Driver\InfluxDB;

use ArrayIterator;
use IteratorAggregate;
use Doctrine\DBAL\Driver\Statement;
use InfluxDB\Client;

class InfluxDBStatement implements IteratorAggregate, Statement
{
    private $client;
    private $statement;
    private $values = [];

    private $results = [];

    private $currentIterator = null;

    private $defaultFetchMode = \PDO::FETCH_BOTH;

    public function __construct(Client $client, $statement)
    {
        $this->client = $client;
        $this->statement = $statement;
    }

    function bindValue($param, $value, $type = null)
    {
        $this->values[$param] = $value;
    }

    function bindParam($column, &$variable, $type = null, $length = null)
    {
        $this->values[$column] =& $variable;
    }

    function errorCode()
    {

    }

    function errorInfo()
    {

    }

    function execute($params = null)
    {
        $this->values = (is_array($params)) ? array_replace($this->values, $params) : $this->values;

        $stmt = $this->statement;

        foreach ($this->values as $key => $value) {
            $value = is_string($value) ? "'".addslashes($value)."'" : $value;
            $stmt = preg_replace("/(\?|:{$key})/i", "{$value}", $stmt, 1);
        }

        $results = $this->client->query($stmt);
        $return = false;
        if (is_array($results)) {
            $return = true;
            foreach ($results["results"][0]["series"][0]["values"] as $row => $elem) {
                foreach ($results["results"][0]["series"][0]["columns"] as $index => $value) {
                    $this->results[$row][$value] = $results["results"][0]["series"][0]["values"][$row][$index];
                }
            }
        }

        return $return;
    }

    function rowCount()
    {
        return count($this->results);
    }

    public function getIterator() {
        return new ArrayIterator($this->results);
    }

    public function closeCursor()
    {

    }

    public function columnCount()
    {
        return (count($this->results)) ? count($this->results[0]) : null;
    }

    public function setFetchMode($fetchMode, $arg2 = null, $arg3 = null)
    {
        $this->defaultFetchMode = $fetchMode;
    }

    public function fetch($fetchMode = null)
    {
        if (!$this->currentIterator) {
            $this->currentIterator = $this->getIterator();
        }

        $data = $this->currentIterator->current();

        $this->currentIterator->next();

        return $data;
    }

    public function fetchAll($fetchMode = null)
    {
        return $this->getIterator();
    }

    public function fetchColumn($columnIndex = 0)
    {
        $elem = $this->fetch();

        if ($elem) {
            if (array_key_exists($columnIndex, $elem)) {
                return $elem[$columnIndex];
            } else {
                return $elem[array_keys($elem)[$columnIndex]];
            }
        }

        return null;
    }
}
