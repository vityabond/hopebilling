<?php

namespace InfinityFree\MofhClient\Message;

class SuspendResponse extends AbstractResponse
{
    protected $info;

    /**
     * Parse the additional parameters present in the response string.
     */
    protected function parseResponse()
    {
        parent::parseResponse();

        if (!$this->isSuccessful()) {
            $matches = [];
            if (preg_match('/account is not active so can not be suspended\s+\((.+)\)/', $this->getMessage(), $matches)) {
                list($fullMatch, $infoString) = $matches;
                $attributes = explode(',', $infoString, 3);
                $this->info = [];

                foreach ($attributes as $attribute) {
                    list($key, $value) = explode(':', $attribute, 2);
                    $this->info[trim($key)] = trim($value);
                }
            }
        }
    }

    /**
     * Get the status of the account if it's not active.
     *
     * The result is one of the following chars:
     * - x: suspended
     * - r: reactivating
     * - c: closing
     *
     * @return string|null
     */
    public function getStatus()
    {
        return isset($this->info['status']) ? $this->info['status'] : null;
    }

    /**
     * Get the username of the account if it's not active.
     *
     * @return string|null
     */
    public function getVpUsername()
    {
        return isset($this->info['vPuser']) ? $this->info['vPuser'] : null;
    }

    /**
     * Get the suspension reason of the account if it's not active.
     *
     * @return string|null
     */
    public function getReason()
    {
        return isset($this->info['reason']) ? $this->info['reason'] : null;
    }
}