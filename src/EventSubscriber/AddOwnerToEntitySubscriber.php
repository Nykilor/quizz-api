<?php
// api/src/EventSubscriber/AddOwnerToArticleSubscriber.php
// https://stackoverflow.com/questions/52528915/using-api-platform-automatically-assign-user-to-object-onetomany
// https://api-platform.com/docs/core/events/
namespace App\EventSubscriber;

use App\Entity\HasOwnerInterface;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\Entity\User;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

final class AddOwnerToEntitySubscriber implements EventSubscriberInterface
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

        if (!$entity instanceof HasOwnerInterface || Request::METHOD_POST !== $method) {

            // Only handle Article entities (Event is called on any Api entity)
            return;
        }

        // maybe these extra null checks are not even needed
        $token = $this->tokenStorage->getToken();
        if (!$token) {
            return;
        }

        $owner = $token->getUser();

        if (!$owner instanceof User) {
            return;
        }


        // Attach the user to the not yet persisted Article
        $entity->setUser($owner);

    }
}
