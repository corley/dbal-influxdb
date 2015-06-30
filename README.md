# Doctrine DBAL for InfluxDB

[![Circle CI](https://circleci.com/gh/corley/dbal-influxdb/tree/master.svg?style=svg)](https://circleci.com/gh/corley/dbal-influxdb/tree/master)

InfluxDB driver for Doctrine DBAL (Abstraction Layer)

## Query builder

```php
$qb = $conn->createQueryBuilder();

$qb->select("*")
    ->from("cpu_load_short")
    ->where("time = ?")
    ->setParameter(0, 1434055562000000000);

$data = $qb->execute();
foreach ($data->fetchAll() as $element) {
    // Use your element
}
```

## Create a connection

```php
$config = new \Doctrine\DBAL\Configuration();
//..
$connectionParams = array(
    'dbname' => 'mydb',
    'user' => 'root',
    'password' => 'root',
    'host' => 'localhost',
    'port' => 8086,
    "driverClass" => "Corley\\DBAL\\Driver\\InfluxDB",
);
$conn = \Doctrine\DBAL\DriverManager::getConnection($connectionParams, $config);
```
