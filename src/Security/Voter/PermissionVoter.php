<?php

namespace App\Security\Voter;

use App\Entity\Role;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

final class PermissionVoter extends Voter{
    public const PERMISSION_VIEW = 'PERMISSION_VIEW';
    public const PERMISSION_EDIT = 'PERMISSION_EDIT';
    public const PERMISSION_DELETE = 'PERMISSION_DELETE';

    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return in_array($attribute, [self::PERMISSION_VIEW, self::PERMISSION_EDIT, self::PERMISSION_DELETE]);
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        // if the user is anonymous, do not grant access
        if (! $user instanceof User) {
            return false;
        }

        // Get the Role repository
        $roleRepository = $this->entityManager->getRepository(Role::class);

        // Loop through the user's roles and their permissions to check if the user has the required permission
        foreach ($user->getRoles() as $roleName) {
            // Get the Role entity from the DB by its name (e.g., ROLE_ADMIN)
            $role = $roleRepository->findOneBy(['name' => $roleName]);

            if (! $role) {
                continue; // if role is not found in DB, skip to the next one
            }

            foreach ($role->getPermissionsCollection() as $permission) {
                // permission matching
                if ($permission->getName() === $attribute) {
                    return true; // granted
                }
            }
        }

        return false;
    }
}
