<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class LogoutController extends AbstractController
{
    #[Route('/api/logout', name: 'api_logout', methods: ['POST'])]
    public function __invoke(): JsonResponse
    {
        $response = new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);

        $response->headers->setCookie(
            Cookie::create('jwt')
                ->withValue('')
                ->withExpires(0)
                ->withPath('/')
                ->withSecure(true)
                ->withHttpOnly(true)
                ->withSameSite('strict')
        );

        return $response;
    }
}