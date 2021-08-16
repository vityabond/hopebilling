<?php

namespace InfinityFree\MofhClient\Tests\Message;

use GuzzleHttp\Psr7\Response;
use InfinityFree\MofhClient\Message\PasswordRequest;
use InfinityFree\MofhClient\Message\PasswordResponse;

class PasswordRequestTest extends RequestTestCase
{
    /**
     * @var array
     */
    public $requestData;

    public function setUp() : void
    {
        parent::setUp();
        $this->request = new PasswordRequest($this->guzzle);

        $this->requestData = array_merge($this->defaultParameters, [
            'username' => $this->faker->userName,
            'password' => $this->faker->password,
        ]);
        $this->request->initialize($this->requestData);
    }

    public function testGetData()
    {
        $data = $this->request->getData();

        $this->assertEquals([
            'user' => $this->requestData['username'],
            'pass' => $this->requestData['password'],
        ], $data);
    }

    public function testSendSuccessful()
    {
        $this->mockHandler->append(new Response(200, [], '
<passwd>
        <passwd>
                <rawout>        
                 Changing password for test1234
                 Password for test1234 has been changed 
                 Updating ftp passwords for test1234
                 Ftp password files updated. 
                 Ftp vhost passwords synced
                </rawout>
                 <services>
                      <app>system</app>
                 </services>
                 <services>
                      <app>ftp</app>
                 </services>
                 <services>
                      <app>mail</app>
                 </services>
                 <services>
                       <app>mySQL</app>
                 </services>
                <status>1</status>
                <statusmsg>Password changed for user vns6ohth</statusmsg>
        </passwd>
</passwd>
	'));

        $response = $this->request->send();
        $this->assertInstanceOf(PasswordResponse::class, $response);
        $this->assertTrue($response->isSuccessful());
        $this->assertNull($response->getStatus());

        $this->assertValidPostCall('passwd');
    }

    public function testSendSuccessAnErrorOccurred()
    {
        $this->mockHandler->append(new Response(200, [], '<passwd>
        <passwd>
                <rawout>
                 Changing password for vns6ohth
                 Password for vns6ohth has been changed
                 Updating ftp passwords for vns6ohth
                 Ftp password files updated.
                 Ftp vhost passwords synced
                </rawout>
                 <services>
                      <app>system</app>
                 </services>
                 <services>
                      <app>ftp</app>
                 </services>
                 <services>
                      <app>mail</app>
                 </services>
                 <services>
                       <app>mySQL</app>
                 </services>
                <status>0</status>
                <statusmsg>An error occured changing this password.</statusmsg>
        </passwd>
</passwd>'));

        $response = $this->request->send();
        $this->assertInstanceOf(PasswordResponse::class, $response);
        $this->assertTrue($response->isSuccessful());
        $this->assertNull($response->getStatus());

        $this->assertValidPostCall('passwd');
    }

    public function testSendFailed()
    {
        $this->mockHandler->append(new Response(200, [], 'The choosen password is to short.  .  '));

        $response = $this->request->send();
        $this->assertInstanceOf(PasswordResponse::class, $response);
        $this->assertFalse($response->isSuccessful());
        $this->assertEquals('The choosen password is to short.  .', $response->getMessage());
        $this->assertNull($response->getStatus());

        $this->assertValidPostCall('passwd');
    }

    public function testSendFailedNotActive()
    {
        $this->mockHandler->append(new Response(200, [], '<passwd>
        <passwd>
                <rawout>
                 Changing password for test1234
                 Password for test1234 has been changed
                 Updating ftp passwords for test1234
                 Ftp password files updated.
                 Ftp vhost passwords synced
                </rawout>
                 <services>
                      <app>system</app>
                 </services>
                 <services>
                      <app>ftp</app>
                 </services>
                 <services>
                      <app>mail</app>
                 </services>
                 <services>
                       <app>mySQL</app>
                 </services>
                <status>0</status>
                <statusmsg>This account  currently not active, the account must be active to change the password (x). .   </statusmsg>
        </passwd>
</passwd>'));

        $response = $this->request->send();
        $this->assertInstanceOf(PasswordResponse::class, $response);
        $this->assertFalse($response->isSuccessful());
        $this->assertEquals('This account  currently not active, the account must be active to change the password (x). .', $response->getMessage());
        $this->assertEquals('x', $response->getStatus());

        $this->assertValidPostCall('passwd');
    }

    public function testSendFailedDeleted()
    {
        $this->mockHandler->append(new Response(200, [], ''));

        $response = $this->request->send();
        $this->assertInstanceOf(PasswordResponse::class, $response);
        $this->assertFalse($response->isSuccessful());
        $this->assertEquals('', $response->getMessage());
        $this->assertEquals('', $response->getStatus());

        $this->assertValidPostCall('passwd');
    }
}