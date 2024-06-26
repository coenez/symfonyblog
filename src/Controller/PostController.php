<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Post;
use App\Form\CommentType;
use App\Form\PostType;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class PostController extends AbstractController
{
    #[Route('/post', name: 'app_post')]
    public function index(PostRepository $postRepository): Response
    {
        return $this->render('post/index.html.twig', [
            'posts' => $postRepository->findAllWithComments(),
        ]);
    }

    #[Route('/post_topliked', name: 'app_post_topliked')]
    public function topLiked(PostRepository $postRepository): Response
    {
        return $this->render('post/index.html.twig', [
            'posts' => $postRepository->findTopLiked(1),
        ]);
    }

    #[Route('/post_follows', name: 'app_post_follows')]
    public function follows(PostRepository $postRepository): Response
    {
        return $this->render('post/index.html.twig', [
            'posts' => $postRepository->findAllByAuthor($this->getUser()->getFollows()),
        ]);
    }

    #[Route('/post/{id<\d>}', name: 'app_post_show')]
    #[IsGranted(Post::VIEW, 'post')]
    public function show(Post $post): Response
    {
        return $this->render('post/show.html.twig', [
            'post' => $post,
            'comments' => $post->getComments()
        ]);
    }

    #[Route('/post/add', name: 'app_post_add', priority: 2)]
    #[IsGranted('ROLE_VERIFIED_USER')]
    public function add(Request $request, EntityManagerInterface $entityManager): Response
    {
        return $this->save($request, $entityManager);
    }

    #[Route('/post/{id<\d>}/edit', name: 'app_post_edit')]
    #[IsGranted(Post::EDIT, 'post')]
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
            return $this->render('post/' . ($isUpdate ? 'edit' : 'add') . '.html.twig', ['form' => $form, 'post' => $post]);
        }

        $postToSave = $form->getData();

        if (!$isUpdate) {
            $postToSave->setAuthor($this->getUser());
        }

        $entityManager->persist($postToSave);
        $entityManager->flush();

        $this->addFlash('success', 'Post has been ' . ($isUpdate ? 'updated' : 'created') . ' successfully');

        return $this->redirectToRoute('app_post');
    }

    #[Route('/post/{id<\d>}/comment', name: 'app_post_comment')]
    #[IsGranted('ROLE_COMMENTER')]
    public function addComment(Post $post, Request $request, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CommentType::class, new Comment());
        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            return $this->render('post/addComment.html.twig', ['form' => $form, 'post' => $post]);
        }

        $comment = $form->getData();
        $comment->setPost($post);
        $comment->setAuthor($this->getUser());
        $entityManager->persist($comment);
        $entityManager->flush();

        $this->addFlash('success', 'Comment has been added');

        return $this->redirectToRoute('app_post_show', ['id' => $post->getId()]);
    }
}
