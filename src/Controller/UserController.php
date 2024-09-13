<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/users')]
class UserController extends AbstractController
{
    #[Route('', name: 'app_user_index', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Returns the list of users',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: User::class, groups: ['full']))
        )
    )]
    public function getUsers(EntityManagerInterface $entityManagerInterface): JsonResponse
    {
        $users = $entityManagerInterface->getRepository(User::class)->findAll();
        $serializedUsers = [];
        foreach ($users as $user) {
            $serializedUsers[] = [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'username' => $user->getUsername(),
                'password' => $user->getPassword(),
                'roles' => $user->getRoles()
            ];
        }
        return new JsonResponse($serializedUsers);
    }

    #[Route('/{id}', name: 'app_user_show', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Returns the matching user',
        content: new Model(type: User::class, groups: ['full'])
    )]
    public function getUserById($id, EntityManagerInterface $entityManagerInterface): JsonResponse
    {
        $user = $entityManagerInterface->getRepository(User::class)->find($id);
        $serializedUser = [
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'username' => $user->getUsername(),
            'password' =>$user->getPassword(),
            'roles' => $user->getRoles()
        ];

        return new JsonResponse($serializedUser);
    }

    #[Route('', name: 'app_user_create', methods: ['POST'])]
    #[OA\Response(
        response: 201,
        description: 'Returns the inserted entity',
        content: new Model(type: User::class, groups: ['full'])
    )]
    public function createUser(Request $request, EntityManagerInterface $entityManagerInterface, UserPasswordHasherInterface $passwordHasher, ValidatorInterface $validator): JsonResponse
    {
        $user = new User();

        $data = $request->toArray();
        
        if(isset($data['email'], $data['password'])) {
            $user->setEmail($data['email']);
        }
        if(isset($data['username'])) {
            $user->setUsername($data['username']);
        }
        if(isset($data['password'])) {
            $plainPassword = $data['password'];
            $encodedPassword = $passwordHasher->hashPassword($user, $plainPassword);
            $user->setPassword($encodedPassword);
        }

        // check if roles are provided
        if (! empty($data['roles'])) {
            // set found roles on the user
            $user->setRoles($data['roles'], $entityManagerInterface);
        }

        $errors = $validator->validate($user);
        if (count($errors) > 0) {
            // handle validation errors
            return new JsonResponse(['errors' => (string) $errors], Response::HTTP_BAD_REQUEST);
        }

        try {
            $entityManagerInterface->persist($user);
            $entityManagerInterface->flush();

            $serializedUser = [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'username' => $user->getUsername(),
                'password' =>$user->getPassword(),
                'roles' => $user->getRoles()
            ];

            return new JsonResponse($serializedUser, Response::HTTP_CREATED);
        } catch (UniqueConstraintViolationException $e) {
            return new JsonResponse(['error' => 'An user with that username or email already exists.'], Response::HTTP_CONFLICT);
        } catch (Exception $e) {
            return new JsonResponse(['error' => 'Failed to create user'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/{id}', name: 'app_user_update', methods: ['PUT'])]
    #[OA\Response(
        response: 200,
        description: 'Returns the updated entity',
        content: new Model(type: User::class, groups: ['full'])
    )]
    public function updateUser(int $id, Request $request, EntityManagerInterface $entityManagerInterface, UserPasswordHasherInterface $passwordHasher): JsonResponse
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

        // check if roles are provided
        if (! empty($data['roles'])) {
            // set found roles on the user
            $user->setRoles($data['roles'], $entityManagerInterface);
        }
        

        try {
            $entityManagerInterface->persist($user);
            $entityManagerInterface->flush();

            $serializedUser = [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'username' => $user->getUsername(),
                'password' =>$user->getPassword(),
                'roles' => $user->getRoles()
            ];

            return new JsonResponse($serializedUser, Response::HTTP_OK);
        } catch (UniqueConstraintViolationException $e) {
            return new JsonResponse(['error' => 'An user with that username or email already exists.'], Response::HTTP_CONFLICT);
        } catch (Exception $e) {
            return new JsonResponse(['error' => 'Failed to update user'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/{id}', name: 'app_user_delete', methods: ['DELETE'])]
    #[IsGranted('PERMISSION_DELETE')]
    #[OA\Response(
        response: 204,
        description: 'Deletes the entity from its ID',
        content: null
    )]
    public function deleteUser(int $id, EntityManagerInterface $entityManagerInterface): JsonResponse
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
