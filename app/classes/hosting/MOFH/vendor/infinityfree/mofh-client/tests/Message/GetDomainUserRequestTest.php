<?php

namespace Tests\Unit\MofhClient\Message;

use GuzzleHttp\Psr7\Response;
use InfinityFree\MofhClient\Message\GetDomainUserRequest;
use InfinityFree\MofhClient\Message\GetDomainUserResponse;
use InfinityFree\MofhClient\Tests\Message\RequestTestCase;

class GetDomainUserRequestTest extends RequestTestCase
{
    /**
     * @var array
     */
    public $requestData;

    public function setUp() : void
    {
        parent::setUp();
        $this->request = new GetDomainUserRequest($this->guzzle);

        $this->requestData = array_merge($this->defaultParameters, [
            'domain' => $this->faker->domainName,
        ]);
        $this->request->initialize($this->requestData);
    }

    public function testGetData()
    {
        $data = $this->request->getData();

        $this->assertEquals([
            'domain' => $this->requestData['domain'],
            'api_user' => $this->defaultParameters['apiUsername'],
            'api_key' => $this->defaultParameters['apiPassword'],
        ], $data);
    }

    public function testSendSuccessful()
    {
        $domain = $this->faker->domainName;
        $username = 'test_' . $this->faker->randomNumber(8);
        $webRoot = '/home/vol12_3/' . $domain . '/' . $username . '/htdocs';
        $responseData = ['ACTIVE', $domain, $webRoot, $username];

        $this->mockHandler->append(new Response(200, [], json_encode($responseData)));

        $response = $this->request->send();
        $this->assertInstanceOf(GetDomainUserResponse::class, $response);
        $this->assertTrue($response->isSuccessful());
        $this->assertTrue($response->isFound());
        $this->assertEquals($domain, $response->getDomain());
        $this->assertEquals('ACTIVE', $response->getStatus());
        $this->assertEquals($webRoot, $response->getDocumentRoot());
        $this->assertEquals($username, $response->getUsername());

        $this->assertValidGetCall('getdomainuser');
    }

    public function testSendReturnsNull()
    {
        $this->mockHandler->append(new Response(200, [], 'null'));

        $response = $this->request->send();
        $this->assertInstanceOf(GetDomainUserResponse::class, $response);
        $this->assertTrue($response->isSuccessful());
        $this->assertFalse($response->isFound());
        $this->assertNull($response->getDomain());
        $this->assertNull($response->getStatus());
        $this->assertNull($response->getDocumentRoot());
        $this->assertNull($response->getUsername());

        $this->assertValidGetCall('getdomainuser');
    }

    public function testSendFailed()
    {
        $this->mockHandler->append(new Response(200, [], 'The data is not valid.'));

        $response = $this->request->send();
        $this->assertInstanceOf(GetDomainUserResponse::class, $response);
        $this->assertFalse($response->isSuccessful());
        $this->assertEquals('The data is not valid.', $response->getMessage());

        $this->assertValidGetCall('getdomainuser');
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
