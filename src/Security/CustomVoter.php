<?php

namespace App\Security;

use ReflectionException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Routing\Attribute\Route;

class CustomVoter extends Voter
{
    public const EDIT = 'edit';
    public const VIEW = 'view';

    public function __construct(private readonly RequestStack $requestStack, private readonly UrlGeneratorInterface $urlGenerator){
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        // $subject = type de l'objet
        return in_array($attribute, [self::EDIT, self::VIEW]);
    }

    /**
     * @throws ReflectionException
     */
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        $request = $this->requestStack->getCurrentRequest();

        $controller = $request?->attributes->get('_controller');
        $tabController = explode('::', $controller);

        $currentController = new \ReflectionClass($tabController[0]);
        $method = $currentController->getMethod($tabController[1]);
        $options = current($method->getAttributes(Route::class))->getArguments()['options'];

        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        return match($attribute) {
            self::EDIT => $this->{$options['methodApply']}($request, $subject, $user, $options['redirectRoute']),
            default => true,
        };

    }


    protected function verifyCreatedBy(Request $request, $subject, UserInterface $user, string $routeRedirect): bool{
        if (!($subject->getCreatedBy()->getId() === $user->getId())) {
            $request->getSession()->getFlashBag()->add('error', 'Vous n\'avez pas accès à cette donnée.');
            $url = $this->urlGenerator->generate($routeRedirect);
            header('Location: ' . $url);
            exit;
        }
        return true;
    }

}
