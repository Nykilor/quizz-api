<?php
namespace App\EventListener;

use DateTime;

use App\Entity\User;

use App\Exception\UserIsBannedException;

use Doctrine\Common\Persistence\ObjectManager;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTDecodedEvent;

class JWTDecodedListener
{
    private $em;

    public function __construct(ObjectManager $em)
    {
        $this->em = $em;
    }

    public function onJWTDecoded(JWTDecodedEvent $event) : void
    {
        $username = $event->getPayload()["username"];
        $repository = $this->em->getRepository(User::class);
        $user = $repository->findOneBy(["username" => $username]);

        $bannedTillDate = $user->getBannedTill();
        $currentDate = new DateTime();

        if(!is_null($bannedTillDate)) {
          if($currentDate < $bannedTillDate) {
            $event->markAsInvalid();
            throw new UserIsBannedException("You're banned till ".$bannedTillDate->format('Y-m-d H:i:s').", for '".$user->getBanReason()."'");
          }
        }

    }
}
