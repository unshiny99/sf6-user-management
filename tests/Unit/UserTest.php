<?php

namespace App\Unit\Tests;

use App\Entity\Role;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface; 
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    private EntityManagerInterface $entityManager;
    private UserRepository $userRepository;

    protected function setUp(): void
    {
        // Create mock objects for dependencies
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->userRepository = $this->createMock(UserRepository::class);
        
        $this->entityManager->expects($this->once())
            ->method('getRepository')
            ->with(User::class)
            ->willReturn($this->userRepository);
    }

    public function testUserCreation(): void
    {
        // arrange
        $username = 'testUser';
        $user = new User();
        $user->setUsername($username);
        $user->setEmail('test@example.com');
        $user->setPassword('password123');

        // act
        $this->userRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['username' => $username])
            ->willReturn($user);
        $repository = $this->entityManager->getRepository(User::class);
        $foundUser = $repository->findOneBy(['username' => $username]);
        
        // assert
        $this->assertSame($username, $foundUser->getUsername());
        $this->assertSame('test@example.com', $foundUser->getEmail());
        $this->assertSame('password123', $foundUser->getPassword());
    }

    public function testUserCreationWithRoles(): void
    {
        // arrange
        $username = 'testUser';
        $user = new User();
        $user->setUsername($username);
        $user->setEmail('test@example.com');
        $user->setPassword('password123');

        $role = new Role();
        $role->setName('ROLE_ADMIN');

        // act
        // Expect the User repository to return the correct username
        $this->userRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['username' => $username])
            ->willReturn($user);

        // Add the role to the user
        $user->addRole($role);
        $userRepository = $this->entityManager->getRepository(User::class);
        $foundUser = $userRepository->findOneBy(['username' => $username]);

        // assert
        $this->assertSame($username, $foundUser->getUsername());
        $this->assertSame('ROLE_ADMIN', $foundUser->getRoles()[0]); // Check that the role was correctly added
    }

    public function testUserUpdate(): void
    {
        // arrange
        $username = 'testUser';
        $user = new User();
        $user->setUsername($username);
        $user->setEmail('old@example.com');
        $user->setPassword('password123');

        // Set up the repository's expectation for findOneBy()
        $this->userRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['username' => $username])
            ->willReturn($user);

        // act
        $repository = $this->entityManager->getRepository(User::class);
        $userToUpdate = $repository->findOneBy(['username' => $username]);

        // Update the user information
        $userToUpdate->setEmail('new@example.com');
        
        // Persist the updated user
        $this->entityManager->persist($userToUpdate);
        $this->entityManager->flush();

        // assert
        $this->assertSame('new@example.com', $userToUpdate->getEmail());
    }

    public function testUserDelete(): void
    {
        // arrange
        $username = 'testUser';
        $user = new User();
        $user->setUsername($username);
        $user->setEmail('test@example.com');
        $user->setPassword('password123');

        // Set up the repository's expectation for findOneBy()
        $this->userRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['username' => $username])
            ->willReturn($user);

        // act
        $repository = $this->entityManager->getRepository(User::class);
        $userToDelete = $repository->findOneBy(['username' => $username]);

        // assert
        $this->assertSame($username, $userToDelete->getUsername());
    }
}
