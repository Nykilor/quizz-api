<?php

namespace App\Tests;

use App\Entity\Quiz;

use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class ApiTest extends WebTestCase
{
  /** @var Client */
  protected $client;

  public function loginTest() : void
  {
    $response = $this->request('POST', '/api/login', [
      "username" => "root",
      "password" => "root"
    ]);

    $json = json_decode($response->getContent(), true);
    $this->assertEquals(200, $response->getStatusCode());
    $this->assertArrayHasKey('token', $json);
  }

  public function testRetrieveQuizList() : void
  {
      $response = $this->request('GET', '/api/quizzes');
      $json = json_decode($response->getContent(), true);

      $this->assertEquals(200, $response->getStatusCode());
      $this->assertEquals('application/ld+json; charset=utf-8', $response->headers->get('Content-Type'));
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

  public function testFailAddQuiz() : void
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

  /**
    * @param string|array|null $content
    */
   protected function request(string $method, string $uri, $content = null, array $headers = []): Response
   {
       $server = ['CONTENT_TYPE' => 'application/ld+json', 'HTTP_ACCEPT' => 'application/ld+json'];
       foreach ($headers as $key => $value) {
           if (strtolower($key) === 'content-type') {
               $server['CONTENT_TYPE'] = $value;

               continue;
           }

           $server['HTTP_'.strtoupper(str_replace('-', '_', $key))] = $value;
       }

       if (is_array($content) && false !== preg_match('#^application/(?:.+\+)?json$#', $server['CONTENT_TYPE'])) {
           $content = json_encode($content);
       }

       $this->client->request($method, $uri, [], [], $server, $content);

       return $this->client->getResponse();
   }

   protected function setUp()
   {
        parent::setUp();

        $this->client = static::createClient();
   }

   protected function findOneIriBy(string $resourceClass, array $criteria): string
   {
        $resource = static::$container->get('doctrine')->getRepository($resourceClass)->findOneBy($criteria);
        return static::$container->get('api_platform.iri_converter')->getIriFromitem($resource);
   }

   protected function getAuthToken(string $username = "root", string $password = "root") : string
   {
     $this->client->request(
      'POST',
      '/api/login',
      [],
      [],
      ['CONTENT_TYPE' => 'application/json'],
      json_encode([
        'username' => $username,
        'password' => $password,
      ])
      );

      $data = json_decode($this->client->getResponse()->getContent(), true);

      return $data["token"];
   }
}
