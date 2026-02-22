<?php
namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\AllowedIp;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $hasher;

    public function __construct(UserPasswordHasherInterface $hasher)
    {
        $this->hasher = $hasher;
    }

    public function load(ObjectManager $manager): void
    {
        // Default admin user
        $user = new User();
        $user->setEmail('admin@letstalk.nl');
        $user->setRoles(['ROLE_ADMIN']);
        $user->setPassword($this->hasher->hashPassword($user, 'admin123!'));
        $manager->persist($user);

        // Default whitelisted IP
        $ip = new AllowedIp();
        $ip->setIpOrSubnet('127.0.0.1');
        $ip->setDescription('Localhost');
        $manager->persist($ip);

        $manager->flush();
    }
}