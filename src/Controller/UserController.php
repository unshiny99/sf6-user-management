<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/users')]
class UserController extends AbstractController
{
    #[Route('', name: 'app_user_index', methods: ['GET'])]
    public function getUsers(EntityManagerInterface $entityManagerInterface): JsonResponse
    {
        $users = $entityManagerInterface->getRepository(User::class)->findAll();
        $serializedUsers = [];
        foreach ($users as $user) {
            $serializedUsers[] = [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'username' => $user->getUsername(),
                'password' =>$user->getPassword()
            ];
        }
        return new JsonResponse($serializedUsers);
    }

    #[Route('/{id}', name: 'app_user_show', methods: ['GET'])]
    public function getUserById($id, EntityManagerInterface $entityManagerInterface): JsonResponse
    {
        $user = $entityManagerInterface->getRepository(User::class)->find($id);
        $serializedUser = [
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'username' => $user->getUsername(),
            'password' =>$user->getPassword()
        ];

        return new JsonResponse($serializedUser);
    }

    #[Route('', name: 'app_user_create', methods: ['POST'])]
    public function createUser(Request $request, EntityManagerInterface $entityManagerInterface, UserPasswordHasherInterface $passwordHasher): JsonResponse
    {
        $user = new User();

        $data = $request->toArray();
        
        $user->setEmail($data['email']);
        $user->setUsername($data['username']);
        $plainPassword = $data['password'];
        $encodedPassword = $passwordHasher->hashPassword($user, $plainPassword);
        $user->setPassword($encodedPassword);

        try {
            $entityManagerInterface->persist($user);
            $entityManagerInterface->flush();

            $serializedUser = [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'username' => $user->getUsername(),
                'password' =>$user->getPassword()
            ];

            return new JsonResponse($serializedUser, Response::HTTP_CREATED);
        } catch (Exception $e) {
            return new JsonResponse(['error' => 'Failed to create user'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/{id}', name: 'app_user_update', methods: ['PUT'])]
    public function updateUser($id, Request $request, EntityManagerInterface $entityManagerInterface, UserPasswordHasherInterface $passwordHasher): JsonResponse
    {
        $user = $entityManagerInterface->getRepository(User::class)->find($id);

        if (! $user) {
            throw $this->createNotFoundException('User not found.');
        }

        $data = $request->toArray();
        
        $user->setEmail($data['email']);
        $user->setUsername($data['username']);
        $plainPassword = $data['password'];
        $encodedPassword = $passwordHasher->hashPassword($user, $plainPassword);
        $user->setPassword($encodedPassword);

        try {
            $entityManagerInterface->persist($user);
            $entityManagerInterface->flush();

            $serializedUser = [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'username' => $user->getUsername(),
                'password' =>$user->getPassword()
            ];

            return new JsonResponse($serializedUser, Response::HTTP_OK);
        } catch (Exception $e) {
            return new JsonResponse(['error' => 'Failed to update user'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/{id}', name: 'app_user_delete', methods: ['DELETE'])]
    public function deleteUser($id, EntityManagerInterface $entityManagerInterface): JsonResponse
    {
        $user = $entityManagerInterface->getRepository(User::class)->find($id);

        if (! $user) {
            return new JsonResponse(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        $entityManagerInterface->remove($user);
        $entityManagerInterface->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
