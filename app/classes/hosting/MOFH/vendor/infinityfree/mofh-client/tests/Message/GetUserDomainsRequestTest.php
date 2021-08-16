<?php

namespace Tests\Unit\MofhClient\Message;

use GuzzleHttp\Psr7\Response;
use InfinityFree\MofhClient\Message\GetUserDomainsRequest;
use InfinityFree\MofhClient\Message\GetUserDomainsResponse;
use InfinityFree\MofhClient\Tests\Message\RequestTestCase;

class GetUserDomainsRequestTest extends RequestTestCase
{
    /**
     * @var array
     */
    public $requestData;

    public function setUp() : void
    {
        parent::setUp();
        $this->request = new GetUserDomainsRequest($this->guzzle);

        $this->requestData = array_merge($this->defaultParameters, [
            'username' => $this->faker->userName,
        ]);
        $this->request->initialize($this->requestData);
    }

    public function testNullServerResponse()
    {
        $this->mockHandler->append(new Response(200, [], 'null'));

        $response = $this->request->send();
        $this->assertInstanceOf(GetUserDomainsResponse::class, $response);
        $this->assertEquals(null, $response->getStatus());

        $this->assertValidGetCall('getuserdomains');
    }

    public function testGetData()
    {
        $data = $this->request->getData();

        $this->assertEquals([
            'username' => $this->requestData['username'],
            'api_user' => $this->defaultParameters['apiUsername'],
            'api_key' => $this->defaultParameters['apiPassword'],
        ], $data);
    }

    public function testSendSuccessful()
    {
        $domains = [$this->faker->domainName, $this->faker->domainName];

        $responseData = array_map(function ($domain) {
            return ['ACTIVE', $domain];
        }, $domains);

        $this->mockHandler->append(new Response(200, [], json_encode($responseData)));

        $response = $this->request->send();
        $this->assertInstanceOf(GetUserDomainsResponse::class, $response);
        $this->assertTrue($response->isSuccessful());
        $this->assertEquals($domains, $response->getDomains());
        $this->assertEquals('ACTIVE', $response->getStatus());

        $this->assertValidGetCall('getuserdomains');
    }

    public function testSendFailed()
    {
        $this->mockHandler->append(new Response(200, [], 'The data is not valid.'));

        $response = $this->request->send();
        $this->assertInstanceOf(GetUserDomainsResponse::class, $response);
        $this->assertFalse($response->isSuccessful());
        $this->assertEquals('The data is not valid.', $response->getMessage());

        $this->assertValidGetCall('getuserdomains');
    }

    protected function assertValidGetCall($function)
    {
        $this->assertCount(1, $this->container);
        $request = $this->container[0]['request'];

        $this->assertEquals('GET', $request->getMethod());
        $this->assertEquals(
            $this->defaultParameters['apiUrl'] . $function . '?' . http_build_query($this->request->getData()),
            (string)$request->getUri()
        );
    }
}
