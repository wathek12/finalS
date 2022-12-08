<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormEditType;
use App\Form\UserType;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/user')]
class UserController extends AbstractController
{


    #[Route('/edit', name: 'app_user_edit')]
    public function edit(Request $request, UserRepository $userRepository): Response
    {
        $user= $userRepository->find($this->getUser());
        $form = $this->createForm(RegistrationFormEditType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $userRepository->save($user, true);

            return $this->redirectToRoute('front', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('user/editProfil.html.twig', [
            'user' => $user,
            'registrationForm' => $form,
        ]);
    }
    #[Route('/', name: 'app_user_index', methods: ['GET'])]
    public function index(UserRepository $userRepository): Response
    {
        return $this->render('user/index.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }


    #[Route('/back', name: 'back', methods: ['GET'])]
    public function back(UserRepository $userRepository): Response
    {
        return $this->render('back.html.twig', [
        ]);
    }

    #[Route('/front', name: 'front', methods: ['GET'])]
    public function front(UserRepository $userRepository): Response
    {
        return $this->render('front.html.twig', [
        ]);
    }

    #[Route('/new', name: 'app_user_new', methods: ['GET', 'POST'])]
    public function new(Request $request, UserRepository $userRepository): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $userRepository->save($user, true);

            return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('user/new.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_user_show', methods: ['GET'])]
    public function show(User $user): Response
    {
        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }


    #[Route('/{id}', name: 'app_user_delete', methods: ['POST'])]
    public function delete(Request $request, User $user, UserRepository $userRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $userRepository->remove($user, true);
        }

        return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
    }



    #[Route('/blocker/{id}', name: 'bloquer', methods: ['GET'])]
    public function blocker($id)
    {
        $em = $this->getDoctrine()->getManager();
        $res = $em->getRepository(User::class)->find($id);
        $res->setEtat(0);
        $em->persist($res);
        $em->flush();
        return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);

    }

    #[Route('/deblocker/{id}', name: 'debloquer', methods: ['GET'])]
    public function debloquer($id)
    {
        $em = $this->getDoctrine()->getManager();
        $res = $em->getRepository(User::class)->find($id);
        $res->setEtat(1);
        $em->persist($res);
        $em->flush();
        return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);

    }
}
