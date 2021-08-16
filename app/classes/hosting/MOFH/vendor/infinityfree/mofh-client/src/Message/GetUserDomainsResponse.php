<?php

namespace InfinityFree\MofhClient\Message;

class GetUserDomainsResponse extends AbstractResponse
{
    public function parseResponse()
    {
        $this->data = (string)$this->response->getBody();
    }

    /**
     * Get the error message, if defined.
     *
     * @return array|null|string
     */
    public function getMessage()
    {
        return $this->isSuccessful() ? null : $this->getData();
    }

    /**
     * Check if the request was successful.
     *
     * @return bool
     */
    public function isSuccessful()
    {
        return strpos($this->getData(), '[') === 0 || trim($this->getData()) == 'null';
    }

    /**
     * Get the list of domains on the account.
     *
     * @return array
     */
    public function getDomains()
    {
        if ($this->isSuccessful()) {
            if (trim($this->getData()) == 'null') {
                return [];
            } else {
                return array_map(function ($item) {
                    return $item[1];
                }, json_decode($this->getData(), true));
            }
        } else {
            return [];
        }
    }

    /**
     * Get the status of the account.
     *
     * @return string|null
     */
    public function getStatus()
    {
        if ($this->isSuccessful()) {
            $data = json_decode($this->getData(), true);
            
            if ($data == null) {
                return null;
            }

            $statuses = array_unique(array_map(function ($item) {
                return $item[0];
            }, $data));

            if (count($statuses) == 1) {
                return $statuses[0];
            } elseif (count($statuses) > 1) {
                throw new \RuntimeException('The account domains have different statuses: ' . $data);
            } else {
                return null;
            }
        } else {
            return null;
        }
    }
}
