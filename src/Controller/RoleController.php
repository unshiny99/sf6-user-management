<?php

namespace App\Controller;

use App\Entity\Role;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/roles')]
class RoleController extends AbstractController
{
    #[Route('', name: 'app_role_index', methods: ['GET'])]
    public function getRoles(EntityManagerInterface $entityManagerInterface): JsonResponse
    {
        $roles = $entityManagerInterface->getRepository(Role::class)->findAll();
        // custom serializer
        $serializedRoles = [];
        foreach ($roles as $role) {
            $serializedRoles[] = [
                'id' => $role->getId(),
                'name' => $role->getName(),
                'permissions' => $role->getPermissions(),
            ];
        }
        return new JsonResponse($serializedRoles);
    }

    #[Route('/{id}', name: 'app_role_show', methods: ['GET'])]
    public function getRoleById($id, EntityManagerInterface $entityManagerInterface): JsonResponse
    {
        $role = $entityManagerInterface->getRepository(role::class)->find($id);

        if(! $role) {
            return new JsonResponse(['error' => 'Role not found'], Response::HTTP_BAD_REQUEST);
        }

        $serializedRole = [
            'id' => $role->getId(),
            'name' => $role->getName(),
            'permissions' => $role->getPermissions(),
        ];

        return new JsonResponse($serializedRole);
    }

    #[Route('', name: 'app_role_create', methods: ['POST'])]
    public function createRole(Request $request, EntityManagerInterface $entityManagerInterface, ValidatorInterface $validator): JsonResponse
    {
        $role = new Role();
 
        $data = $request->toArray();
        
        $role->setName($data['name']);
        if (! empty($data['permissions'])) {
            $role->setPermissions($data['permissions'], $entityManagerInterface);
        }

        $errors = $validator->validate($role);
        if (count($errors) > 0) {
            // handle validation errors
            return new JsonResponse(['errors' => (string) $errors], Response::HTTP_BAD_REQUEST);
        }

        try {
            $entityManagerInterface->persist($role);
            $entityManagerInterface->flush();

            $serializedRole = [
                'id' => $role->getId(),
                'name' => $role->getName(),
                'permissions' => $role->getPermissions(),
            ];

            return new JsonResponse($serializedRole, Response::HTTP_CREATED);
        } catch (UniqueConstraintViolationException $e) {
            return new JsonResponse(['error' => 'A role with that name already exists.'], Response::HTTP_CONFLICT);
        } catch (Exception $e) {
            return new JsonResponse(['error' => 'Failed to create role'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/{id}', name: 'app_role_update', methods: ['PUT'])]
    public function updateRole(int $id, Request $request, EntityManagerInterface $entityManagerInterface, ValidatorInterface $validator): JsonResponse
    {
        $role = $entityManagerInterface->getRepository(Role::class)->find($id);

        if (! $role) {
            return new JsonResponse(['error' => 'Role not found'], Response::HTTP_BAD_REQUEST);
        }
 
        $data = $request->toArray();
        
        $role->setName($data['name']);
        // check if permissions are provided
        if (! empty($data['permissions'])) {
            $role->setPermissions($data['permissions'], $entityManagerInterface);
        }

        $errors = $validator->validate($role);
        if (count($errors) > 0) {
            // handle validation errors
            return new JsonResponse(['errors' => (string) $errors], Response::HTTP_BAD_REQUEST);
        }

        try {
            $entityManagerInterface->persist($role);
            $entityManagerInterface->flush();

            $serializedRole = [
                'id' => $role->getId(),
                'name' => $role->getName(),
                'permissions' => $role->getPermissions(),
            ];

            return new JsonResponse($serializedRole, Response::HTTP_CREATED);
        } catch (UniqueConstraintViolationException $e) {
            return new JsonResponse(['error' => 'A role with that name already exists.'], Response::HTTP_CONFLICT);
        } catch (Exception $e) {
            return new JsonResponse(['error' => 'Failed to create role'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/{id}', name: 'app_role_delete', methods: ['DELETE'])]
    public function deleteRole(int $id, EntityManagerInterface $entityManagerInterface): JsonResponse
    {
        $role = $entityManagerInterface->getRepository(className: Role::class)->find($id);

        if (! $role) {
            return new JsonResponse(['error' => 'Role not found'], Response::HTTP_NOT_FOUND);
        }

        $entityManagerInterface->remove($role);
        $entityManagerInterface->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
