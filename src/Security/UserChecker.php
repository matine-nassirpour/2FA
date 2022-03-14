<?php

namespace App\Security;

use App\Entity\User;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserChecker implements UserCheckerInterface
{
    private RequestStack $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof User) {
            return;
        }

        // Warning, if you enter a wrong password, the exception will be displayed
        $expirationDate = $user->getAccountMustBeVerifiedBefore()->format('d/m/Y à H\hi');
        if (!$user->getIsVerified()) {
            throw new CustomUserMessageAccountStatusException('Votre compte n\'est pas actif, veuillez consulter vos e-mail pour l\'activer avant le ' . $expirationDate);
        }
    }

    public function checkPostAuth(UserInterface $user): void
    {
        if (!$user instanceof User) {
            return;
        }

        if ($user->getIsGuardCheckIp() && !$this->isUserIpIsInWhiteList($user)) {
            throw new CustomUserMessageAccountStatusException('Vous n\'êtes pas autorisé à vous identifier avec cette adresse IP, car elle ne se figure pas sur la liste blanche des adresses IP autorisées !');
        }
    }

    private function isUserIpIsInWhiteList(User $user): bool
    {
        $request = $this->requestStack->getCurrentRequest();

        if (!$request) {
            return false;
        }

        $userIp = $request->getClientIp();
        $userWhiteListIp = $user->getWhitelistedIpAddresses();

        return in_array($userIp, $userWhiteListIp, true);
    }
}
