<?php
use Mini\Model\BotListDescriptor;


require_once "DBTestCase.php";

/**
 * @coversDefaultClass \Mini\Model\Bots
 */
class BotsTest extends DBTestCase
{
    /**
     * @var \Mini\Model\Bots
     */
    private $bots;

    /**
     * @var int
     */
    const pageSize = 100;

    public function setUp()
    {
        $this->bots = new \Mini\Model\Bots(static::$pdo, self::pageSize);
        parent::setUp();
    }

    public function tearDown()
    {
        $this->bots = null;
        parent::tearDown();
    }

    /**
     * @covers ::getBot
     */
    public function testGetBot()
    {
        $bot = $this->bots->getBot("butler_of_ec0ke");

        $this->assertEquals("butler_of_ec0ke", $bot->name);
        $this->assertEquals(22, $bot->type);
        //$this->assertLessThanOrEqual(strtotime($bot->date), time());
        $this->assertEquals("ec0ke", $bot->channel);
        $this->assertInstanceOf(\Mini\Model\Bot::class, $bot);
    }

    /**
     * @covers ::getBot
     */
    public function testGetNotExistingBot()
    {
        $bot = $this->bots->getBot("freaktechnik");

        $this->assertFalse($bot);
    }

    /**
     * @covers ::getBotsByType
     */
    public function testGetBotsByType()
    {
        $bots = $this->bots->getBotsByType(22);

        $queryTable = $this->getConnection()->createQueryTable(
            'bots', 'SELECT name FROM bots WHERE type=22 LIMIT '.self::pageSize
        );

        $this->assertCount($queryTable->getRowCount(), $bots);

        foreach($bots as $bot) {
            $this->assertObjectHasAttribute("name", $bot);
            $this->assertObjectHasAttribute("type", $bot);
            $this->assertObjectHasAttribute("date", $bot);
            //$this->assertLessThanOrEqual(strtotime($bot->date), time());
            $this->assertInstanceOf(\Mini\Model\Bot::class, $bot);
        }

        $bots = $this->bots->getBotsByType(22, self::pageSize);
        $queryTable = $this->getConnection()->createQueryTable(
            'bots', 'SELECT name FROM bots WHERE type=22 LIMIT '.self::pageSize.','.self::pageSize
        );
        $this->assertCount($queryTable->getRowCount(), $bots);
    }

    /**
     * @covers ::getCount
     */
    public function testGetCount()
    {
        $botCount = $this->bots->getCount();
        $this->assertEquals($botCount, $this->getConnection()->getRowCount('bots'));

        $descriptor = new BotListDescriptor();
        $descriptor->type = 22;
        $botCount = $this->bots->getCount($descriptor);
        $queryTable = $this->getConnection()->createQueryTable(
            'bots', 'SELECT name FROM bots WHERE type=22'
        );

        $this->assertEquals($botCount, $queryTable->getRowCount());
    }

    /**
     * @covers ::getBots
     */
    public function testGetBots()
    {
        $bots = $this->bots->getBots();

        $queryTable = $this->getConnection()->createQueryTable(
            'bots', 'SELECT name FROM bots LIMIT '.self::pageSize
        );

        $this->assertCount($queryTable->getRowCount(), $bots);

        foreach($bots as $bot) {
            $this->assertObjectHasAttribute("name", $bot);
            $this->assertObjectHasAttribute("type", $bot);
            $this->assertObjectHasAttribute("multichannel", $bot);
            $this->assertObjectHasAttribute("typename", $bot);
            $this->assertInstanceOf(\Mini\Model\Bot::class, $bot);
        }

        $bots = $this->bots->getBots(2);
        $queryTable = $this->getConnection()->createQueryTable(
            'bots', 'SELECT name FROM bots LIMIT '.self::pageSize.','.self::pageSize
        );
        $this->assertCount($queryTable->getRowCount(), $bots);
    }

    /**
     * @covers ::getAllRawBots
     */
    public function testGetAllRawBots()
    {
        $bots = $this->bots->getAllRawBots();

        $queryTable = $this->getConnection()->createQueryTable(
            'bots', 'SELECT name FROM bots LIMIT '.self::pageSize
        );

        $this->assertCount($queryTable->getRowCount(), $bots);

        foreach($bots as $bot) {
            $this->assertObjectHasAttribute("name", $bot);
            $this->assertObjectHasAttribute("type", $bot);
            $this->assertObjectHasAttribute("date", $bot);
            //$this->assertLessThanOrEqual(strtotime($bot->date), time());
            $this->assertInstanceOf(\Mini\Model\Bot::class, $bot);
        }

        $bots = $this->bots->getAllRawBots(self::pageSize);
        $queryTable = $this->getConnection()->createQueryTable(
            'bots', 'SELECT name FROM bots LIMIT '.self::pageSize.','.self::pageSize
        );
        $this->assertCount($queryTable->getRowCount(), $bots);
    }

    /**
     * @covers ::getBotsByNames
     */
    public function testGetBotsByNames()
    {
        $names = array(
            "butler_of_ec0ke",
            "freaktechnik",
            "nightbot",
            "ec0ke",
            "syntria"
        );
        $bots = $this->bots->getBotsByNames($names);

        $this->assertCount(2, $bots);

        foreach($bots as $bot) {
            $this->assertObjectHasAttribute("name", $bot);
            $this->assertObjectHasAttribute("type", $bot);
            $this->assertObjectHasAttribute("date", $bot);
            $this->assertObjectNotHasAttribute("index", $bot);
            $this->assertObjectNotHasAttribute("value", $bot);
            //$this->assertLessThanOrEqual(strtotime($bot->date), time());
            $this->assertInstanceOf(\Mini\Model\Bot::class, $bot);
        }

        $bots = $this->bots->getBotsByNames($names, self::pageSize);
        $this->assertEmpty($bots);
    }

    /**
     * @covers ::removeBot
     */
    public function testRemoveBot()
    {
        $initialCount = $this->bots->getCount();
        $this->bots->removeBot('ackbot');

        $this->assertEquals($initialCount - 1, $this->bots->getCount());

        $queryTable = $this->getConnection()->createQueryTable(
            'bots0', "SELECT * FROM bots WHERE name='ackbot'"
        );
        $this->assertEquals(0, $queryTable->getRowCount());

        $incativeTable = $this->getConnection()->createQueryTable(
            'inactiveTable0', "SELECT * FROM inactive_bots WHERE twitch_id=1"
        );
        $this->assertEquals(1, $queryTable->getRowCount());
    }

    /**
     * @covers ::removeBots
     */
    public function testRemoveBots()
    {
        $initialCount = $this->bots->getCount();
        $this->bots->removeBots(array(1, 15));

        $this->assertEquals($initialCount - 2, $this->bots->getCount());

        $queryTable = $this->getConnection()->createQueryTable(
            'bots', "SELECT * FROM bots WHERE twitch_id IN (1,15)"
        );
        $this->assertEquals(0, $queryTable->getRowCount());

        $incativeTable = $this->getConnection()->createQueryTable(
            'inactiveTable', "SELECT * FROM inactive_bots WHERE twitch_id IN (1,15)"
        );
        $this->assertEquals(2, $queryTable->getRowCount());
    }
}
