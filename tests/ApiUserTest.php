<?php

namespace App\Tests;

use App\Entity\User;

use App\Util\ApiPlatformFunctionalTestTrait;

use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;


class ApiUserTest extends WebTestCase
{
    use ApiPlatformFunctionalTestTrait;

    /** @var Client */
    protected $client;
    //get token
    public function testLogin() : void
    {
      $response = $this->request('POST', '/api/login', [
        "username" => "root",
        "password" => "root"
      ]);
      $json = json_decode($response->getContent(), true);
      $this->assertEquals(200, $response->getStatusCode());
      $this->assertArrayHasKey('token', $json);
    }
    //create user
    public function testRegister() : void
    {
      $response = $this->request('POST', '/api/register', [
        "username" => "test",
        "password" => "root",
        "email" => "test@test.pl"
      ]);

      $this->assertEquals(201, $response->getStatusCode());
    }
    //get user
    public function testRetriveUserCollection() : void
    {
      $response = $this->request("GET", "/api/users");

      $json = json_decode($response->getContent(), true);

      $this->assertEquals(200, $response->getStatusCode());

      $member = $json["hydra:member"][0];
      $this->assertArrayHasKey("id", $member);
      $this->assertArrayHasKey("username", $member);
      $this->assertArrayHasKey("quizzes", $member);
    }

    public function testRetriveSingleUser() : void
    {
      $response = $this->request("GET", $this->findOneIriBy(User::class, ["username" => "root"]));
      $json = json_decode($response->getContent(), true);

      $this->assertEquals(200, $response->getStatusCode());
      $this->assertArrayHasKey("id", $json);
      $this->assertArrayHasKey("username", $json);
      $this->assertArrayHasKey("quizzes", $json);
    }
    //delete user
    public function testDeleteYourself() : void
    {
      $headers = [
        "Authorization" => "Bearer ".$this->getAuthToken("user1", "root")
      ];

      $response = $this->request('DELETE', $this->findOneIriBy(User::class, ["username" => "user1"]), null, $headers);
      $this->assertEquals(204, $response->getStatusCode());
    }

    public function testFailToDeleteUser() : void
    {
      $headers = [
        "Authorization" => "Bearer ".$this->getAuthToken("user2", "root")
      ];

      $response = $this->request('DELETE', $this->findOneIriBy(User::class, ["username" => "user1"]), null, $headers);
      $this->assertEquals(403, $response->getStatusCode());
    }

    public function testAdminDeleteUser() : void
    {
      $headers = [
        "Authorization" => "Bearer ".$this->getAuthToken("root", "root")
      ];

      $response = $this->request('DELETE', $this->findOneIriBy(User::class, ["username" => "user1"]), null, $headers);
      $this->assertEquals(204, $response->getStatusCode());
    }
    //users report collection
    public function testFailRetriveUserReportCollection() : void
    {
      $response = $this->request("GET", $this->findOneIriBy(User::class, ["username" => "user2"])."/reports");
      $this->assertEquals(401, $response->getStatusCode());
    }

    public function testRetriveUserReportCollectionAsOwner() : void
    {
      $headers = [
        "Authorization" => "Bearer ".$this->getAuthToken("user2", "root")
      ];

      $response = $this->request("GET", $this->findOneIriBy(User::class, ["username" => "user2"])."/reports", null, $headers);
      $this->assertEquals(200, $response->getStatusCode());
    }

    public function testRetriveUserReportCollectionAsAdmin() : void
    {
      $headers = [
        "Authorization" => "Bearer ".$this->getAuthToken("root", "root")
      ];

      $response = $this->request("GET", $this->findOneIriBy(User::class, ["username" => "user2"])."/reports", null, $headers);
      $this->assertEquals(200, $response->getStatusCode());
    }

    public function testFailRetriveUserReportCollectionAsOtherUser() : void
    {
      $headers = [
        "Authorization" => "Bearer ".$this->getAuthToken("user1", "root")
      ];

      $response = $this->request("GET", $this->findOneIriBy(User::class, ["username" => "user2"])."/reports", null, $headers);
      $this->assertEquals(403, $response->getStatusCode());
    }
    //users media collection
    public function testFailRetriveUserMediaObjectCollection() : void
    {
      $response = $this->request("GET", $this->findOneIriBy(User::class, ["username" => "user2"])."/media_objects");
      $this->assertEquals(401, $response->getStatusCode());
    }

