<?php

namespace App\Service;


use Symfony\Component\HttpFoundation\RequestStack;

class Login
{

    private $session;

    public function __construct(RequestStack $requestStack)
    {
        $this->session = $requestStack->getSession();
    }

    public function login(array $response)
    {
        if (array_key_exists('status', $response) && $response['status'] == true) {
            $this->session->set('user_logged', true);
        } else {
            $this->session->set('user_logged', false);
        }
    }

    public function getStatus()
    {
        return $this->session->get('user_logged');
    }
}