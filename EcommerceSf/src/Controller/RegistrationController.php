<?php

namespace App\Controller;

use App\Entity\Users;
use App\Form\RegistrationFormType;
use App\Repository\UsersRepository;
use App\Security\UsersAuthenticator;
use App\Service\JWTService;
use App\Service\SendMailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;

class RegistrationController extends AbstractController
{
    #[Route('/inscription', name: 'app_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        UserAuthenticatorInterface $userAuthenticator,
        UsersAuthenticator $authenticator,
        EntityManagerInterface $entityManager,
        SendMailService $mail,
        JWTService $jwt
    ): Response {
        //get the users
        $user = new Users();
        //create the form based on RegistrationType
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $passwordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $entityManager->persist($user);
            $entityManager->flush();

            //we generate the JWT of the user
            //we create the header
            $header = [
                'typ' => 'JWT',
                'alg' => 'HS256'
            ];
            //we create the payload
            $payload = [
                'user_id' => $user->getId()
            ];
            //we generate the token using the JWTService 
            $token = $jwt->generate($header, $payload, $this->getParameter('app.jwtsecret'));

            //we send the verification mail, using the SendMailService, and the token for the verification
            $mail->send(
                'no-reply@monsite.net',
                $user->getEmail(),
                'Activation de votre compte sur le site e-commerce',
                'register',
                compact('user', 'token')
            );
            //if the user is created, it authenticate and connect the user
            return $userAuthenticator->authenticateUser(
                $user,
                $authenticator,
                $request
            );
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    #[Route('/verification/{token}', name: 'verify_user')]
    public function verifyUser($token, JWTService $jwt, UsersRepository $usersRepository, EntityManagerInterface $entityManager): Response
    {
        //we verify if the token's form is valid, if it didn't expired and id it doesn't have been modified
        if ($jwt->isValid($token) && !$jwt->isExpired($token) && $jwt->check($token, $this->getParameter('app.jwtsecret'))) {
            //we get the paylad
            $payload = $jwt->getPayload($token);
            //we get user from the token, using the payload and the UserRepository
            $user = $usersRepository->find($payload['user_id']);
            //if the user exists and hasn't yes verified their account
            if ($user && !$user->getIsVerified()) {
                $user->setIsVerified(true);
                $entityManager->flush($user);
                $this->addFlash('success', 'Votre compte a bien été activé');
                return $this->redirectToRoute('profile_index');
            }
        }
        //the token has a problem, we inform the user and we redirect to the login page
        $this->addFlash('danger', 'Le token est invalide ou a expiré');
        return $this->redirectToRoute('app_login');
    }

    #[Route('/renvoieverification', name: 'resend_verify_user')]
    public function resendVerifyUser(JWTService $jwt, SendMailService $mail, UsersRepository $usersRepository): Response
    {
        $user = $this->getUser();
        if (!$user) {
            $this->addFlash('danger', 'Vous devez être connecté pour accéder à cette page');
            return $this->redirectToRoute('app_login');
        }
        if ($user->getIsVerified()) {
            $this->addFlash('warning', 'Vous avez déjà activé votre compte');
            return $this->redirectToRoute('profile_index');
        }

        $header = [
            'typ' => 'JWT',
            'alg' => 'HS256'
        ];
        $payload = [
            'user_id' => $user->getId()
        ];
        $token = $jwt->generate($header, $payload, $this->getParameter('app.jwtsecret'));

        $mail->send(
            'no-reply@monsite.net',
            $user->getEmail(),
            'Activation de votre compte sur le site e-commerce',
            'register',
            compact('user', 'token')
        );
        $this->addFlash('success', 'Un e-mail de vérification vous a été renvoyé.');
        return $this->redirectToRoute('profile_index');
    }
}
