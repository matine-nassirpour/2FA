<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('user/account/profile/', name: 'app_user_account_profile_')]
class UserAccountController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('home', name: 'home', methods: ['GET'])]
    public function account(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        /**
         * @var User $user
         */
        $user = $this->getUser();

        return $this->render('user_account/index.html.twig', [
            'user' => $user
        ]);
    }

    #[Route('add-IP', name: 'add_IP', methods: ['GET'])]
    public function addUserIpToWhiteList(Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        if (!$request->isXmlHttpRequest()) {
            throw new \HttpException(400, 'The header "X-Requested-With" is missing');
        }

        $userIP = $request->getClientIp();

        /**
         * @var User $user
         */
        $user = $this->getUser();

        $user->setWhitelistedIpAddresses($userIP);

        $this->entityManager->flush();

        return $this->json([
            'message' => 'Adresse IP a été ajoutée à la liste blanche',
            'user_IP' => $userIP
        ]);
    }

    #[Route('toggle-checking-ip', name: 'toggle_checking_ip', methods: ['POST'])]
    public function toggleGuardCheckingIp(Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        if (!$request->isXmlHttpRequest()) {
            throw new \HttpException(400, 'The header "X-Requested-With" is missing');
        }

        $switchValue = $request->getContent();

        if (!in_array($switchValue, ['true', 'false'], true)) {
            throw new \HttpException(400, 'Expected value is "true" or "false"');
        }

        /**
         * @var User $user
         */
        $user = $this->getUser();

        $isSwitchOn = filter_var($switchValue, FILTER_VALIDATE_BOOLEAN);

        $user->setIsGuardCheckIp($isSwitchOn);

        $this->entityManager->flush();

        return $this->json([
            'isGuardCheckingIp' => $isSwitchOn
        ]);
    }
}
