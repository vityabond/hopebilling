<?php

namespace InfinityFree\MofhClient\Message;

use GuzzleHttp\Client;
use InfinityFree\MofhClient\Exception\InvalidRequestException;
use RuntimeException;

abstract class AbstractRequest
{
    protected $httpClient;
    protected $parameters;
    protected $response;

    /**
     * Create a new Request
     *
     * @param Client $httpClient  A HTTP client to make API calls with
     */
    public function __construct(Client $httpClient)
    {
        $this->httpClient = $httpClient;
        $this->initialize();
    }

    /**
     * Initialize the object with parameters.
     *
     * If any unknown parameters passed, they will be ignored.
     *
     * @param array $parameters An associative array of parameters
     *
     * @return $this
     * @throws RuntimeException
     */
    public function initialize(array $parameters = array())
    {
        if (null !== $this->response) {
            throw new RuntimeException('Request cannot be modified after it has been sent!');
        }
        $this->parameters = $parameters;
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
     * @return AbstractRequest Provides a fluent interface
     * @throws RuntimeException if a request parameter is modified after the request has been sent.
     */
    protected function setParameter($key, $value)
    {
        if (null !== $this->response) {
            throw new RuntimeException('Request cannot be modified after it has been sent!');
        }

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

    public function setUsername($username)
    {
        return $this->setParameter('username', $username);
    }

    public function getUsername()
    {
        return $this->getParameter('username');
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
     * Validate the request.
     *
     * This method is called internally by gateways to avoid wasting time with an API call
     * when the request is clearly invalid.
     *
     * @param string ... a variable length list of required parameters
     * @throws InvalidRequestException
     */
    public function validate()
    {
        foreach (func_get_args() as $key) {
            $value = $this->getParameter($key);
            if (! isset($value)) {
                throw new InvalidRequestException("The {$key} parameter is required");
            }
        }
    }

    /**
     * Send the request.
     *
     * @return AbstractResponse
     */
    public function send()
    {
        $data = $this->getData();
        return $this->sendData($data);
    }

    /**
     * Send a POST query to the XML API
     *
     * @param string $function The MOFH API function name
     * @param array $parameters The API function arguments
     * @return \Psr\Http\Message\ResponseInterface
     */
    protected function sendRequest($function, array $parameters)
    {
        return $this->httpClient->post($this->getApiUrl() . $function, [
            'form_params' => $parameters,
            'auth' => [$this->getApiUsername(), $this->getApiPassword()],
            'verify' => false,
        ]);
    }

    abstract public function sendData($data);
    abstract public function getData();
}