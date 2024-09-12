<?php

namespace App\Controller;

use App\Entity\Permission;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/permissions')]
class PermissionController extends AbstractController
{
    #[Route('', name: 'app_permission_index', methods: ['GET'])]
    public function getPermissions(EntityManagerInterface $entityManagerInterface): JsonResponse
    {
        $permissions = $entityManagerInterface->getRepository(Permission::class)->findAll();
        $serializedPermissions = [];
        foreach ($permissions as $role) {
            $serializedPermissions[] = [
                'id' => $role->getId(),
                'name' => $role->getName(),
            ];
        }
        return new JsonResponse($serializedPermissions);
    }

    #[Route('', name: 'app_permission_create', methods: ['POST'])]
    public function createPermission(Request $request, EntityManagerInterface $entityManagerInterface,  ValidatorInterface $validator): JsonResponse
    {
        $permission = new Permission();
 
        $data = $request->toArray();
        
        $permission->setName($data['name']);

        $errors = $validator->validate($permission);
        if (count($errors) > 0) {
            // handle validation errors
            return new JsonResponse(['errors' => (string) $errors], Response::HTTP_BAD_REQUEST);
        }

        try {
            $entityManagerInterface->persist($permission);
            $entityManagerInterface->flush();

            $serializedPermission = [
                'id' => $permission->getId(),
                'name' => $permission->getName(),
            ];

            return new JsonResponse($serializedPermission, Response::HTTP_CREATED);
        } catch (UniqueConstraintViolationException $e) {
            return new JsonResponse(['error' => 'A permission with that name already exists.'], Response::HTTP_CONFLICT);
        } catch (Exception $e) {
            return new JsonResponse(['error' => 'Failed to create permission'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/{id}', name: 'app_permission_update', methods: ['PUT'])]
    public function updatePermission(int $id, Request $request, EntityManagerInterface $entityManagerInterface, ValidatorInterface $validator): JsonResponse
    {
        $permission = $entityManagerInterface->getRepository(Permission::class)->find($id);

        if (! $permission) {
            throw $this->createNotFoundException('Role not found.');
        }
 
        $data = $request->toArray();
        
        $permission->setName($data['name']);

        $errors = $validator->validate($permission);
        if (count($errors) > 0) {
            // handle validation errors
            return new JsonResponse(['errors' => (string) $errors], Response::HTTP_BAD_REQUEST);
        }

        try {
            $entityManagerInterface->persist($permission);
            $entityManagerInterface->flush();

            $serializedPermission = [
                'id' => $permission->getId(),
                'name' => $permission->getName(),
            ];

            return new JsonResponse($serializedPermission, Response::HTTP_CREATED);
        } catch (UniqueConstraintViolationException $e) {
            return new JsonResponse(['error' => 'A permission with that name already exists.'], Response::HTTP_CONFLICT);
        } catch (Exception $e) {
            return new JsonResponse(['error' => 'Failed to create permission'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/{id}', name: 'app_permision_delete', methods: ['DELETE'])]
    public function deletePermission(int $id, EntityManagerInterface $entityManagerInterface): JsonResponse
    {
        $permission = $entityManagerInterface->getRepository(className: Permission::class)->find($id);

        if (! $permission) {
            return new JsonResponse(['error' => 'Permission not found'], Response::HTTP_NOT_FOUND);
        }

        $entityManagerInterface->remove($permission);
        $entityManagerInterface->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
