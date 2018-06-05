<?php

require_once "DBTestCase.php";

use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Mini\Model\Twitch
 */
class TwitchTest extends TestCase
{
    /**
     * @var \Mini\Model\Twitch
     */
    private $twitch;

    /**
     * @var \Aeris\GuzzleHttp\Mock
     */
    private $httpMock;

    public function __construct()
    {
        ob_start();

        parent::__construct();
    }

    public function setUp()
    {
        $this->httpMock = new \GuzzleHttp\Handler\MockHandler();
        $client = new \GuzzleHttp\Client(array(
            'handler' => \GuzzleHttp\HandlerStack::create($this->httpMock)
        ));
        $this->twitch = new \Mini\Model\Twitch($client, 'test', ['http_errors' => false]);
        parent::setUp();
    }

    public function tearDown()
    {
        $this->httpMock = null;
        $this->twitch = null;
        parent::tearDown();
    }
}
