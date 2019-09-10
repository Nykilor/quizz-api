<?php
// api/src/EventSubscriber/AddOwnerToArticleSubscriber.php
// https://stackoverflow.com/questions/52528915/using-api-platform-automatically-assign-user-to-object-onetomany
// https://api-platform.com/docs/core/events/
namespace App\EventSubscriber;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\Entity\User;
use App\Entity\Report;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

final class AddReportResolverSubscriber implements EventSubscriberInterface
{

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    public function __construct(TokenStorageInterface $tokenStorage)
    {

        $this->tokenStorage = $tokenStorage;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::VIEW => ['attachOwner', EventPriorities::PRE_WRITE],
        ];
    }

    public function attachOwner(GetResponseForControllerResultEvent $event)
    {
        $entity = $event->getControllerResult();
        $method = $event->getRequest()->getMethod();

        if (!$entity instanceof Report || Request::METHOD_PUT !== $method) {
            return;
        }

        $token = $this->tokenStorage->getToken();
        if (!$token) {
            return;
        }

        $resolver = $token->getUser();

        if (!$resolver instanceof User) {
            return;
        }


        $entity->setResolved(true);
        $entity->setResolvedBy($resolver);

    }
}
