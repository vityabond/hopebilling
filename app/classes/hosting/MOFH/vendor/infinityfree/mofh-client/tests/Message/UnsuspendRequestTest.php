<?php

namespace InfinityFree\MofhClient\Tests\Message;

use GuzzleHttp\Psr7\Response;
use InfinityFree\MofhClient\Message\UnsuspendRequest;
use InfinityFree\MofhClient\Message\UnsuspendResponse;

class UnsuspendRequestTest extends RequestTestCase
{
    /**
     * @var array
     */
    public $requestData;

    public function setUp() : void
    {
        parent::setUp();
        $this->request = new UnsuspendRequest($this->guzzle);

        $this->requestData = array_merge($this->defaultParameters, [
            'username' => $this->faker->userName,
        ]);
        $this->request->initialize($this->requestData);
    }

    public function testGetData()
    {
        $data = $this->request->getData();

        $this->assertEquals([
            'user' => $this->requestData['username'],
        ], $data);
    }

    public function testSendSuccessful()
    {
        $this->mockHandler->append(new Response(200, [], '
<unsuspendacct>
    <result>
        <status>1</status>
        <statusmsg>
            <script>if (self[\'clear_ui_status\']) { clear_ui_status(); }</script>
            vns6ohth account has been unsuspended
        </statusmsg>
    </result>
</unsuspendacct>
'));

        $response = $this->request->send();
        $this->assertInstanceOf(UnsuspendResponse::class, $response);
        $this->assertTrue($response->isSuccessful());

        $this->assertValidPostCall('unsuspendacct');
    }

    public function testSendFailed()
    {
        $this->mockHandler->append(new Response(200, [], 'The username is invalid (8 characters maximum). .   '));

        $response = $this->request->send();
        $this->assertInstanceOf(UnsuspendResponse::class, $response);
        $this->assertFalse($response->isSuccessful());
        $this->assertEquals('The username is invalid (8 characters maximum). .', $response->getMessage());

        $this->assertValidPostCall('unsuspendacct');
    }

    public function testSendFailedNotActive()
    {
        $this->mockHandler->append(new Response(200, [], '<unsuspendacct>
    <result>
        <status>0</status>
        <statusmsg>
	This account is NOT currently suspended (status : a ) .  .  
        </statusmsg>
    </result>
</unsuspendacct>'));

        $response = $this->request->send();
        $this->assertInstanceOf(UnsuspendResponse::class, $response);
        $this->assertFalse($response->isSuccessful());
        $this->assertEquals('This account is NOT currently suspended (status : a ) .  .', $response->getMessage());
        $this->assertEquals('a', $response->getStatus());

        $this->assertValidPostCall('unsuspendacct');
    }

    public function testSendFailedDeleted()
    {
        $this->mockHandler->append(new Response(200, [], '<unsuspendacct>
    <result>
        <status>0</status>
        <statusmsg>
	This account is NOT currently suspended (status :  ) .  .  
        </statusmsg>
    </result>
</unsuspendacct>'));

        $response = $this->request->send();
        $this->assertInstanceOf(UnsuspendResponse::class, $response);
        $this->assertFalse($response->isSuccessful());
        $this->assertEquals('This account is NOT currently suspended (status :  ) .  .', $response->getMessage());
        $this->assertEquals('d', $response->getStatus());

        $this->assertValidPostCall('unsuspendacct');
    }
}