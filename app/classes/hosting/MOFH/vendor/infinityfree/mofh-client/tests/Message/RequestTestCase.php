<?php

namespace InfinityFree\MofhClient\Tests\Message;

use Faker\Factory as FakerFactory;
use Faker\Generator;
use GuzzleHttp\Client as Guzzle;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use InfinityFree\MofhClient\Client;
use InfinityFree\MofhClient\Message\AbstractRequest;
use PHPUnit\Framework\TestCase;

class RequestTestCase extends TestCase
{
    /**
     * @var Client
     */
    public $client;

    /**
     * @var MockHandler
     */
    public $mockHandler;

    /**
     * @var Generator
     */
    public $faker;

    /**
     * @var Guzzle
     */
    public $guzzle;

    /**
     * A container for the Guzzle history
     *
     * @var array
     */
    public $container = [];

    /**
     * The default parameters to push to the request.
     *
     * @var array
     */
    public $defaultParameters = [];

    /**
     * @var AbstractRequest
     */
    public $request;

    public function setUp() : void
    {
        $this->container = [];
        $this->mockHandler = new MockHandler();
        $stack = HandlerStack::create($this->mockHandler);
        $stack->push(Middleware::history($this->container));
        $this->guzzle = new Guzzle(['handler' => $stack]);
        $this->client = new Client($this->guzzle);
        $this->faker = FakerFactory::create();

        $this->defaultParameters = [
            'apiUsername' => $this->faker->word,
            'apiPassword' => $this->faker->word,
            'apiUrl' => 'https://' . $this->faker->domainName . '/xml-api/',
        ];
    }

    public function assertValidPostCall($function)
    {
        $this->assertCount(1, $this->container);
        $request = $this->container[0]['request'];

        $this->assertEquals('POST', $request->getMethod());
        $this->assertEquals(
            $this->defaultParameters['apiUrl'] . $function,
            (string)$request->getUri()
        );
        $this->assertEquals(http_build_query($this->request->getData()), $request->getBody());
    }
}