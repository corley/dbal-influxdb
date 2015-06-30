<?php
namespace Corley\DBAL\Driver\InfluxDB;

use Prophecy\Argument;

class InfluxDBStatementTest extends \PHPUnit_Framework_TestCase
{
    public function testPrepareOneParamStatement()
    {
        $client = $this->prophesize("InfluxDB\Client");
        $client->query("SELECT * FROM cpu WHERE time = 1234")->shouldBeCalledTimes(1);

        $stmt = new InfluxDBStatement($client->reveal(), "SELECT * FROM cpu WHERE time = ?");
        $stmt->bindValue(1, 1234);
        $stmt->execute();
    }

    public function testPrepareMoreParamStatement()
    {
        $client = $this->prophesize("InfluxDB\Client");
        $client->query("SELECT * FROM cpu WHERE time = 1234 and field = 12345 and another = 1847")
            ->shouldBeCalledTimes(1);

        $stmt = new InfluxDBStatement(
            $client->reveal(),
            "SELECT * FROM cpu WHERE time = ? and field = ? and another = ?"
        );
        $stmt->bindValue(1, 1234);
        $stmt->bindValue(2, 12345);
        $stmt->bindValue(3, 1847);
        $stmt->execute();
    }

    public function testStringEscape()
    {
        $client = $this->prophesize("InfluxDB\Client");
        $client->query("SELECT * FROM cpu WHERE message = 'hello'")->shouldBeCalledTimes(1);

        $stmt = new InfluxDBStatement($client->reveal(), "SELECT * FROM cpu WHERE message = ?");
        $stmt->bindValue(1, "hello");
        $stmt->execute();
    }

    public function testPrepareStatementWithName()
    {
        $client = $this->prophesize("InfluxDB\Client");
        $client->query("SELECT * FROM cpu WHERE message = 'hello'")->shouldBeCalledTimes(1);

        $stmt = new InfluxDBStatement($client->reveal(), "SELECT * FROM cpu WHERE message = :name");
        $stmt->bindValue("name", "hello");
        $stmt->execute();
    }

    public function testPrepareStatementWithBindParam()
    {
        $client = $this->prophesize("InfluxDB\Client");
        $client->query("SELECT * FROM cpu WHERE message = 'hello'")->shouldBeCalledTimes(1);

        $ref = null;

        $stmt = new InfluxDBStatement($client->reveal(), "SELECT * FROM cpu WHERE message = :name");
        $stmt->bindParam("name", $ref);

        $ref = "hello";

        $stmt->execute();
    }

    public function testExecuteAppendsParams()
    {
        $client = $this->prophesize("InfluxDB\Client");
        $client->query("SELECT * FROM cpu WHERE message = 'hello'")->shouldBeCalledTimes(1);

        $stmt = new InfluxDBStatement($client->reveal(), "SELECT * FROM cpu WHERE message = :name");
        $stmt->execute([
            "name" => "hello"
        ]);
    }

    public function testCountResults()
    {
        $client = $this->prophesize("InfluxDB\Client");
        $client->query(Argument::Any())
            ->willReturn(json_decode(<<<EOF
{
    "results": [
        {
            "series": [
                {
                    "name": "cpu_load_short",
                    "tags": {
                        "host": "server01",
                        "region": "us-west"
                    },
                    "columns": [
                        "time",
                        "value"
                    ],
                    "values": [
                        [
                            "2015-06-11T20:46:02Z",
                            0.64
                        ],
                        [
                            "2015-06-12T02:19:22Z",
                            0.54
                        ],
                        [
                            "2015-06-12T05:06:02Z",
                            0.92
                        ]
                    ]
                }
            ]
        }
    ]
}
EOF
, true))
        ;

        $stmt = new InfluxDBStatement($client->reveal(), "SELECT * FROM cpu");
        $stmt->execute();

        $this->assertEquals(3, $stmt->rowCount());
        $this->assertCount(3, $stmt->fetchAll());
    }

    public function testColumnCount()
    {
        $client = $this->prophesize("InfluxDB\Client");
        $client->query(Argument::Any())
            ->willReturn(json_decode(<<<EOF
{
    "results": [
        {
            "series": [
                {
                    "name": "cpu_load_short",
                    "tags": {
                        "host": "server01",
                        "region": "us-west"
                    },
                    "columns": [
                        "time",
                        "value"
                    ],
                    "values": [
                        [
                            "2015-06-11T20:46:02Z",
                            0.64
                        ],
                        [
                            "2015-06-12T02:19:22Z",
                            0.54
                        ],
                        [
                            "2015-06-12T05:06:02Z",
                            0.92
                        ]
                    ]
                }
            ]
        }
    ]
}
EOF
, true))
        ;

        $stmt = new InfluxDBStatement($client->reveal(), "SELECT * FROM cpu");
        $stmt->execute();

        $this->assertEquals(2, $stmt->columnCount());
    }

