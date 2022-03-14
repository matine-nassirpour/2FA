<?php

namespace App\EventSubscriber;

use App\Entity\User;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\AuthenticationEvents;
use Symfony\Component\Security\Core\Event\AuthenticationSuccessEvent;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\Event\LogoutEvent;
use Symfony\Component\Security\Http\Event\SwitchUserEvent;
use Symfony\Component\Security\Http\SecurityEvents;

class AuthenticatorSubscriber implements EventSubscriberInterface
{
    private LoggerInterface $securityLogger;
    private RequestStack $requestStack;

    public function __construct(
        LoggerInterface $securityLogger,
        RequestStack $requestStack
    )
    {
        $this->securityLogger = $securityLogger;
        $this->requestStack = $requestStack;
    }

    /**
     * @return string[]
     */
    public static function getSubscribedEvents()
    {
        return [
            AuthenticationEvents::AUTHENTICATION_SUCCESS => 'onSecurityAuthenticationSuccess',
            SecurityEvents::INTERACTIVE_LOGIN => 'onSecurityInteractiveLogin',
            LogoutEvent::class => 'onSecurityLogout',
            SecurityEvents::SWITCH_USER => 'onSecuritySwitchUser'
        ];
    }

    public function onSecurityAuthenticationSuccess(AuthenticationSuccessEvent $event): void
    {
        [
            'route_name' => $routeName,
            'user_ip' => $userIp
        ] = $this->getRouteNameAndUserIp();

        if (empty($event->getAuthenticationToken()->getRoleNames())) {
            $this->securityLogger->info('Oh, un utilisateur anonyme ayant l\'adresse IP ' . $userIp . ' est apparu sur la route : ' . $routeName . ' :-)');
        } else {
            $securityToken = $event->getAuthenticationToken();

            $userEmail = $this->getUserEmail($securityToken);

            $this->securityLogger->info('Un utilisateur ayant l\'adresse IP ' . $userIp . ' a évolué en entité User avec l\'email ' . $userEmail . ' :-)');
        }
    }

    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event): void
    {
        // ...
    }

    public function onSecurityLogout(LogoutEvent $event): void
    {
        // ...
    }

    public function onSecuritySwitchUser(SwitchUserEvent $event): void
    {
        // ...
    }

    /**
     * Returns the user's IP and the name of the route where the user arrived
     *
     * @return array{user_ip: string|null, route_name: mixed}
     */
    private function getRouteNameAndUserIp(): array
    {
        $request = $this->requestStack->getCurrentRequest();

        if (!$request) {
            return [
                'route_name' => 'Inconnue',
                'user_ip' => 'Inconnue'
            ];
        }

        return [
            'route_name' => $request->attributes->get('_route'),
            'user_ip' => $request->getClientIp() ?? 'Inconnue'
        ];
    }

    private function getUserEmail(TokenInterface $securityToken): string
    {
        /** @var User $user */
        $user = $securityToken->getUser();

        return $user->getEmail();
    }
}
