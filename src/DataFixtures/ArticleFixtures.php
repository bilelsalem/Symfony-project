<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Article;
use App\Entity\Category;
use App\Entity\Comment;
use Faker\Factory;

class ArticleFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $faker = Factory::create('fr_FR');

        //créer 3 catégories fakées
        for ($i = 1; $i <= 3; $i++) {
            $category = new Category();
            $category->setTitle($faker->sentence())
                ->setDescription($faker->paragraph());


            $manager->persist($category);

            //créer entre 4 et 6 articles
            for ($j = 0; $j <= mt_rand(4, 6); $j++) {
                $content = '<p>' . join($faker->paragraphs(5), '</p><p>') . '</p>';
                $article = new Article();
                $article->setTitle($faker->sentence())
                    ->setContent($content)
                    ->setImage($faker->imageUrl())
                    ->setCreatedAt($faker->dateTimeBetween('-6 months'))
                    ->setCategory($category);

                $manager->persist($article);

                //On donne des commentaires à l'article
                for ($k = 0; $k <= mt_rand(4, 6); $k++) {
                    $comment = new Comment();
                    $content = '<p>' . join($faker->paragraphs(2), '</p><p>') . '</p>';

                    $days = (new \DateTime())->diff($article->getCreatedAt())->days;

                    $comment->setAuthor($faker->name())
                        ->setContent($content)
                        ->setCreatedAt($faker->dateTimeBetween('-' . $days . ' days'))
                        ->setArticle($article);

                    $manager->persist($comment);
                }
            }
        }
        $manager->flush();
    }
}
