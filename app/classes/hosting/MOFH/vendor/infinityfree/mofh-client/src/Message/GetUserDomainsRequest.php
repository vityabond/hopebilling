<?php

namespace InfinityFree\MofhClient\Message;

/**
 * @method GetUserDomainsResponse send() Send the request.
 */
class GetUserDomainsRequest extends AbstractRequest
{
    public function sendData($data)
    {
        $httpResponse = $this->sendRequest('getuserdomains', $data);

        return $this->response = new GetUserDomainsResponse($this, $httpResponse);
    }

    public function getData()
    {
        $this->validate('apiUsername', 'apiPassword', 'apiUrl', 'username');

        return [
            'api_user' => $this->getApiUsername(),
            'api_key' => $this->getApiPassword(),
            'username' => $this->getUsername(),
        ];
    }

    protected function sendRequest($function, array $parameters)
    {
        return $this->httpClient->get($this->getApiUrl() . $function, [
            'query' => $parameters,
            'verify' => false,
        ]);
    }
}
