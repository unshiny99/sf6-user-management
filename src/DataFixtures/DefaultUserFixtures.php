<?php

namespace App\DataFixtures;

use App\Entity\Permission;
use App\Entity\Role;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class DefaultUserFixtures extends Fixture
{
    private $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        // create permissions
        $permissions = ['PERMISSION_DELETE', 'PERMISSION_READ', 'PERMISSION_CREATE', 'PERMISSION_UPDATE'];
        $permissionEntities = [];
        foreach ($permissions as $permissionName) {
            $permission = new Permission();
            $permission->setName($permissionName);
            $manager->persist($permission);
            $permissionEntities[$permissionName] = $permission; // Save for future assignment to roles
        }

        // create roles
        $roles = [
            'ROLE_ADMIN' => ['PERMISSION_DELETE', 'PERMISSION_READ', 'PERMISSION_CREATE', 'PERMISSION_UPDATE'],
            'ROLE_USER' => ['PERMISSION_READ', 'PERMISSION_CREATE'],
            'ROLE_INVITED' => ['PERMISSION_READ']
        ];

        $roleEntities = [];
        foreach ($roles as $roleName => $rolePermissions) {
            $role = new Role();
            $role->setName($roleName);

            // Assign permissions to the role
            foreach ($rolePermissions as $permissionName) {
                $role->addPermission($permissionEntities[$permissionName]); // Add permission to role
            }

            $manager->persist($role);
            $roleEntities[$roleName] = $role; // Save role entity for future user assignment
        }

        // Create the default user
        $user = new User();
        $user->setUsername('admin');
        $user->setEmail('admin@example.com');

        // Hash the password (set a default password)
        $hashedPassword = $this->passwordHasher->hashPassword($user, 'admin');
        $user->setPassword($hashedPassword);

        // Assign a default role (e.g., ROLE_ADMIN)
        $user->addRole($roleEntities['ROLE_ADMIN']);

        // Persist the user to the database
        $manager->persist($user);

        // Save to the database
        $manager->flush();
    }
}
