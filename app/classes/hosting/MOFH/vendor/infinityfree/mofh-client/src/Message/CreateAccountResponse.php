<?php

namespace InfinityFree\MofhClient\Message;

class CreateAccountResponse extends AbstractResponse
{
    /**
     * Get the VistaPanel username of the account (like test_123456789)
     *
     * @return null|string
     */
    public function getVpUsername()
    {
        if (isset($this->getData()['result']['options']['vpusername'])) {
            return $this->getData()['result']['options']['vpusername'];
        } else {
            return null;
        }
    }
}