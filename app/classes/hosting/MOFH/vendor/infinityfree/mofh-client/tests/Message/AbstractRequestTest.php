<?php

namespace InfinityFree\MofhClient\Tests\Message;

use Faker\Factory;
use Faker\Generator;
use GuzzleHttp\Client as Guzzle;
use GuzzleHttp\Psr7\Response;
use InfinityFree\MofhClient\Message\AbstractRequest;
use InfinityFree\MofhClient\Message\AbstractResponse;
use PHPUnit\Framework\TestCase;

class AbstractRequestTest extends TestCase
{
    /**
     * @var TestRequest
     */
    public $request;

    /**
     * @var Generator
     */
    public $faker;

    public function setUp() : void
    {
        parent::setUp();
        $this->request = new TestRequest(new Guzzle());
        $this->faker = Factory::create();
    }

    public function testGetSetApiUsername()
    {
        $username = $this->faker->word;

        $this->request->setApiUsername($username);
        $this->assertEquals($username, $this->request->getApiUsername());
    }

    public function testGetSetApiPassword()
    {
        $password = $this->faker->word;

        $this->request->setApiPassword($password);
        $this->assertEquals($password, $this->request->getApiPassword());
    }

    public function testGetSetApiUrl()
    {
        $url = $this->faker->url;

        $this->request->setApiUrl($url);
        $this->assertEquals($url, $this->request->getApiUrl());
    }

    public function testGetSetUsername()
    {
        $username = $this->faker->word;

        $this->request->setUsername($username);
        $this->assertEquals($username, $this->request->getUsername());
    }

    public function testGetUndefinedParameter()
    {
        $result = $this->request->getFooAttribute();
        $this->assertNull($result);
    }

    public function testInitializeAfterSent()
    {
        $this->request->send();

        $this->expectException(\RuntimeException::class);

        $this->request->initialize([]);
    }

    public function testSetAttributeAfterSent()
    {
        $this->request->send();

        $this->expectException(\RuntimeException::class);

        $this->request->setFooAttribute();
    }

}

class TestRequest extends AbstractRequest
{

    public function sendData($data)
    {
        return $this->response = new TestResponse($this, new Response());
    }

    public function getData()
    {
        return [];
    }

    public function getFooAttribute()
    {
        return $this->getParameter('foo');
    }

    public function setFooAttribute()
    {
        return $this->setParameter('foo', 'bar');
    }
}

class TestResponse extends AbstractResponse
{

}