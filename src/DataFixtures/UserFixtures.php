<?php

namespace App\DataFixtures;

use App\Entity\User;
use DateInterval;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $firstUser = new User();
        $firstUser->setEmail('matine.nassirpour@chatelet.fr')
            ->setPassword($this->passwordHasher->hashPassword($firstUser, 'chatelet'))
            ->setIsVerified(true)
            ->setAccountVerifiedAt((new DateTimeImmutable('now'))->add(new DateInterval('PT7M')))
        ;

        $secondUser = new User();
        $secondUser->setEmail('olivier.bastin@chatelet.fr')
            ->setPassword($this->passwordHasher->hashPassword($secondUser, 'chatelet'))
        ;

        $thirdUser = new User();
        $thirdUser->setEmail('louis.morvan@chatelet.fr')
            ->setPassword($this->passwordHasher->hashPassword($thirdUser, 'chatelet'))
        ;

        $manager->persist($firstUser);
        $manager->persist($secondUser);
        $manager->persist($thirdUser);

        $manager->flush();
    }
}
