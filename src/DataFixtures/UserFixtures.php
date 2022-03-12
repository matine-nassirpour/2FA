<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    private Generator $faker;
    private ObjectManager $manager;
    private UserPasswordHasherInterface $passwordHash;

    public function __construct(UserPasswordHasherInterface $passwordHash) {
        $this->passwordHash = $passwordHash;
    }

    public function load(ObjectManager $manager): void
    {
        $this->manager = $manager;
        $this->faker = Factory::create();
        $this->generateUsers(3);
        $this->manager->flush();
    }

    private function generateUsers(int $number): void
    {
        $isVerified = [true, false, false];

        for ($i = 0; $i < $number; $i++) {
            $user = new User();

            $user->setEmail($this->faker->email)
                ->setPassword($this->passwordHash->hashPassword($user, 'chatelet'))
                ->setIsVerified($isVerified[$i]);

            $this->manager->persist($user);
        }
    }
}
