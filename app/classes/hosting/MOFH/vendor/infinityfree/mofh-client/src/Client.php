<?php

namespace InfinityFree\MofhClient;

use GuzzleHttp\Client as Guzzle;
use InfinityFree\MofhClient\Message\AbstractRequest;
use InfinityFree\MofhClient\Message\AvailabilityRequest;
use InfinityFree\MofhClient\Message\CreateAccountRequest;
use InfinityFree\MofhClient\Message\GetDomainUserRequest;
use InfinityFree\MofhClient\Message\GetUserDomainsRequest;
use InfinityFree\MofhClient\Message\PasswordRequest;
use InfinityFree\MofhClient\Message\SuspendRequest;
use InfinityFree\MofhClient\Message\UnsuspendRequest;
use RuntimeException;

class Client
{
    /**
     * @var Client
     */
    protected $httpClient;

    /**
     * @var array
     */
    protected $parameters;

    /**
     * Create a new gateway instance
     *
     * @param Guzzle|null $httpClient A HTTP client to make API calls with
     */
    public function __construct(Guzzle $httpClient = null)
    {
        $this->httpClient = $httpClient ?: $this->getDefaultHttpClient();
        $this->initialize();
    }

    /**
     * @return Guzzle
     */
    protected function getDefaultHttpClient()
    {
        return new Guzzle();
    }

    /**
     * Create a new client
     *
     * @param array $parameters See getDefaultParameters()
     * @return Client
     */
    public static function create(array $parameters = [])
    {
        $client = new self();
        $client->initialize($parameters);
        return $client;
    }

    /**
     * Get the available parameters and their default settings
     *
     * @return array
     */
    public function getDefaultParameters()
    {
        return [
            'apiUsername' => '',
            'apiPassword' => '',
            'apiUrl' => 'https://panel.myownfreehost.net:2087/xml-api/',
            'plan' => '',
        ];
    }

    /**
     * Initialize this gateway with default parameters
     *
     * @param  array $parameters
     * @return $this
     */
    public function initialize(array $parameters = array())
    {
        $this->parameters = $parameters;

        // set default parameters
        foreach (array_replace($this->getDefaultParameters(), $parameters) as $key => $value) {
            $this->setParameter($key, $value);
        }

        return $this;
    }

    /**
     * Get a single parameter.
     *
     * @param string $key The parameter key
     * @return mixed
     */
    protected function getParameter($key)
    {
        if (isset($this->parameters[$key])) {
            return $this->parameters[$key];
        } else {
            return null;
        }
    }

    /**
     * Set a single parameter
     *
     * @param string $key The parameter key
     * @param mixed $value The value to set
     * @return $this
     * @throws RuntimeException if a request parameter is modified after the request has been sent.
     */
    protected function setParameter($key, $value)
    {
        $this->parameters[$key] = $value;

        return $this;
    }

    public function setApiUsername($username)
    {
        return $this->setParameter('apiUsername', $username);
    }

    public function getApiUsername()
    {
        return $this->getParameter('apiUsername');
    }

    public function setApiPassword($password)
    {
        return $this->setParameter('apiPassword', $password);
    }

    public function getApiPassword()
    {
        return $this->getParameter('apiPassword');
    }

    public function setPlan($plan)
    {
        return $this->setParameter('plan', $plan);
    }

    public function getPlan()
    {
        return $this->getParameter('plan');
    }

    public function setApiUrl($url)
    {
        return $this->setParameter('apiUrl', $url);
    }

    public function getApiUrl()
    {
        return $this->getParameter('apiUrl');
    }

    /**
     * Create and initialize a request object
     *
     * @see AbstractRequest
     * @param string $class The request class name
     * @param array $parameters
     * @return AbstractRequest
     */
    protected function createRequest($class, array $parameters)
    {
        $obj = new $class($this->httpClient);
        return $obj->initialize(array_replace($this->parameters, $parameters));
    }

    /**
     * Create a new hosting account
     *
     * Parameters:
     * - username: A custom username, max. 8 characters of letters and numbers
     * - password: The FTP/control panel/database password for the account
     * - email: The contact e-mail address of the owner
     * - domain: The primary domain name of the account
     * - plan: The hosting plan to create the account on
     *
     * @param array $parameters
     * @return CreateAccountRequest
     */
    public function createAccount(array $parameters = array())
    {
        return $this->createRequest(CreateAccountRequest::class, $parameters);
    }

    /**
     * Suspend an account on MOFH
     *
     * Parameters:
     * - username: The custom username
     * - reason: The reason why the account was suspended
     * - linked: If true, related accounts will be suspended as well
     *
     * @param array $parameters
     * @return SuspendRequest
     */
    public function suspend(array $parameters = array())
    {
        return $this->createRequest(SuspendRequest::class, $parameters);
    }

    /**
     * Unsuspend the account with the given username at MOFH
     *
     * Parameters:
     * - username: The custom username
     *
     * @param array $parameters
     * @return UnsuspendRequest
     */
    public function unsuspend(array $parameters = array())
    {
        return $this->createRequest(UnsuspendRequest::class, $parameters);
    }

    /**
     * Change the password of an (active) account
     *
     * Parameters:
     * - username: The custom username
     * - password: The new password
     *
     * @param array $parameters
     * @return PasswordRequest
     */
    public function password(array $parameters = array())
    {
        return $this->createRequest(PasswordRequest::class, $parameters);
    }

    /**
     * Check whether a domain is available at MOFH
     *
     * Parameters:
     * - domain: The domain name or subdomain to check
     *
     * @param array $parameters
     * @return AvailabilityRequest
     */
    public function availability(array $parameters = array())
    {
        return $this->createRequest(AvailabilityRequest::class, $parameters);
    }

    /**
     * Get the domains belonging to an account.
     *
     * Parameters:
     * - username
     *
     * @param array $parameters
     * @return GetUserDomainsRequest
     */
    public function getUserDomains(array $parameters = array())
    {
        return $this->createRequest(GetUserDomainsRequest::class, $parameters);
    }

    /**
     * Get the user details corresponding to a domain name.
     *
     * Parameters:
     * - domain
     *
     * @param array $parameters
     * @return GetDomainUserRequest
     */
    public function getDomainUser(array $parameters = array())
    {
        return $this->createRequest(GetDomainUserRequest::class, $parameters);
    }
}
