<?php

namespace App\Controller;

use App\Entity\User;

use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class RegisterUserController
{
    private $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    public function __invoke(User $data) : User
    {
        $pw = $data->getPassword();

        $data->setPassword($this->encoder->encodePassword($data, $pw));

        return $data;
    }
}
