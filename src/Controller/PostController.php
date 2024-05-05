<?php

namespace App\Controller;

use App\Entity\Post;
use App\Form\PostType;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class PostController extends AbstractController
{
    #[Route('/post', name: 'app_post')]
    public function index(PostRepository $postRepository): Response
    {
        return $this->render('post/index.html.twig', [
            'posts' => $postRepository->findAll(),
        ]);

    }

    #[Route('/post/{id<\d>}', name: 'app_post_show')]
    public function show(Post $post): Response
    {
        return $this->render('post/show.html.twig', [
            'post' => $post
        ]);
    }

    #[Route('/post/add', name: 'app_post_add', priority: 2)]
    public function add(Request $request, EntityManagerInterface $entityManager): Response
    {
       return $this->save($request, $entityManager);
    }

    #[Route('/post/{id<\d>}/edit', name: 'app_post_edit')]
    public function edit(Post $post, Request $request, EntityManagerInterface $entityManager): Response
    {
        return $this->save($request, $entityManager, $post);
    }

    private function save(Request $request, EntityManagerInterface $entityManager, Post $post = null): Response
    {
        $isUpdate = !is_null($post);

        $form = $this->createForm(PostType::class, $post ?? new Post());
        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            return $this->render('post/' . ($isUpdate ? 'add' : 'edit') . '.html.twig', ['form' => $form]);
        }

        $postToSave = $form->getData();

        if (!$isUpdate) {
            $postToSave->setCreated(new \DateTime());
        }

        $entityManager->persist($postToSave);
        $entityManager->flush();

        $this->addFlash('success', 'Post has been ' . ($isUpdate ? 'created' : 'updated') . ' successfully');

        return $this->redirectToRoute('app_post');
    }
}
