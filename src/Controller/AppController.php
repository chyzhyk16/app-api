<?php

namespace App\Controller;


use App\Service\Login;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class AppController extends AbstractController
{


    /**
     * @var HttpClientInterface
     */
    private $client;
    /**
     * @var Login
     */
    private $login;

    public function __construct(HttpClientInterface $client, Login $login)
    {
        $this->client = $client;
        $this->login = $login;
    }

    /**
     * @Route("/btcRate", name="btcRate")
     */
    public function btcRate(): Response
    {
        if ($this->login->getStatus() == true) {
            $response = $this->sendRequest('GET', 'http://localhost:8001/btcRate');
            return $this->json($response);
        } else {
            return $this->json(['error' => 'You are not logged in']);
        }
    }


    /**
     * @Route("/usr/login", name="login")
     */
    public function usrLogin(Request $request): Response
    {
        $request = $this->transformJsonBody($request);
        if ($this->checkUserParams($request)) {
            return $this->json(['error' => 'Bad request params']);
        }
        $response =  $this->sendRequest('POST', 'http://localhost:8002/find', [
            'json' => ['email' => $request->get('email'), 'password' => $request->get('password')],
        ]);
        $this->login->login($response);
        return $this->json(['login_status' => $this->login->getStatus()]);
    }

    /**
     * @Route("/usr/create", name="usrCreate")
     */
    public function usrCreate(Request $request): Response
    {
        $request = $this->transformJsonBody($request);
        if ($this->checkUserParams($request)) {
            return $this->json(['error' => 'Bad request params']);
        }
        $response =  $this->sendRequest('POST', 'http://localhost:8002/create', [
            'json' => ['email' => $request->get('email'), 'password' => $request->get('password')],
        ]);
        return $this->json($response);
    }

    public function checkUserParams($request)
    {
        return !$request->get('email') || !$request->request->get('password');
    }

    private function sendRequest(string $method, string $url, array $options = [])
    {
        try {
            $response = $this->client->request($method, $url, $options)->getContent();
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
        return json_decode($response, true);

    }

    private function transformJsonBody(Request $request): Request
    {
        $data = json_decode($request->getContent(), true);
        if ($data === null) {
            return $request;
        }

        $request->request->replace($data);

        return $request;
    }

}
