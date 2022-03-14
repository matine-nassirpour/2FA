<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Service\SendEmail;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;

class RegistrationController extends AbstractController
{
    #[Route(path: '/register', name: 'app_register', methods: ['GET', 'POST'])]
    public function register(
        EntityManagerInterface $entityManager,
        Request $request,
        SendEmail $sendEmail,
        TokenGeneratorInterface $tokenGenerator,
        UserPasswordHasherInterface $userPasswordHasher,
    ): Response
    {
         // Redirect to home page if user is already logged in
         if ($this->getUser()) {
             return $this->redirectToRoute('app_home');
         }

        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $registrationToken = $tokenGenerator->generateToken();
            $user->setRegistrationToken($registrationToken)
                ->setPassword($userPasswordHasher->hashPassword($user, $form->get('password')->getData()));

            $entityManager->persist($user);
            $entityManager->flush();

            $sendEmail->send([
                'recipient_email' => $user->getEmail(),
                'subject' => 'Activation de votre compte utilisateur',
                'html_template' => 'email/confirm_registration.html.twig',
                'context' => [
                    'userID' => $user->getId(),
                    'registrationToken' => $registrationToken,
                    'tokenLifeTime' => $user->getAccountMustBeVerifiedBefore()->format('d/m/Y à H:i')
                ]
            ]);

            $this->addFlash('success', 'Votre compte utilisateur a bien été créé, veuillez consulter vos e-mails pour l\'activer');

            return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    #[Route(path: '/{id<\d+>}/{token}', name: 'app_verify_account', methods: ['GET'])]
    public function verifyUserAccount(
        EntityManagerInterface $entityManager,
        User $user,
        string $token
    ): Response
    {
        if (($user->getRegistrationToken() === null)
            || ($user->getRegistrationToken() !== $token)
            || ($this->isNotRequestedInTime($user->getAccountMustBeVerifiedBefore()))) {
            throw new AccessDeniedException();
        }

        $user->setIsVerified(true);
        $user->setAccountVerifiedAt(new \DateTimeImmutable('now'));
        $user->setRegistrationToken(null);

        $entityManager->flush();

        $this->addFlash('success', 'Votre compte utilisateur est dès à présent activé, vous pouvez vous connecter !');

        return $this->redirectToRoute('app_login');
    }

    private function isNotRequestedInTime(\DateTimeImmutable $accountMustBeVerifiedBefore): bool
    {
        return (new \DateTimeImmutable('now') > $accountMustBeVerifiedBefore);
    }
}