    public function testCountColumnWorkOnZero()
    {
        $client = $this->prophesize("InfluxDB\Client");
        $client->query("SELECT * FROM cpu")->shouldBeCalledTimes(1);

        $stmt = new InfluxDBStatement($client->reveal(), "SELECT * FROM cpu");
        $stmt->execute();

        $this->assertNull($stmt->columnCount());
    }

    public function testFetchSingleRecord()
    {
        $client = $this->prophesize("InfluxDB\Client");
        $client->query(Argument::Any())
            ->willReturn(json_decode(<<<EOF
{
    "results": [
        {
            "series": [
                {
                    "name": "cpu_load_short",
                    "tags": {
                        "host": "server01",
                        "region": "us-west"
                    },
                    "columns": [
                        "time",
                        "value"
                    ],
                    "values": [
                        [
                            "2015-06-11T20:46:02Z",
                            0.64
                        ],
                        [
                            "2015-06-12T02:19:22Z",
                            0.54
                        ],
                        [
                            "2015-06-12T05:06:02Z",
                            0.92
                        ]
                    ]
                }
            ]
        }
    ]
}
EOF
, true))
        ;

        $stmt = new InfluxDBStatement($client->reveal(), "SELECT * FROM cpu");
        $stmt->execute();

        $this->assertEquals([
            "time" => "2015-06-11T20:46:02Z",
            "value" => 0.64,
        ], $stmt->fetch());

        $this->assertEquals([
            "time" => "2015-06-12T02:19:22Z",
            "value" => 0.54,
        ], $stmt->fetch());

        $this->assertEquals([
            "time" => "2015-06-12T05:06:02Z",
            "value" => 0.92,
        ], $stmt->fetch());

        $this->assertNull($stmt->fetch());
        $this->assertNull($stmt->fetch());
    }

    public function testFetchSingleColumn()
    {
        $client = $this->prophesize("InfluxDB\Client");
        $client->query(Argument::Any())
            ->willReturn(json_decode(<<<EOF
{
    "results": [
        {
            "series": [
                {
                    "name": "cpu_load_short",
                    "tags": {
                        "host": "server01",
                        "region": "us-west"
                    },
                    "columns": [
                        "time",
                        "value"
                    ],
                    "values": [
                        [
                            "2015-06-11T20:46:02Z",
                            0.64
                        ],
                        [
                            "2015-06-12T02:19:22Z",
                            0.54
                        ],
                        [
                            "2015-06-12T05:06:02Z",
                            0.92
                        ]
                    ]
                }
            ]
        }
    ]
}
EOF
, true))
        ;

        $stmt = new InfluxDBStatement($client->reveal(), "SELECT * FROM cpu");
        $stmt->execute();

        $this->assertEquals("2015-06-11T20:46:02Z", $stmt->fetchColumn());
        $this->assertEquals("2015-06-12T02:19:22Z", $stmt->fetchColumn());
        $this->assertEquals("2015-06-12T05:06:02Z", $stmt->fetchColumn());
        $this->assertNull($stmt->fetchColumn());
    }

    public function testFetchNumberedColumn()
    {
        $client = $this->prophesize("InfluxDB\Client");
        $client->query(Argument::Any())
            ->willReturn(json_decode(<<<EOF
{
    "results": [
        {
            "series": [
                {
                    "name": "cpu_load_short",
                    "tags": {
                        "host": "server01",
                        "region": "us-west"
                    },
                    "columns": [
                        "time",
                        "value"
                    ],
                    "values": [
                        [
                            "2015-06-11T20:46:02Z",
                            0.64
                        ],
                        [
                            "2015-06-12T02:19:22Z",
                            0.54
                        ],
                        [
                            "2015-06-12T05:06:02Z",
                            0.92
                        ]
                    ]
                }
            ]
        }
    ]
}
EOF
, true))
        ;

        $stmt = new InfluxDBStatement($client->reveal(), "SELECT * FROM cpu");
        $stmt->execute();

        $this->assertEquals(0.64, $stmt->fetchColumn(1));
        $this->assertEquals(0.54, $stmt->fetchColumn("value"));
    }
}
