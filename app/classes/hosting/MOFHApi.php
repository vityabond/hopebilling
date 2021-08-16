<?php
namespace hosting;

require __DIR__ . '/MOFH/vendor/autoload.php';

use \InfinityFree\MofhClient\Client;


class MOFHApi implements IHostingAPI
{
    private $response;
    private $execError = null;
    private $_error;
	private $client;

    public function __construct($server)
    {
		$this->client = Client::create([
		'apiUsername' => $server->login,
		'apiPassword' => $server->pass,
		'apiUrl' => $server->host . '/xml-api/'
		]);
		fwrite(
			fopen(__DIR__ . '/MOFH/log.html', 'a+'), 
			date('d.m.Y, H:i:s') .'Wywołanie API <br />'
			);
		
    }
	

    public function getErrorDetails(){
        return $this->_error;
    }
    public function changeUserPassword($user, $password){
        $this->query .= 'passwd?'. http_build_query(array('user' => $user, 'password' => $password));
        if ($this->exec()) {
            print_r($this->object);
            if ($this->object->metadata->result) {
                return HostingAPI::ANSWER_OK;
            }

            if (preg_match('/(.*) passwords must be at least (.*)/', $this->object->metadata->reason)) {
                return HostingAPI::ANSWER_USER_PASSWORD_NOT_VALID;
            }
            if (preg_match('/(.*) the user (.*) does not exist./', $this->object->metadata->reason)) {
                return HostingAPI::ANSWER_USER_NOT_EXIST;
            }
            if (preg_match("/(.*) the password you selected cannot be used (.*)/", $this->object->metadata->reason)) {
                return HostingAPI::ANSWER_USER_PASSWORD_NOT_VALID;
            }

            return HostingAPI::ANSWER_SYSTEM_ERROR;

        } else {
            return $this->execError;
        }
    }

    public function userExist($user)
    {
        $this->query .= 'accountsummary?' . http_build_query(array('user' => $user));

        if ($this->exec()) {
            if ($this->object->metadata->result) {
                return HostingAPI::ANSWER_USER_EXIST;
            } else {
                return HostingAPI::ANSWER_USER_NOT_EXIST;
            }
        } else {
            return $this->execError;
        }
    }

    public function planExist($name)
    {

		$json = file_get_contents(__DIR__ . '/MOFH/plans.json');
		file_put_contents(__DIR__ . '/json.txt', print_r('JSON : ' . $json, true));
		$decode = json_decode($json, true);
		
		if (isset($decode[$name])){
			return HostingAPI::ANSWER_PLAN_EXIST;
		} else {
			return HostingAPI::ANSWER_PLAN_NOT_EXIST;
		}

    }

    public function createUser($data)
    {

		// Create a request object to create the request.
		$request = $this->client->createAccount([
			'username' => $data['username'], // A unique, 8 character identifier of the account.
			'password' => $data['password'], // A password to login to the control panel, FTP and databases.
			'domain' => $data['domain'], // Can be a subdomain or a custom domain.
			'email' => $data['email'], // The email address of the user.
			'plan' => $data['package'], // Optional, you can submit a hosting plan here or with the Client instantiation.
		]);

		// Send the API request and keep the response.
		$this->response = $request->send();
		

		// Check whether the request was successful.
		if ($this->response->isSuccessful()) {
			return HostingAPI::ANSWER_OK;
			file_put_contents(__DIR__ . '/userCreate_suc.txt', print_r('Twoje dane logowania: ' . $this->response->getVpUsername(), true));
		} else {
			
			if (preg_match("/The username (.*) appears to be allready created/", $this->response->getMessage())) {
				return HostingAPI::ANSWER_USER_ALREADY_EXIST;
				
			}
			
			if (preg_match("/The domain name (.*) is allready added to a hosting account/", $this->response->getMessage())) {
				return HostingAPI::ANSWER_DOMAIN_ALREADY_EXIST;
				
			}
			
			if (preg_match("/The username is invalid (.*)/", $this->response->getMessage())) {
				return HostingAPI::ANSWER_USER_NAME_NOT_VALID;
				
			}
			
			if (preg_match("/The name servers for (.*) are not set to valid name servers./", $this->response->getMessage())) {

				return HostingAPI::ANSWER_SYSTEM_ERROR; 
			}
							fwrite(
					fopen(__DIR__ . '/MOFH/log.html', 'a+'), 
					date('d.m.Y, H:i:s') .'USER CREATE: Błąd: Brak NS w domenie' . $this->response->getMessage() .  '<br />'
				);
				
		}
		
		return HostingAPI::ANSWER_SYSTEM_ERROR;
	}

