<?php

namespace App\DataFixtures\Loader;

use App\Entity\Enum\Status;
use App\Entity\User;
use App\Repository\EntrepriseRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Yaml\Yaml;

class LoadUserData extends Fixture implements OrderedFixtureInterface
{
    public function __construct(
        private EntrepriseRepository $entrepriseRepository,
        private UserPasswordHasherInterface $hasher)
    {
    }

    public function load(ObjectManager $manager): void
    {
        $userRows = Yaml::parseFile(__DIR__.'/../Files/User.yml');
        foreach ($userRows['users'] as $row) {
            $this->loadUsers($manager, $row);
        }
        $manager->flush();
    }

    private function loadUsers(ObjectManager $manager, array $row): void
    {
        $faker = Factory::create();
        $user = (new User())
            ->setRoles(json_decode($row['roles'], true))
            ->setEmail($row['email'])
            ->setStatus(Status::from($row['status']));

        if (isset($row['entreprise'])) {
            $user->setEntreprise(
                $this->entrepriseRepository->findOneBy(['uuid' => $row['entreprise']]))
            ;
        }

        $password = $this->hasher->hashPassword($user, 'punaise');
        $user->setPassword($password);

        $manager->persist($user);
    }

    public function getOrder(): int
    {
        return 5;
    }
}
