<?php

namespace InfinityFree\MofhClient\Tests\Message;

use GuzzleHttp\Psr7\Response;
use InfinityFree\MofhClient\Message\AvailabilityRequest;
use InfinityFree\MofhClient\Message\AvailabilityResponse;

class AvailabilityRequestTest extends RequestTestCase
{
    public function setUp() : void
    {
        parent::setUp();
        $this->request = new AvailabilityRequest($this->guzzle);
        $this->request->initialize($this->defaultParameters);
    }

    public function testGetData()
    {
        $domain = $this->faker->domainName;
        $this->request->setDomain($domain);

        $data = $this->request->getData();
        $this->assertEquals([
            'api_user' => $this->defaultParameters['apiUsername'],
            'api_key' => $this->defaultParameters['apiPassword'],
            'domain' => $domain,
        ], $data);
    }

    public function testSendSuccessful()
    {
        $this->mockHandler->append(new Response(200, [], '1'));

        $domain = $this->faker->domainName;
        $this->request->setDomain($domain);

        $response = $this->request->send();
        $this->assertInstanceOf(AvailabilityResponse::class, $response);
        $this->assertTrue($response->isSuccessful());

        $this->assertCount(1, $this->container);
        $request = $this->container[0]['request'];

        $this->assertEquals('GET', $request->getMethod());
        $this->assertEquals(
            $this->defaultParameters['apiUrl'] . 'checkavailable?' . http_build_query($this->request->getData()),
            (string)$request->getUri()
        );
    }

    public function testSendFailed()
    {
        $this->mockHandler->append(new Response(200, [], '0'));

        $domain = $this->faker->domainName;
        $this->request->setDomain($domain);

        $response = $this->request->send();
        $this->assertInstanceOf(AvailabilityResponse::class, $response);
        $this->assertFalse($response->isSuccessful());
        $this->assertEquals('0', $response->getMessage());

        $this->assertCount(1, $this->container);
        $request = $this->container[0]['request'];

        $this->assertEquals('GET', $request->getMethod());
        $this->assertEquals(
            $this->defaultParameters['apiUrl'] . 'checkavailable?' . http_build_query($this->request->getData()),
            (string)$request->getUri()
        );
    }
}