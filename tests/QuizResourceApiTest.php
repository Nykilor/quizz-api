<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class QuizResourceApiTest extends WebTestCase
{

    protected $client;

    public function testRetriveQuizzesList()
    {
      $client = static::createClient();

      $response = $client->request('GET', "api/quizzes");

      $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }
}
