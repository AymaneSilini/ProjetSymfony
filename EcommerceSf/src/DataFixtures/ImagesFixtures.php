<?php

namespace App\DataFixtures;

use App\Entity\Images;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\String\Slugger\SluggerInterface;
use Faker;


class ImagesFixtures extends Fixture implements DependentFixtureInterface
{
    public function __construct(private SluggerInterface $slugger)
    {
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Faker\Factory::create('fr_FR');
        for ($img = 1; $img <= 10; $img++) {
            $image = new Images();
            $image->setName($faker->image(null, 640, 480));
            //we get a product reference from ProductsFixtures
            $product = $this->getReference('prod-' . rand(1, 10));
            $image->setProducts($product);
            $manager->persist($image);
        }
        $manager->flush();
    }


    //ProductsFixtures will execute before ImagesFitxures
    public function getDependencies(): array
    {
        return [
            ProductsFixtures::class
        ];
    }
}
