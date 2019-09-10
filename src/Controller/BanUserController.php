<?php

namespace App\Controller;

use App\Entity\User;

class BanUserController
{
    public function __invoke(User $data) : User
    {
        var_dump($data);

        return $data;
    }
}
