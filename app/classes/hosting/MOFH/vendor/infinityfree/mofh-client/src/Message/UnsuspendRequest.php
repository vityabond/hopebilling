<?php

namespace InfinityFree\MofhClient\Message;

/**
 * @method UnsuspendResponse send() Send the request.
 */
class UnsuspendRequest extends AbstractRequest
{
    public function getDomain()
    {
        return $this->getParameter('domain');
    }

    public function setDomain($domain)
    {
        return $this->setParameter('domain', $domain);
    }

    public function sendData($data)
    {
        $httpResponse = $this->sendRequest('unsuspendacct', $data);

        return $this->response = new UnsuspendResponse($this, $httpResponse);
    }

    public function getData()
    {
        $this->validate('apiUsername', 'apiPassword', 'apiUrl', 'username');

        return [
            'user' => $this->getUsername(),
        ];
    }
}