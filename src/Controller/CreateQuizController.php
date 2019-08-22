<?php
// api/src/Controller/CreateBookPublication.php

namespace App\Controller;

use App\Entity\Quiz;

class CreateQuizController
{

    public function __invoke(Quiz $data) : Quiz
    {
        $quiz = $data;
        $quiz->setUpdateDate($quiz->getCreationDate());
        $quiz->setUser();
        return $data;
    }
}