    public function testRetriveUserMediaObjectCollectionAsOwner() : void
    {
      $headers = [
        "Authorization" => "Bearer ".$this->getAuthToken("user2", "root")
      ];

      $response = $this->request("GET", $this->findOneIriBy(User::class, ["username" => "user2"])."/media_objects", null, $headers);
      $this->assertEquals(200, $response->getStatusCode());
    }

    public function testRetriveUserMediaObjectCollectionAsAdmin() : void
    {
      $headers = [
        "Authorization" => "Bearer ".$this->getAuthToken("root", "root")
      ];

      $response = $this->request("GET", $this->findOneIriBy(User::class, ["username" => "user2"])."/media_objects", null, $headers);
      $this->assertEquals(200, $response->getStatusCode());
    }

    public function testFailRetriveUserMediaObjectCollectionAsOtherUser() : void
    {
      $headers = [
        "Authorization" => "Bearer ".$this->getAuthToken("user1", "root")
      ];

      $response = $this->request("GET", $this->findOneIriBy(User::class, ["username" => "user2"])."/media_objects", null, $headers);
      $this->assertEquals(403, $response->getStatusCode());
    }
    //quizzes
    public function testFailRetriveUserQuizzesCollection() : void
    {
      $response = $this->request("GET", $this->findOneIriBy(User::class, ["username" => "user2"])."/quizzes");
      $this->assertEquals(401, $response->getStatusCode());
    }

    public function testRetriveUserQuizzesCollectionAsOwner() : void
    {
      $headers = [
        "Authorization" => "Bearer ".$this->getAuthToken("user2", "root")
      ];

      $response = $this->request("GET", $this->findOneIriBy(User::class, ["username" => "user2"])."/quizzes", null, $headers);

      $json = json_decode($response->getContent(), true);
      $single_result = $json["hydra:member"][0];
      $this->assertArrayHasKey("disabled", $single_result);
      $this->assertArrayHasKey("isPublic", $single_result);
      $this->assertArrayHasKey("disablingReason", $single_result);

      $this->assertEquals(200, $response->getStatusCode());

    }

    public function testRetriveUserQuizzesCollectionAsAdmin() : void
    {
      $headers = [
        "Authorization" => "Bearer ".$this->getAuthToken("root", "root")
      ];

      $response = $this->request("GET", $this->findOneIriBy(User::class, ["username" => "user2"])."/quizzes", null, $headers);

      $json = json_decode($response->getContent(), true);
      $single_result = $json["hydra:member"][0];
      $this->assertArrayHasKey("disabled", $single_result);
      $this->assertArrayHasKey("isPublic", $single_result);
      $this->assertArrayHasKey("disablingReason", $single_result);

      $this->assertEquals(200, $response->getStatusCode());
    }

    public function testFailRetriveUserQuizzesCollectionAsOtherUser() : void
    {
      $headers = [
        "Authorization" => "Bearer ".$this->getAuthToken("user1", "root")
      ];

      $response = $this->request("GET", $this->findOneIriBy(User::class, ["username" => "user2"])."/quizzes", null, $headers);
      $this->assertEquals(403, $response->getStatusCode());
    }

    public function testBanUser() : void
    {
      $headers = [
        "Authorization" => "Bearer ".$this->getAuthToken()
      ];

      $data = [
        "bannedTill" => "2030-09-11",
        "banReason" => "You're a twat"
      ];

      $response = $this->request("PUT", $this->findOneIriBy(User::class, ["username" => "user2"])."/ban", $data, $headers);
      $this->assertEquals(200, $response->getStatusCode());
    }

    public function testLoginAsBannedUser() : void
    {
      $response = $this->request('POST', '/api/login', [
        "username" => "user3",
        "password" => "root"
      ]);

      $this->assertEquals(403, $response->getStatusCode());
    }

    public function testIfUsersTokenGetsDeactivatedAfterBan() : void
    {
      $headers = [
        "Authorization" => "Bearer ".$this->getAuthToken("user2", "root")
      ];

      $response = $this->request("GET", $this->findOneIriBy(User::class, ["username" => "user2"])."/quizzes", null, $headers);
      $this->assertEquals(200, $response->getStatusCode());

      $this->testBanUser();

      $response_banned = $this->request("GET", $this->findOneIriBy(User::class, ["username" => "user2"])."/quizzes", null, $headers);
      $this->assertEquals(403, $response_banned->getStatusCode());
    }
}
