<?php

namespace App\DataFixtures;

use App\Entity\Categories;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\String\Slugger\SluggerInterface;

class CategoriesFixtures extends Fixture

{
    public function __construct(private SluggerInterface $slugger)
    {
    }
    private $counter = 1;

    public function load(ObjectManager $manager): void
    {
        //functional way
        $parent = $this->createCategory('Informatique', null, $manager);
        $this->createCategory('Ordinateur Portable', $parent, $manager);
        $this->createCategory('Ordinateur Fixe', $parent, $manager);

        $parent = $this->createCategory('Mode', null, $manager);
        $this->createCategory('Homme', $parent, $manager);
        $this->createCategory('Femme', $parent, $manager);
        $this->createCategory('Enfant', $parent, $manager);


        $manager->flush();


        //repetitive way
        // $parent = new Categories();
        // $parent->setName('Informatique');
        // $parent->setSlug($this->slugger->slug($parent->getName())->lower());
        // $manager->persist($parent);

        // $category = new Categories();
        // $category->setName('Ordinateur portable');
        // $category->setSlug($this->slugger->slug($category->getName())->lower());
        // $category->setParent($parent);
        // $manager->persist($category);

        // $manager->flush();


    }

    public function createCategory(string $name, Categories $parent = null, ObjectManager $manager)
    {
        $category = new Categories();
        $category->setName($name);
        $category->setSlug($this->slugger->slug($category->getName())->lower());
        $category->setParent($parent);
        $manager->persist($category);

        //stock a reference that can be returned in the ProductFixtures
        $this->addReference('cat-' . $this->counter, $category);
        $this->counter++;

        return $category;
    }
}
