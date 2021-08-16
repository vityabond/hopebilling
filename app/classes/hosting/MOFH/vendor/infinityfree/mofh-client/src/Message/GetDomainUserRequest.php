<?php

namespace InfinityFree\MofhClient\Message;

/**
 * @method GetDomainUserResponse send() Send the request.
 */
class GetDomainUserRequest extends AbstractRequest
{
    public function sendData($data)
    {
        $httpResponse = $this->sendRequest('getdomainuser', $data);

        return $this->response = new GetDomainUserResponse($this, $httpResponse);
    }

    public function getData()
    {
        $this->validate('apiUsername', 'apiPassword', 'apiUrl', 'domain');

        return [
            'api_user' => $this->getApiUsername(),
            'api_key' => $this->getApiPassword(),
            'domain' => $this->getDomain(),
        ];
    }

    public function setDomain($domain)
    {
        return $this->setParameter('domain', $domain);
    }

    public function getDomain()
    {
        return $this->getParameter('domain');
    }

    protected function sendRequest($function, array $parameters)
    {
        return $this->httpClient->get($this->getApiUrl() . $function, [
            'query' => $parameters,
            'verify' => false,
        ]);
    }
}
