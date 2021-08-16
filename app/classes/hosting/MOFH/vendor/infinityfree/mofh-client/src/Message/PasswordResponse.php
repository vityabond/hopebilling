<?php

namespace InfinityFree\MofhClient\Message;

class PasswordResponse extends AbstractResponse
{
    protected $status;

    protected function parseResponse()
    {
        parent::parseResponse();

        if (!$this->isSuccessful()) {
            $matches = [];
            if (preg_match('/the account must be active to change the password\s+\((.+)\)/', $this->getMessage(), $matches)) {
                $this->status = $matches[1];
            }
        }
    }

    /**
     * Response Message
     *
     * @return null|string A response message from the payment gateway
     */
    public function getMessage()
    {
        if ($this->getData() && isset($this->getData()['passwd']['statusmsg'])) {
            return trim($this->getData()['passwd']['statusmsg']);
        } else {
            return trim($this->response->getBody());
        }
    }

    /**
     * Whether the action was successful
     *
     * @return bool
     */
    public function isSuccessful()
    {
        if ($this->getData() && isset($this->getData()['passwd']['status']) && $this->getData()['passwd']['status'] == 1) {
            return true; // The password call was successful
        } elseif (strpos($this->getMessage(), 'error occured changing this password') !== false) {
            return true; // The password is identical (which is technically identical to be being changed successfully)
        } else {
            return false;
        }
    }

    /**
     * Get the status of the account if the account is not active.
     *
     * The result is one of the following chars:
     * - x: suspended
     * - r: reactivating
     * - c: closing
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }
}