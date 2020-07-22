<?php

namespace App\Controller;

use App\Entity\Article;

use App\Entity\Comment;
use App\Form\ArticleType;
use App\Form\CommentType;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class BlogController extends AbstractController
{
    /**
     * @var ArticleRepository
     */
    private $repository;

    /**
     * @var EntityManagerInterface
     */
    private $em;



    public function __construct(ArticleRepository $repository, EntityManagerInterface $em)
    {
        $this->repository = $repository;
        $this->em = $em;
    }

    /**
     * @Route("/blog", name="blog")
     */
    public function index()
    {
        $articles = $this->repository->findAll();
        return $this->render('blog/index.html.twig', [
            'controller_name' => 'BlogController',
            'articles' => $articles,
        ]);
    }

    /**
     * @Route ("/",name="home")
     */
    public function home()
    {
        return $this->render('blog/home.html.twig');
    }
    /**
     * @Route("/blog/{slug}.{id}", name="blog_show")
     */
    public function show(Article $article, $slug, Request $request) // $article est connu grace au paramConventer qui va chercher l'article suivant l'id passant dans l'url
    {
        $comment = new Comment();

        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $comment->setCreatedAt(new \DateTime())
                ->setArticle($article);
            $this->em->persist($comment);
            $this->em->flush();
            return $this->redirectToRoute('blog_show', [
                'id' => $article->getId(),
                'slug' => $article->getSlug(),
            ], 301);
        }
        // $article = $this->repository->find($id);
        if ($article->getSlug() != $slug) {
            return $this->redirectToRoute('blog_show', [
                'id' => $article->getId(),
                'slug' => $article->getSlug(),
            ], 301);
        }
        return $this->render('blog/show.html.twig', [
            'article' => $article,
            'commentForm' => $form->createView(),
        ]);
    }
    /**
     * @Route("/blog/new" , name = "blog_new")
     * @Route("/blog/{id}/edit" , name = "blog_edit")
     */
    public function form(Article $article = null, Request $request)
    {
        if (!$article) {
            $article = new Article();
        }
        $form = $this->createForm(ArticleType::class, $article);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$article->getId()) {
                $article->setCreatedAt(new \DateTime());
            }
            $this->em->persist($article);
            $this->em->flush();
            return $this->redirectToRoute('blog_show', [
                'id' => $article->getId(),
                'slug' => $article->getSlug(),
            ], 301);
        }

        return $this->render('blog/create.html.twig', [
            'form' => $form->createView(),
            'modeEdit' => $article->getId() !== null,
        ]);
    }
}
