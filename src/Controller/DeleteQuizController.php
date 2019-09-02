<?php
// api/src/Controller/CreateBookPublication.php

namespace App\Controller;

use App\Entity\Quiz;
use App\Entity\Report;

use Doctrine\Common\Persistence\ObjectManager;

class DeleteQuizController
{
    private $em;

    public function __construct(ObjectManager $em)
    {
        $this->em = $em;
    }

    public function __invoke(Quiz $data) : Quiz
    {
        $quiz_id = $data->getId();

        $report_repostiory = $this->em->getRepository(Report::class);

        $reports = $report_repostiory->findBy(["quiz" => $quiz_id]);

        foreach ($reports as $key => $entity)
        {
          $this->em->remove($entity);

          if(($key+1 % 5) === 0)
          {
            $this->em->flush();
          }
        }

        $this->em->flush();

        return $data;
    }
}
