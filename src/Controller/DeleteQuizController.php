<?php
// api/src/Controller/CreateBookPublication.php

namespace App\Controller;

use App\Entity\Quiz;
use App\Entity\Report;

use App\Util\DeleteEntitiesByTrait;

use Doctrine\Common\Persistence\ObjectManager;

class DeleteQuizController
{
    use DeleteEntitiesByTrait;
    private $em;

    public function __construct(ObjectManager $em)
    {
        $this->em = $em;
    }

    public function __invoke(Quiz $data) : Quiz
    {
        $this->removeEntietiesBy(Report::class, ["quiz" => $data->getId()], $this->em);

        return $data;
    }
}
