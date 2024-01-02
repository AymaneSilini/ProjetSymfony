<?php

namespace App\Controller;

use App\Form\ResetPasswordRequestFormType;
use App\Repository\UsersRepository;
use App\Service\SendMailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route(path: '/connexion', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // if ($this->getUser()) {
        //     return $this->redirectToRoute('target_path');
        // }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    #[Route(path: '/deconnexion', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    #[Route(path: '/oublie-mdp', name: 'forgotten_password')]
    public function forgottenPassword(Request $request, UsersRepository $usersRepository, TokenGeneratorInterface $tokenGenerator, EntityManagerInterface $entityManager, SendMailService $mail): Response
    {
        $form = $this->createForm(ResetPasswordRequestFormType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user = $usersRepository->findOneByEmail($form->get('email')->getData());
            if ($user) {
                //we generate reset token
                $token = $tokenGenerator->generateToken();
                $user->setResetToken($token);
                $entityManager->persist($user);
                $entityManager->flush();
                //we generate reset link
                $url = $this->generateUrl('reset_password', ['token' => $token], UrlGeneratorInterface::ABSOLUTE_URL);
                //we generate the mail
                $mail->send(
                    'no-reply@e-commerce.fr',
                    $user->getEmail(),
                    'Réinitialisation de mot de passe',
                    'reset_password',
                    compact('url', 'user')
                );
                $this->addFlash('danger', 'Un problème est survenue');
            }
            $this->addFlash('success', 'Un e-mail de changement de mot de passe vous a été renvoyé');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('security/reset_password_request.html.twig', [
            'requestPassForm' => $form->createView()
        ]);
    }

    #[Route(path: '/oublie-mdp/{token}', name: 'reset_password')]
    public function resetPassword(Request $request, UsersRepository $usersRepository, TokenGeneratorInterface $tokenGenerator, EntityManagerInterface $entityManager): Response
    {
        return $this->render('security/reset_password.html.twig', []);
    }
}
