<?php

namespace App\Controller;

use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

class ApiLoginController extends AbstractController
{
    #[Route('/api/login', name: 'api_login', methods: ['POST'])]
    public function login(UserInterface $user, JWTTokenManagerInterface $jwtManager): JsonResponse
    {
        // Generate JWT token for the authenticated user
        return new JsonResponse([
            'token' => $jwtManager->create($user),
        ]);
    }
}
