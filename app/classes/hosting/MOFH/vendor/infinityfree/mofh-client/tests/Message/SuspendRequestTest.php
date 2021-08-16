<?php

namespace InfinityFree\MofhClient\Tests\Message;

use GuzzleHttp\Psr7\Response;
use InfinityFree\MofhClient\Message\SuspendRequest;
use InfinityFree\MofhClient\Message\SuspendResponse;

class SuspendRequestTest extends RequestTestCase
{
    /**
     * @var array
     */
    public $requestData;

    public function setUp() : void
    {
        parent::setUp();
        $this->request = new SuspendRequest($this->guzzle);

        $this->requestData = array_merge($this->defaultParameters, [
            'username' => $this->faker->userName,
            'reason' => $this->faker->sentence,
        ]);
        $this->request->initialize($this->requestData);
    }

    public function testGetData()
    {
        $data = $this->request->getData();

        $this->assertEquals([
            'user' => $this->requestData['username'],
            'reason' => $this->requestData['reason'],
            'linked' => '0',
        ], $data);
    }

    public function testSendSuccessful()
    {
        $this->mockHandler->append(new Response(200, [], '<suspendacct>
    <result>
        <status>1</status>
        <statusmsg>
            <script>if (self[\'clear_ui_status\']) { clear_ui_status(); }</script>
            Changing Shell to /bin/false...Changing shell for test1234.
            Shell changed.
            Locking Password...Locking password for user test1234.
            marking user vhosts / databases for suspension.
	    ..
	    ..
	    Completed, this account will be fully suspended in 2 minutes.
        </statusmsg>
    </result>
</suspendacct>'));

        $response = $this->request->send();
        $this->assertInstanceOf(SuspendResponse::class, $response);
        $this->assertTrue($response->isSuccessful());
        $this->assertNull($response->getStatus());
        $this->assertNull($response->getVpUsername());
        $this->assertNull($response->getReason());

        $this->assertValidPostCall('suspendacct');
    }

    public function testSendFailed()
    {
        $this->mockHandler->append(new Response(200, [], 'The suspension reason is to short, please give a reason for suspension.  .  '));

        $response = $this->request->send();
        $this->assertInstanceOf(SuspendResponse::class, $response);
        $this->assertFalse($response->isSuccessful());
        $this->assertEquals('The suspension reason is to short, please give a reason for suspension.  .', $response->getMessage());
        $this->assertNull($response->getStatus());
        $this->assertNull($response->getVpUsername());
        $this->assertNull($response->getReason());

        $this->assertValidPostCall('suspendacct');
    }

    public function testSendFailedNotActive()
    {
        $this->mockHandler->append(new Response(200, [], '<suspendacct>
    <result>
        <status>0</status>
        <statusmsg>
	This account is not active so can not be suspended  ( vPuser : test_1234578 ,  status : x , reason : RES_CLOSE : test suspension ) ..  
        </statusmsg>
    </result>
</suspendacct>'));

        $response = $this->request->send();
        $this->assertInstanceOf(SuspendResponse::class, $response);
        $this->assertFalse($response->isSuccessful());
        $this->assertEquals('This account is not active so can not be suspended  ( vPuser : test_1234578 ,  status : x , reason : RES_CLOSE : test suspension ) ..', $response->getMessage());
        $this->assertEquals('x', $response->getStatus());
        $this->assertEquals('test_1234578', $response->getVpUsername());
        $this->assertEquals('RES_CLOSE : test suspension', $response->getReason());

        $this->assertValidPostCall('suspendacct');
    }

    public function testSendFailedDeleted()
    {
        $this->mockHandler->append(new Response(200, [], '<suspendacct>
    <result>
        <status>0</status>
        <statusmsg>
	This account is not active so can not be suspended  ( vPuser :  ,  status :  , reason :  ) ..  No account mathcing this username test1234 could be found.  .  
        </statusmsg>
    </result>
</suspendacct>'));

        $response = $this->request->send();
        $this->assertInstanceOf(SuspendResponse::class, $response);
        $this->assertFalse($response->isSuccessful());
        $this->assertEquals('This account is not active so can not be suspended  ( vPuser :  ,  status :  , reason :  ) ..  No account mathcing this username test1234 could be found.  .', $response->getMessage());
        $this->assertEquals('', $response->getStatus());
        $this->assertEquals('', $response->getVpUsername());
        $this->assertEquals('', $response->getReason());

        $this->assertValidPostCall('suspendacct');
    }
}