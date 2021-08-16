<?php

namespace InfinityFree\MofhClient\Message;

/**
 * @method CreateAccountRequest send() Send the request.
 */
class CreateAccountRequest extends AbstractRequest
{
    public function getPassword()
    {
        return $this->getParameter('password');
    }

    public function setPassword($password)
    {
        return $this->setParameter('password', $password);
    }

    public function getEmail()
    {
        return $this->getParameter('email');
    }

    public function setEmail($email)
    {
        return $this->setParameter('email', $email);
    }

    public function getDomain()
    {
        return $this->getParameter('domain');
    }

    public function setDomain($domain)
    {
        return $this->setParameter('domain', $domain);
    }

    public function getPlan()
    {
        return $this->getParameter('plan');
    }

    public function setPlan($plan)
    {
        return $this->setParameter('plan', $plan);
    }

    public function sendData($data)
    {
        $httpResponse = $this->sendRequest('createacct', $data);

        return $this->response = new CreateAccountResponse($this, $httpResponse);
    }

    public function getData()
    {
        $this->validate('apiUsername', 'apiPassword', 'apiUrl', 'username', 'password', 'email', 'domain', 'plan');

        return [
            'username' => $this->getUsername(),
            'password' => $this->getPassword(),
            'contactemail' => $this->getEmail(),
            'domain' => $this->getDomain(),
            'plan' => $this->getPlan(),
        ];
    }
}