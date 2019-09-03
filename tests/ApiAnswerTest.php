<?php

namespace App\Tests;

use App\Entity\Quiz;
use App\Entity\Answer;
use App\Entity\Question;

use App\Util\ApiPlatformFunctionalTestTrait;

use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ApiAnswerTest extends WebTestCase
{
  use ApiPlatformFunctionalTestTrait;

  /** @var Client */
  protected $client;

  public function testRetriveSingleAnswer() : void
  {
    $response = $this->request('GET', $this->findOneIriBy(Answer::class, ["text" => "Yes"]));

    $this->assertEquals(200, $response->getStatusCode());
    $this->assertEquals('application/ld+json; charset=utf-8', $response->headers->get('Content-Type'));
  }

  public function testFailDeleteAnswerWhenNotLoggedIn() : void
  {
    $response = $this->request("DELETE", $this->findOneIriBy(Answer::class, ["text" => "Yes"]));
    $this->assertEquals(401, $response->getStatusCode());
  }

  public function testFailDeleteAnswerAsUser() : void
  {
    $headers = [
      "Authorization" => "Bearer ".$this->getAuthToken("user1", "root")
    ];

    $quiz = $this->getResourceBy(Quiz::class, ["title" => "Test Admin Quiz!"]);
    $question = $this->getResourceBy(Question::class, ["quiz" => $quiz->getId()]);

    $response = $this->request("DELETE", $this->findOneIriBy(Answer::class, ["question" => $question->getId()]), null, $headers);
    $this->assertEquals(403, $response->getStatusCode());
  }

  public function testDeleteAnswerAsOwner() : void
  {
    $headers = [
      "Authorization" => "Bearer ".$this->getAuthToken("user2", "root")
    ];

    $quiz = $this->getResourceBy(Quiz::class, ["title" => "Test User2 Quiz!"]);
    $question = $this->getResourceBy(Question::class, ["quiz" => $quiz->getId()]);

    $response = $this->request("DELETE", $this->findOneIriBy(Answer::class, ["question" => $question->getId()]), null, $headers);
    $this->assertEquals(204, $response->getStatusCode());
  }

  public function testDeleteAnswerAsAdmin() : void
  {
    $headers = [
      "Authorization" => "Bearer ".$this->getAuthToken()
    ];

    $quiz = $this->getResourceBy(Quiz::class, ["title" => "Test User2 Quiz!"]);
    $question = $this->getResourceBy(Question::class, ["quiz" => $quiz->getId()]);

    $response = $this->request("DELETE", $this->findOneIriBy(Answer::class, ["question" => $question->getId()]), null, $headers);
    $this->assertEquals(204, $response->getStatusCode());
  }

  public function testFailUpdateAnswerWhenNotLoggedIn() : void
  {
    $jwt = $this->request('PUT', $this->findOneIriBy(Answer::class, ["text" => "Yes"]), ["text" => "Maybe"]);
    $this->assertEquals(401, $jwt->getStatusCode());
  }

  public function testUpdateAnswerAsAdmin() : void
  {
    $data = [
      "text" => "Admin updated!",
    ];

    $headers = [
      "Authorization" => "Bearer ".$this->getAuthToken()
    ];

    $response = $this->request('PUT', $this->findOneIriBy(Answer::class, ["text" => "Yes"]), $data, $headers);
    $this->assertEquals(200, $response->getStatusCode());
  }

  public function testUpdateAnswerAsOwner() : void
  {
    $data = [
      "text" => "Owner updated!",
    ];

    $headers = [
      "Authorization" => "Bearer ".$this->getAuthToken("user1", "root")
    ];

    $quiz = $this->getResourceBy(Quiz::class, ["title" => "Test User1 Quiz!"]);
    $question = $this->getResourceBy(Question::class, ["quiz" => $quiz->getId()]);

    $response = $this->request('PUT', $this->findOneIriBy(Answer::class, ["question" => $question->getId()]), $data, $headers);
    $this->assertEquals(200, $response->getStatusCode());
  }

  public function testFailUpdateAnswerAsUser() : void
  {
    $data = [
      "text" => "User updated!",
    ];

    $headers = [
      "Authorization" => "Bearer ".$this->getAuthToken("user2", "root")
    ];

    $quiz = $this->getResourceBy(Quiz::class, ["title" => "Test User1 Quiz!"]);
    $question = $this->getResourceBy(Question::class, ["quiz" => $quiz->getId()]);

    $response = $this->request('PUT', $this->findOneIriBy(Answer::class, ["question" => $question->getId()]), $data, $headers);
    $this->assertEquals(403, $response->getStatusCode());
  }

  public function testFailCreateAnswer() : void
  {
    $data = [
      "question" => $this->findOneIriBy(Question::class, ["text" => "Is 100kg of feathers lighter than 100 kg of steel ?"]),
      "text" => "User updated!",
    ];

    $jwt = $this->request('POST', '/api/answers', $data);
    $this->assertEquals(401, $jwt->getStatusCode());
  }

  public function testCreateAnswer() : void
  {
    $quiz = $this->getResourceBy(Quiz::class, ["title" => "Test User1 Quiz!"]);

    $data = [
      "question" => $this->findOneIriBy(Question::class, ["quiz" => $quiz->getId()]),
      "text" => "Test answer!",
      "is_answer" => true
    ];

    $headers = [
      "Authorization" => "Bearer ".$this->getAuthToken("user1", "root")
    ];

    $response = $this->request('POST', '/api/answers', $data, $headers);
    $this->assertEquals(201, $response->getStatusCode());
  }
}
