<?php

namespace InfinityFree\MofhClient\Message;

class AvailabilityResponse extends AbstractResponse
{

    public function parseResponse()
    {
        $this->data = (string)$this->response->getBody();
    }

    public function getMessage()
    {
        return $this->getData();
    }

    /**
     * Whether the domain name is available for registration.
     *
     * @return bool
     */
    public function isSuccessful()
    {
        return $this->data === '1';
    }
}