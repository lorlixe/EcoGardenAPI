<?php

namespace App\DataFixtures;

use App\Entity\Advice;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private $userPasswordHasher;

    public function __construct(UserPasswordHasherInterface $userPasswordHasher)
    {
        $this->userPasswordHasher = $userPasswordHasher;
    }
    public function load(ObjectManager $manager): void
    {

        // Création d'un user "normal"
        $user = new User();
        $user->setEmail("user@EcoGardenApi.com");
        $user->setRoles(["ROLE_USER"]);
        $user->setPassword($this->userPasswordHasher->hashPassword($user, "password"));
        $user->setCity("Paris");
        $manager->persist($user);

        // Création d'un user admin
        $userAdmin = new User();
        $userAdmin->setEmail("admin@bookapi.com");
        $userAdmin->setRoles(["ROLE_ADMIN"]);
        $userAdmin->setPassword($this->userPasswordHasher->hashPassword($userAdmin, "password"));
        $userAdmin->setCity("Nice");
        $manager->persist($userAdmin);

        // Création d'un conseil

        for ($i = 0; $i < 20; $i++) {
            $advice = new Advice();
            $advice->setTitle("Conseil n° " . $i);
            $advice->setDescription("Description du conseil n°" . $i);
            $advice->setUsers($userAdmin);
            $manager->persist($advice);
        }

        $manager->flush();
    }
}
