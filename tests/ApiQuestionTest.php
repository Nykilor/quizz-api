<?php

namespace App\Tests;

use App\Entity\Quiz;
use App\Entity\Question;

use App\Util\ApiPlatformFunctionalTestTrait;

use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ApiQuestionTest extends WebTestCase
{
  use ApiPlatformFunctionalTestTrait;

  /** @var Client */
  protected $client;

  public function testRetriveSingleQuestion() : void
  {
    $response = $this->request('GET', $this->findOneIriBy(Question::class, ["text" => "Is 100kg of feathers lighter than 100 kg of steel ?"]));

    $this->assertEquals(200, $response->getStatusCode());
    $this->assertEquals('application/ld+json; charset=utf-8', $response->headers->get('Content-Type'));
  }

  public function testFailDeleteQuestionWhenNotLoggedIn() : void
  {
    $response = $this->request("DELETE", $this->findOneIriBy(Question::class, ["text" => "Is 100kg of feathers lighter than 100 kg of steel ?"]));
    $this->assertEquals(401, $response->getStatusCode());
  }

  public function testFailDeleteQuestionAsUser() : void
  {
    $headers = [
      "Authorization" => "Bearer ".$this->getAuthToken("user1", "root")
    ];

    $quiz = $this->getResourceBy(Quiz::class, ["title" => "Test Admin Quiz!"]);
    $quiz_id = $quiz->getId();

    $response = $this->request("DELETE", $this->findOneIriBy(Question::class, ["quiz" => $quiz_id]), null, $headers);
    $this->assertEquals(403, $response->getStatusCode());
  }

  public function testDeleteQuestionAsOwner() : void
  {
    $headers = [
      "Authorization" => "Bearer ".$this->getAuthToken("user2", "root")
    ];

    $quiz = $this->getResourceBy(Quiz::class, ["title" => "Test User2 Quiz!"]);
    $quiz_id = $quiz->getId();

    $response = $this->request("DELETE", $this->findOneIriBy(Question::class, ["quiz" => $quiz_id]), null, $headers);
    $this->assertEquals(204, $response->getStatusCode());
  }

  public function testDeleteQuestionAsAdmin() : void
  {
    $headers = [
      "Authorization" => "Bearer ".$this->getAuthToken()
    ];

    $quiz = $this->getResourceBy(Quiz::class, ["title" => "Test User1 Quiz!"]);
    $quiz_id = $quiz->getId();

    $response = $this->request("DELETE", $this->findOneIriBy(Question::class, ["quiz" => $quiz_id]), null, $headers);
    $this->assertEquals(204, $response->getStatusCode());
  }

  public function testFailUpdateQuestionWhenNotLoggedIn() : void
  {
    $jwt = $this->request('PUT', $this->findOneIriBy(Question::class, ["text" => "Is 100kg of feathers lighter than 100 kg of steel ?"]), []);
    $this->assertEquals(401, $jwt->getStatusCode());
  }

  public function testUpdateQuestionAsAdmin() : void
  {
    $data = [
      "text" => "Admin updated!",
    ];

    $headers = [
      "Authorization" => "Bearer ".$this->getAuthToken()
    ];

    $response = $this->request('PUT', $this->findOneIriBy(Question::class, ["text" => "Is 100kg of feathers lighter than 100 kg of steel ?"]), $data, $headers);
    $this->assertEquals(200, $response->getStatusCode());
  }

  public function testUpdateQuestionAsOwner() : void
  {
    $data = [
      "text" => "Owner updated!",
    ];

    $headers = [
      "Authorization" => "Bearer ".$this->getAuthToken("user1", "root")
    ];

    $quiz_id = $this->getResourceBy(Quiz::class, ["title" => "Test User1 Quiz!"]);

    $response = $this->request('PUT', $this->findOneIriBy(Question::class, ["quiz" => $quiz_id->getId()]), $data, $headers);
    $this->assertEquals(200, $response->getStatusCode());
  }

  public function testFailUpdateQuestionAsUser() : void
  {
    $data = [
      "text" => "User updated!",
    ];

    $headers = [
      "Authorization" => "Bearer ".$this->getAuthToken("user2", "root")
    ];
    $quiz = $this->getResourceBy(Quiz::class, ["title" => "Test User1 Quiz!"]);
    $response = $this->request('PUT', $this->findOneIriBy(Question::class, ["quiz" => $quiz->getId()]), $data, $headers);
    $this->assertEquals(403, $response->getStatusCode());
  }

  public function testFailCreateQuestion() : void
  {
    $data = [
      "quiz" => $this->findOneIriBy(Quiz::class, ["title" => "Test User1 Quiz!"]),
      "text" => "User updated!",
    ];

    $jwt = $this->request('POST', '/api/questions', $data);
    $this->assertEquals(401, $jwt->getStatusCode());
  }

  public function testCreateQuestion() : void
  {
    $data = [
      "quiz" => $this->findOneIriBy(Quiz::class, ["title" => "Test User1 Quiz!"]),
      "text" => "Test question!",
    ];

    $headers = [
      "Authorization" => "Bearer ".$this->getAuthToken("user1", "root")
    ];

    $response = $this->request('POST', '/api/questions', $data, $headers);
    $this->assertEquals(201, $response->getStatusCode());
  }
}
