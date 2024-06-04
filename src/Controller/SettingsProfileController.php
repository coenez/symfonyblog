<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\UserProfile;
use App\Form\ProfileImageType;
use App\Form\UserProfileType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;

class SettingsProfileController extends AbstractController
{
    #[Route('/settings/profile', name: 'app_settings_profile')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function profile(Request $request, EntityManagerInterface $entityManager): Response
    {
        /**
         * @var User $user
         */

        $user = $this->getUser();
        $userProfile = $user?->getUserProfile() ?? new UserProfile();
        $form = $this->createForm(UserProfileType::class, $userProfile);
        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            return $this->render('settings_profile/profile.html.twig', ['form' => $form]);
        }

        $userProfile = $form->getData();
        $user->setUserProfile($userProfile);
        $entityManager->persist($user);
        $entityManager->flush();

        $this->addFlash('success', 'Profile has been saved');

        return $this->redirectToRoute('app_settings_profile');
    }

    #[Route('/settings/profile-image', name: 'app_settings_profile_image')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function profileImage(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        /**
         * @var User $user
         */
        $user = $this->getUser();
        $form = $this->createForm(ProfileImageType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form->get('profileImage')->getData();
            if ($file) {
                $info = pathinfo($file->getClientOriginalName());
                $safeFileName = $slugger->slug($info['filename']);
                $newFileName = $safeFileName . '-' . uniqid() . '.' . $info['extension'];

                try {
                    $file->move($this->getParameter('profiles_directory'), $newFileName);

                    $profile = $user->getUserProfile() ?? new UserProfile();
                    $profile->setImage($newFileName);
                    $user->setUserProfile($profile);

                    $entityManager->persist($user);
                    $entityManager->flush();
                    $this->addFlash('success', 'Profile image has been saved');

                } catch (FileException $e) {
                    $this->addFlash('error', 'Profile image save has failed');
                }
                return $this->redirectToRoute('app_settings_profile_image');
            }
        }
        return $this->render('settings_profile/profile_image.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
