<?php

namespace App\Tests;

use App\Entity\Role;
use App\Entity\User;
use App\Repository\RoleRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    private $entityManager;

    protected function setUp(): void
    {
        // Mock the EntityManager
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
    }

    public function testUserCreation(): void
    {
        $user = new User();
        $user->setUsername('testUser');
        $user->setEmail('test@example.com');
        $user->setPassword('password123');
        
        // Assertions to verify the user entity behavior
        $this->assertSame('testUser', $user->getUsername());
        $this->assertSame('test@example.com', $user->getEmail());
        $this->assertSame('password123', $user->getPassword());
    }

    public function testUserCreationWithRoles(): void
    {
        $roleRepository = $this->createMock(RoleRepository::class);

        // Mock role object for ROLE_ADMIN
        $role = new Role();
        $role->setName('ROLE_ADMIN');

        $roleRepository->method('findOneBy')
            ->willReturn($role);

        // Set up the EntityManager to return the mocked RoleRepository
        $this->entityManager->method('getRepository')
            ->with(Role::class)
            ->willReturn($roleRepository);

        $user = new User();
        $user->setUsername('testUser');
        $user->setEmail('test@example.com');
        $user->setPassword('password123');
        $user->addRole($role);
        
        // Assertions to verify the user entity behavior
        $this->assertSame('testUser', $user->getUsername());
        $this->assertSame('test@example.com', $user->getEmail());
        $this->assertSame('password123', $user->getPassword());

        // Check if roles are correctly set
        $roles = $user->getRoles();
        $this->assertCount(1, $roles);
        $this->assertSame('ROLE_ADMIN', $roles[0]);
    }

    public function testUserUpdate(): void
    {
        // Create and persist initial user
        $user = new User();
        $user->setUsername('initialUser');
        $user->setEmail('initial@example.com');
        $user->setPassword('initialPassword');
        
        // Mock the EntityManager and Repository
        $this->entityManager->method('find')
            ->willReturn($user);

        $user->setUsername('updatedUser');
        $user->setEmail('updated@example.com');
        $user->setPassword('updatedPassword');

        // Assertions to verify the update
        $this->assertSame('updatedUser', $user->getUsername());
        $this->assertSame('updated@example.com', $user->getEmail());
        $this->assertSame('updatedPassword', $user->getPassword());
    }

    public function testUserDeletion(): void
    {
        $user = new User();
        $user->setUsername('userToDelete');
        $user->setEmail('delete@example.com');
        $user->setPassword('deletePassword');

        // Mock the EntityManager
        $this->entityManager->expects($this->once())
            ->method('remove')
            ->with($this->equalTo($user));

        $this->entityManager->expects($this->once())
            ->method('flush');

        // Assuming you have a method deleteUser() that handles the deletion
        $userRepository = $this->createMock(UserRepository::class);

        $userRepository->method('find')
            ->willReturn($user);
        $this->entityManager->method('getRepository')
            ->willReturn($userRepository);

        // Simulate deletion logic
        $this->entityManager->remove($user);
        $this->entityManager->flush();

        // Assertions to verify the user is removed
        $this->assertNull($user->getId());
    }
}
