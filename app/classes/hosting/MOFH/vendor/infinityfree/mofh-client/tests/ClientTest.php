<?php

namespace InfinityFree\MofhClient\Tests;

use Faker\Factory as FakerFactory;
use Faker\Generator;
use InfinityFree\MofhClient\Client;
use InfinityFree\MofhClient\Message\AvailabilityRequest;
use InfinityFree\MofhClient\Message\CreateAccountRequest;
use InfinityFree\MofhClient\Message\GetUserDomainsRequest;
use InfinityFree\MofhClient\Message\PasswordRequest;
use InfinityFree\MofhClient\Message\SuspendRequest;
use InfinityFree\MofhClient\Message\UnsuspendRequest;
use PHPUnit\Framework\TestCase;

class ClientTest extends TestCase
{
    /**
     * @var Client
     */
    public $client;

    /**
     * @var Generator
     */
    public $faker;

    public function setUp() : void
    {
        $this->client = new Client();
        $this->faker = FakerFactory::create();
    }

    public function testCreate()
    {
        $client = Client::create();
        $this->assertInstanceOf(Client::class, $client);
    }

    public function testGetDefaultParameters()
    {
        $parameters = $this->client->getDefaultParameters();
        $this->assertTrue(is_array($parameters));
    }

    public function testGetSetApiUsername()
    {
        $apiUsername = $this->faker->word;
        $this->client->setApiUsername($apiUsername);
        $this->assertEquals($apiUsername, $this->client->getApiUsername());
    }

    public function testGetGetSetApiPassword()
    {
        $apiPassword = $this->faker->word;
        $this->client->setApiPassword($apiPassword);
        $this->assertEquals($apiPassword, $this->client->getApiPassword());
    }

    public function testGetGetSetApiUrl()
    {
        $url = $this->faker->url;
        $this->client->setApiUrl($url);
        $this->assertEquals($url, $this->client->getApiUrl());
    }

    public function testGetGetSetPlan()
    {
        $plan = $this->faker->word;
        $this->client->setPlan($plan);
        $this->assertEquals($plan, $this->client->getPlan());
    }

    public function testInitialize()
    {
        $username = $this->faker->word;
        $password = $this->faker->word;

        $this->client->initialize([
            'apiUsername' => $username,
            'apiPassword' => $password,
        ]);

        $this->assertEquals($username, $this->client->getApiUsername());
        $this->assertEquals($password, $this->client->getApiPassword());
        $this->assertEquals($this->client->getDefaultParameters()['apiUrl'], $this->client->getApiUrl());
    }

    public function testCreateAccount()
    {
        $parameters = [
            'username' => $this->faker->userName,
            'password' => $this->faker->password,
            'email' => $this->faker->email,
            'domain' => $this->faker->domainName,
            'plan' => $this->faker->word,
        ];

        $request = $this->client->createAccount($parameters);
        $this->assertInstanceOf(CreateAccountRequest::class, $request);
        $this->assertEquals($parameters['username'], $request->getUsername());
        $this->assertEquals($parameters['password'], $request->getPassword());
        $this->assertEquals($parameters['email'], $request->getEmail());
        $this->assertEquals($parameters['domain'], $request->getDomain());
        $this->assertEquals($parameters['plan'], $request->getPlan());
    }

    public function testSuspend()
    {
        $parameters = [
            'username' => $this->faker->userName,
            'reason' => $this->faker->sentence,
        ];

        $request = $this->client->suspend($parameters);
        $this->assertInstanceOf(SuspendRequest::class, $request);
        $this->assertEquals($parameters['username'], $request->getUsername());
        $this->assertEquals($parameters['reason'], $request->getReason());
    }

    public function testUnsuspend()
    {
        $parameters = [
            'username' => $this->faker->userName,
        ];

        $request = $this->client->unsuspend($parameters);
        $this->assertInstanceOf(UnsuspendRequest::class, $request);
        $this->assertEquals($parameters['username'], $request->getUsername());
    }

    public function testPassword()
    {
        $parameters = [
            'username' => $this->faker->userName,
            'password' => $this->faker->password,
        ];

        $request = $this->client->password($parameters);
        $this->assertInstanceOf(PasswordRequest::class, $request);
        $this->assertEquals($parameters['username'], $request->getUsername());
        $this->assertEquals($parameters['password'], $request->getPassword());
    }

    public function testAvailability()
    {
        $parameters = [
            'domain' => $this->faker->domainName,
        ];

        $request = $this->client->availability($parameters);
        $this->assertInstanceOf(AvailabilityRequest::class, $request);
        $this->assertEquals($parameters['domain'], $request->getDomain());
    }

    public function testGetParameterUndefined()
    {
        $method = new \ReflectionMethod(Client::class, 'getParameter');
        $method->setAccessible(true);

        $this->assertNull($method->invoke($this->client, 'iDontExist'));
    }

    public function testGetUserDomains()
    {
        $username = $this->faker->userName;

        $request = $this->client->getUserDomains(['username' => $username]);
        $this->assertInstanceOf(GetUserDomainsRequest::class, $request);
        $this->assertEquals($username, $request->getUsername());
    }
}
