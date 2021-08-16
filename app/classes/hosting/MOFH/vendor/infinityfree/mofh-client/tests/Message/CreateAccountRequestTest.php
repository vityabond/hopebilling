<?php

namespace InfinityFree\MofhClient\Tests\Message;

use GuzzleHttp\Psr7\Response;
use InfinityFree\MofhClient\Message\CreateAccountRequest;
use InfinityFree\MofhClient\Message\CreateAccountResponse;

class CreateAccountRequestTest extends RequestTestCase
{
    /**
     * @var array
     */
    public $requestData;

    public function setUp() : void
    {
        parent::setUp();
        $this->request = new CreateAccountRequest($this->guzzle);

        $this->requestData = array_merge($this->defaultParameters, [
            'username' => $this->faker->userName,
            'password' => $this->faker->password,
            'email' => $this->faker->email,
            'domain' => $this->faker->domainName,
            'plan' => $this->faker->word,
        ]);
        $this->request->initialize($this->requestData);
    }

    public function testGetData()
    {
        $data = $this->request->getData();

        $this->assertEquals([
            'username' => $this->requestData['username'],
            'password' => $this->requestData['password'],
            'contactemail' => $this->requestData['email'],
            'domain' => $this->requestData['domain'],
            'plan' => $this->requestData['plan'],
        ], $data);
    }

    public function testSendSuccessful()
    {
        $this->mockHandler->append(new Response(200, [], '<createacct>
     <result>        
          <options>
                  <ip>n</ip>
		  <vpusername>test_12345678</vpusername>
                  <nameserver>ns1.byet.org</nameserver>
                  <nameserver2>ns2.byet.org</nameserver2>
                  <nameserver3/>
                  <nameserver4/>
                  <nameservera/>
                  <nameservera2/>
                  <nameservera3/>
                  <nameservera4/>
                  <nameserverentry/>
                  <nameserverentry2/>
                  <nameserverentry3/>
                  <nameserverentry4/>
                  <package></package>
           </options>
           <rawout>
	   account added to queue to be added 
            </rawout>
            <status>1</status>
            <statusmsg>This account has been successfuly created</statusmsg>
     </result>
</createacct>'));

        $domain = $this->faker->domainName;
        $this->request->setDomain($domain);

        $response = $this->request->send();
        $this->assertInstanceOf(CreateAccountResponse::class, $response);
        $this->assertTrue($response->isSuccessful());
        $this->assertEquals('test_12345678', $response->getVpUsername());

        $this->assertValidPostCall('createacct');
    }

    public function testSendFailed()
    {
        $this->mockHandler->append(new Response(200, [], '<createacct>
     <result>
          <options>
                  <ip>n</ip>
                  <nameserver>ns1.byet.org</nameserver>
                  <nameserver2>ns2.byet.org</nameserver2>
                  <nameserver3/>
                  <nameserver4/>
                  <nameservera/>
                  <nameservera2/>
                  <nameservera3/>
                  <nameservera4/>
                  <nameserverentry/>
                  <nameserverentry2/>
                  <nameserverentry3/>
                  <nameserverentry4/>
                  <package></package>
           </options>
           <rawout>
           account added to queue to be added
            </rawout>
            <status>0</status>
            <statusmsg>The username test1234 appears to be allready created.  .  </statusmsg>
     </result>
</createacct>'));

        $domain = $this->faker->domainName;
        $this->request->setDomain($domain);

        $response = $this->request->send();
        $this->assertInstanceOf(CreateAccountResponse::class, $response);
        $this->assertFalse($response->isSuccessful());
        $this->assertEquals('The username test1234 appears to be allready created.  .', $response->getMessage());
        $this->assertNull($response->getVpUsername());

        $this->assertValidPostCall('createacct');
    }
}