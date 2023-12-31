<?php

namespace App\DataFixtures;

use App\Entity\Products;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\String\Slugger\SluggerInterface;
use Faker;


class ProductsFixtures extends Fixture
{
    public function __construct(private SluggerInterface $slugger)
    {
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Faker\Factory::create('fr_FR');
        for ($prod = 1; $prod <= 10; $prod++) {
            $product = new Products();
            $product->setName($faker->text(15));
            $product->setDescription($faker->text());
            $product->setSlug($this->slugger->slug($product->getName())->lower());
            $product->setPrice($faker->numberBetween(900, 10000));
            $product->setStock($faker->numberBetween(0, 10));
            //we get a category reference from CategoryFixtures
            $category = $this->getReference('cat-' . rand(1, 7));
            //dump($category->getName());
            $product->setCategories($category);

            //stock a reference that can be returned in the ImagesFixtures
            $this->setReference('prod-' . $prod, $product);
            $manager->persist($product);
        }
        $manager->flush();
    }
}
