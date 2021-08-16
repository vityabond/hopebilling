<?php

namespace InfinityFree\MofhClient\Message;

abstract class AbstractResponse
{
    /**
     * The embodied request object.
     *
     * @var AbstractRequest
     */
    protected $request;

    /**
     * The data contained in the response.
     *
     * @var mixed
     */
    protected $data;

    /**
     * The response interface
     *
     * @var mixed
     */
    protected $response;

    /**
     * Constructor
     *
     * @param mixed $request the initiating request.
     * @param mixed $response
     */
    public function __construct($request, $response)
    {
        $this->request = $request;
        $this->response = $response;

        $this->parseResponse();
    }

    /**
     * Parse the response after it has been received.
     */
    protected function parseResponse()
    {
        $data = (string)$this->response->getBody();

        if (strpos(trim($data), '<') !== 0) {
            $this->data = null;
        } else {
            $this->data = $this->xmlToArray((array)simplexml_load_string($data));
        }
    }

    /**
     * Get the response data.
     *
     * @return array|null
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Recursively convert a SimpleXMLElement array to regular arrays
     *
     * @param array $input
     * @return array
     */
    protected function xmlToArray($input)
    {
        foreach ($input as $key => $value) {
            if ($value instanceof \SimpleXMLElement) {
                $value = (array)$value;
            }

            if (is_array($value)) {
                $input[$key] = $this->xmlToArray($value);
            }
        }

        return $input;
    }

    /**
     * Get the error message from the response if the call failed.
     *
     * @return string
     */
    public function getMessage()
    {
        if ($this->getData() && isset($this->getData()['result']['statusmsg'])) {
            return trim($this->getData()['result']['statusmsg']);
        } else {
            return (string)trim($this->response->getBody());
        }
    }

    /**
     * Whether the action was successful
     *
     * @return bool
     */
    public function isSuccessful()
    {
        if ($this->getData() && isset($this->getData()['result']['status'])) {
            return $this->getData()['result']['status'] == 1;
        } else {
            return false;
        }
    }
}