    public function suspendUser($user)
    {
		
		fwrite(
			fopen(__DIR__ . '/MOFH/log.html', 'a+'), 
			date('d.m.Y, H:i:s') .'USER SUSPEND:' . $user .  '<br />'
		);
		
		// Create a request object to create the request.
		$request = $this->client->suspend([
			'username' => $user, // A unique, 8 character identifier of the account.
			'reason' => 'test' // A password to login to the control panel, FTP and databases.
		]);

		// Send the API request and keep the response.
		$this->response = $request->send();
		#file_put_contents(__DIR__ . '/request.txt', print_r('Zapytanie:  ' . $request->send(), true));

		// Check whether the request was successful.
		if ($this->response->isSuccessful()) {
			return HostingAPI::ANSWER_OK;
			file_put_contents(__DIR__ . '/suc.txt', print_r('Twoje dane logowania: ' . $this->response->getVpUsername(), true));
			file_put_contents(__DIR__ . '/resp.txt', print_r('Client' . $this->response->getMessage(), true));
		} else {
			return HostingAPI::ANSWER_SYSTEM_ERROR;
			file_put_contents(__DIR__ . '/suspend_err.txt', print_r('Nie można zrobić konta: ' . $this->response->getMessage(), true));
		}
		
    }

    public function unsuspendUser($user)
    {
		file_put_contents(__DIR__ . '/user.txt', print_r('Zapytanie:  ' . $user, true));
		
				// Create a request object to create the request.
		$request = $this->client->unsuspend([
			'username' => $user
		]);

		// Send the API request and keep the response.
		$this->response = $request->send();
		#file_put_contents(__DIR__ . '/request.txt', print_r('Zapytanie:  ' . $request->send(), true));

		// Check whether the request was successful.
		if ($this->response->isSuccessful()) {
			return HostingAPI::ANSWER_OK;
			file_put_contents(__DIR__ . '/suc.txt', print_r('Twoje dane logowania: ' . $this->response->getVpUsername(), true));
			file_put_contents(__DIR__ . '/resp.txt', print_r('Client' . $this->response->getMessage(), true));
		} else {
			return HostingAPI::ANSWER_SYSTEM_ERROR;
			file_put_contents(__DIR__ . '/unsuspend_err.txt', print_r('Nie można zrobić konta: ' . $this->response->getMessage(), true));
		
		}
		
    }


    public function checkConnection()
    {
        #$this->query .= '?gethostname';
        #if ($this->exec()) {
            return HostingAPI::ANSWER_OK;
        #}
        //iserror
        #return $this->execError;
    }


    public function removeUser($user)
    {

        #return HostingAPI::ANSWER_OK;
         
        return HostingAPI::ANSWER_SYSTEM_ERROR;


        #return $this->execError;
    }


    public function changePlan($user, $plan)
    {
        $this->query .= 'changepackage?' . http_build_query(array('user' => $user, 'pkg' => $plan));
        if ($this->exec()) {
            if ($this->object->metadata->result) {
                return HostingAPI::ANSWER_OK;
            }

            if (preg_match('/Sorry the user (.*) does not exist/', $this->object->metadata->reason)) {
                return HostingAPI::ANSWER_USER_NOT_EXIST;
            }
            if (preg_match('/Specified package (.*) does not exist/', $this->object->metadata->reason)) {
                return HostingAPI::ANSWER_PLAN_NOT_EXIST;
            }

            return HostingAPI::ANSWER_SYSTEM_ERROR;
        } else {
            return $this->execError;
        }
    }

    public function getPlans()
    {
		$json = file_get_contents(__DIR__ . '/MOFH/plans.json');
		file_put_contents(__DIR__ . '/json.txt', print_r('JSON : ' . $json, true));
		$decode = json_decode($json);

		$plans = array();

		foreach ($decode as $name => $options) {
			$plans[$name] = $name;
		}

        return $plans;

    }



    public function setTimeout($time)
    {
        curl_setopt($this->curl, CURLOPT_CONNECTTIMEOUT_MS, $time);
    }





}