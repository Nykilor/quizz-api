<?php

namespace App\Tests;

use App\Entity\Quiz;
use App\Entity\User;

use App\Util\ApiPlatformFunctionalTestTrait;

use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ApiQuizTest extends WebTestCase
{
  use ApiPlatformFunctionalTestTrait;

  /** @var Client */
  protected $client;

  public function testRetrieveQuizList() : void
  {
      $response = $this->request('GET', '/api/quizzes');
      $json = json_decode($response->getContent(), true);

      $this->assertEquals(200, $response->getStatusCode());
      $this->assertEquals('application/ld+json; charset=utf-8', $response->headers->get('Content-Type'));

      $one_entry = $json["hydra:member"][0];
      $this->assertArrayNotHasKey("isPublic", $one_entry);
      $this->assertArrayNotHasKey("disabled", $one_entry);
      $this->assertArrayNotHasKey("disablingReason", $one_entry);
  }

  public function testRetriveSingleQuiz() : void
  {
    $response = $this->request('GET', $this->findOneIriBy(Quiz::class, ["title" => "Test Admin Quiz!"]));
    $json = json_decode($response->getContent(), true);

    $this->assertEquals(200, $response->getStatusCode());
    $this->assertEquals('application/ld+json; charset=utf-8', $response->headers->get('Content-Type'));

    $this->assertArrayHasKey('questions', $json);
    $this->assertCount(1, $json['questions']);
  }

  public function testFailCreateQuiz() : void
  {
    $jwt = $this->request('POST', '/api/quizzes', []);
    $this->assertEquals(401, $jwt->getStatusCode());
  }

  public function testCreateQuiz() : void
  {
    $data = [
      "title" => "Api Test Quiz!",
      "tags" => "#api, #test",
      "description" => "test"
    ];

    $headers = [
      "Authorization" => "Bearer ".$this->getAuthToken()
    ];

    $response = $this->request('POST', '/api/quizzes', $data, $headers);
    $this->assertEquals(201, $response->getStatusCode());
  }

  public function testFailUpdateQuizWhenNotLoggedIn() : void
  {
    $jwt = $this->request('PUT', $this->findOneIriBy(Quiz::class, ["title" => "Test Admin Quiz!"]), []);
    $this->assertEquals(401, $jwt->getStatusCode());
  }

  public function testUpdateQuizAsAdmin() : void
  {
    $data = [
      "title" => "Test Admin Quiz!",
      "tags" => "#api, #test",
      "description" => "test"
    ];

    $headers = [
      "Authorization" => "Bearer ".$this->getAuthToken()
    ];

    $response = $this->request('PUT', $this->findOneIriBy(Quiz::class, ["title" => "Test Admin Quiz!"]), $data, $headers);
    $this->assertEquals(200, $response->getStatusCode());
  }

  public function testUpdateQuizAsOwner() : void
  {
    $data = [
      "title" => "Test User1 Quiz!",
      "tags" => "#api, #test, #updated",
      "description" => "test"
    ];

    $headers = [
      "Authorization" => "Bearer ".$this->getAuthToken("user1", "root")
    ];

    $response = $this->request('PUT', $this->findOneIriBy(Quiz::class, ["title" => "Test User1 Quiz!"]), $data, $headers);
    $this->assertEquals(200, $response->getStatusCode());
  }

  public function testFailUpdateQuizAsUser() : void
  {
    $data = [
      "title" => "Test User1 Quiz!",
      "tags" => "#api, #test, #updated",
      "description" => "test"
    ];

    $headers = [
      "Authorization" => "Bearer ".$this->getAuthToken("user2", "root")
    ];

    $response = $this->request('PUT', $this->findOneIriBy(Quiz::class, ["title" => "Test User1 Quiz!"]), $data, $headers);
    $this->assertEquals(403, $response->getStatusCode());
  }

  public function testFailDeleteQuizWhenNotLoggedIn() : void
  {
    $response = $this->request("DELETE", $this->findOneIriBy(Quiz::class, ["title" => "Test User1 Quiz!"]));
    $this->assertEquals(401, $response->getStatusCode());
  }

  public function testFailDeleteQuizAsUser() : void
  {
    $headers = [
      "Authorization" => "Bearer ".$this->getAuthToken("user1", "root")
    ];

    $response = $this->request("DELETE", $this->findOneIriBy(Quiz::class, ["title" => "Test Admin Quiz!"]), null, $headers);
    $this->assertEquals(403, $response->getStatusCode());
  }

  public function testDeleteQuizAsOwner() : void
  {
    $headers = [
      "Authorization" => "Bearer ".$this->getAuthToken("user2", "root")
    ];

    $response = $this->request("DELETE", $this->findOneIriBy(Quiz::class, ["title" => "Test User2 Quiz!"]), null, $headers);
    $this->assertEquals(204, $response->getStatusCode());
  }

  public function testDeleteQuizAsAdmin() : void
  {
    $headers = [
      "Authorization" => "Bearer ".$this->getAuthToken()
    ];

    $response = $this->request("DELETE", $this->findOneIriBy(Quiz::class, ["title" => "Test User1 Quiz!"]), null, $headers);
    $this->assertEquals(204, $response->getStatusCode());
  }

  public function testRetriveQuizWithTitleParameter() : void
  {
    $response = $this->request("GET", "/api/quizzes?title=Test");
    $this->assertEquals(200, $response->getStatusCode());

    $json = json_decode($response->getContent(), true);
    $this->assertEquals(3, $json["hydra:totalItems"]);
  }

  public function testRetriveQuizWithTagsParameter() : void
  {
    $response = $this->request("GET", "/api/quizzes?tags=test");
    $this->assertEquals(200, $response->getStatusCode());

    $json = json_decode($response->getContent(), true);
    $this->assertEquals(3, $json["hydra:totalItems"]);
  }

  public function testRetriveQuizWithDescriptionParameter() : void
  {
    $response = $this->request("GET", "/api/quizzes?tags=test");
    $this->assertEquals(200, $response->getStatusCode());

    $json = json_decode($response->getContent(), true);
    $this->assertEquals(3, $json["hydra:totalItems"]);
  }

  public function testRetriveQuizWithUserParameter() : void
  {
    $response = $this->request("GET", "/api/quizzes?user=".$this->findOneIriBy(User::class, ["username" => "user1"]));
    $this->assertEquals(200, $response->getStatusCode());

    $json = json_decode($response->getContent(), true);
    $this->assertEquals(1, $json["hydra:totalItems"]);
  }

  public function testRetriveQuizWithUserParameterAsQuizOwner() : void
  {
    $headers = [
      "Authorization" => "Bearer ".$this->getAuthToken("user1", "root")
    ];

    $response = $this->request("GET", "/api/quizzes?user=".$this->findOneIriBy(User::class, ["username" => "user1"]), null, $headers);
    $this->assertEquals(200, $response->getStatusCode());

    $json = json_decode($response->getContent(), true);
    $this->assertEquals(1, $json["hydra:totalItems"]);
  }
}
