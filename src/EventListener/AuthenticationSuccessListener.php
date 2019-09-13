<?php
namespace App\EventListener;

use DateTime;

use App\Exception\UserIsBannedException;

use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;

class AuthenticationSuccessListener
{

    public function onAuthenticationSuccessResponse(AuthenticationSuccessEvent $event)
    {
        //Can't be 'anon.' because this event listner is called after the creation of token
        $user = $event->getUser();
        $bannedTillDate = $user->getBannedTill();

        $currentDate = new DateTime();

        if(!is_null($bannedTillDate)) {
          if($currentDate < $bannedTillDate) {
            throw new UserIsBannedException("You're banned till ".$bannedTillDate->format('Y-m-d H:i:s').", for '".$user->getBanReason()."'");
          }
        }

    }
}
