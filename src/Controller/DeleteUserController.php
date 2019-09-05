<?php

namespace App\Controller;

use App\Entity\Quiz;
use App\Entity\User;
use App\Entity\Report;
use App\Util\DeleteEntitiesByTrait;

use Doctrine\Common\Persistence\ObjectManager;

class DeleteUserController
{
    use DeleteEntitiesByTrait;
    private $em;

    public function __construct(ObjectManager $em)
    {
        $this->em = $em;
    }

    public function __invoke(User $data) : User
    {
        $this->removeAllQuizRelatedReports($data);
        $this->removeEntietiesBy(Report::class, ["user" => $data->getId()], $this->em);
        $this->removeEntietiesBy(Quiz::class, ["user" => $data->getId()], $this->em);

        return $data;
    }

    private function removeAllQuizRelatedReports(User $data) : void
    {
      $quiz_repostiory = $this->em->getRepository(Quiz::class);
      $quizzes_of_user = $quiz_repostiory->findBy(["user" => $data->getId()]);
      foreach ($quizzes_of_user as $key => $quiz) {
        $this->removeEntietiesBy(Report::class, ["quiz" => $quiz->getId()], $this->em);
      }
    }

}
