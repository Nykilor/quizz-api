<?php

namespace App\Tests;

use App\Entity\Quiz;
use App\Entity\User;
use App\Entity\Report;

use App\Util\ApiPlatformFunctionalTestTrait;

use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ApiReportTest extends WebTestCase
{
  use ApiPlatformFunctionalTestTrait;

  /** @var Client */
  protected $client;

  public function testRetriveReportsWhenNotLoggedIn() : void
  {
    $response = $this->request('GET', "/api/reports");

    $this->assertEquals(401, $response->getStatusCode());
  }

  public function testRetriveReportsWhenLoggedInAsUser() : void
  {
    $headers = [
      "Authorization" => "Bearer ".$this->getAuthToken("user2", "root")
    ];

    $response = $this->request('GET', "/api/reports", null, $headers);
    $this->assertEquals(403, $response->getStatusCode());
  }

  public function testCreateReportAsUser() : void
  {
    $data = [
      "quiz" => $this->findOneIriBy(Quiz::class, ["title" => "Test User1 Quiz!"]),
      "reason" => "test",
      "description" => "test"
    ];

    $headers = [
      "Authorization" => "Bearer ".$this->getAuthToken("user2", "root")
    ];

    $response = $this->request('POST', "/api/reports", $data, $headers);
    $this->assertEquals(201, $response->getStatusCode());
  }

  public function testFailCreateReportWhenNotLoggedIn() : void
  {
    $data = [
      "quiz" => $this->findOneIriBy(Quiz::class, ["title" => "Test User1 Quiz!"]),
      "reason" => "test",
      "description" => "test"
    ];

    $response = $this->request('POST', "/api/reports", $data);
    $this->assertEquals(401, $response->getStatusCode());
  }

  public function testFailRetriveSingleReportWhenLoggedInAsNotOwner() : void
  {
    $headers = [
      "Authorization" => "Bearer ".$this->getAuthToken("user1", "root")
    ];

    $response = $this->request('GET', $this->findOneIriBy(Report::class, ["reason" => "He said mean things to me UwU."]), null, $headers);
    $this->assertEquals(403, $response->getStatusCode());
  }

  public function testRetriveSingleReportAsOwner() : void
  {
    $headers = [
      "Authorization" => "Bearer ".$this->getAuthToken("user2", "root")
    ];

    $response = $this->request('GET', $this->findOneIriBy(Report::class, ["reason" => "He said mean things to me UwU."]), null, $headers);
    $this->assertEquals(200, $response->getStatusCode());
  }

  public function testFailDeleteReportAsOwner() : void
  {
    $headers = [
      "Authorization" => "Bearer ".$this->getAuthToken("user2", "root")
    ];

    $response = $this->request('DELETE', $this->findOneIriBy(Report::class, ["reason" => "He said mean things to me UwU."]), null, $headers);
    $this->assertEquals(403, $response->getStatusCode());
  }

  public function testFailResovleReportAsOwner() : void
  {
    $data = [
      "resolveResponse" => "test"
    ];

    $headers = [
      "Authorization" => "Bearer ".$this->getAuthToken("user2", "root")
    ];

    $response = $this->request('PUT', $this->findOneIriBy(Report::class, ["reason" => "He said mean things to me UwU."])."/resolve", $data, $headers);
    $this->assertEquals(403, $response->getStatusCode());
  }

  public function testResovleReportAsAdmin() : void
  {
    $data = [
      "resolveResponse" => "test"
    ];

    $headers = [
      "Authorization" => "Bearer ".$this->getAuthToken()
    ];

    $response = $this->request('PUT', $this->findOneIriBy(Report::class, ["reason" => "He said mean things to me UwU."])."/resolve", $data, $headers);
    $this->assertEquals(200, $response->getStatusCode());
  }

  public function testRetriveReportWithReasonParameter() : void
  {
    $headers = [
      "Authorization" => "Bearer ".$this->getAuthToken()
    ];

    $response = $this->request("GET", "/api/reports?reason=UwU", null, $headers);
    $this->assertEquals(200, $response->getStatusCode());

    $json = json_decode($response->getContent(), true);
    $this->assertEquals(1, $json["hydra:totalItems"]);
  }

  public function testRetriveReportWithDescriptionParameter() : void
  {
    $headers = [
      "Authorization" => "Bearer ".$this->getAuthToken()
    ];

    $response = $this->request("GET", "/api/reports?description=Things", null, $headers);
    $this->assertEquals(200, $response->getStatusCode());

    $json = json_decode($response->getContent(), true);
    $this->assertEquals(1, $json["hydra:totalItems"]);
  }

  public function testRetriveReportWithUserParameter() : void
  {
    $headers = [
      "Authorization" => "Bearer ".$this->getAuthToken()
    ];

    $response = $this->request("GET", "/api/reports?user=".$this->findOneIriBy(User::class, ["username" => "user2"]), null, $headers);
    $this->assertEquals(200, $response->getStatusCode());

    $json = json_decode($response->getContent(), true);
    $this->assertEquals(1, $json["hydra:totalItems"]);
  }

  public function testRetriveReportWithQuizParameter() : void
  {
    $headers = [
      "Authorization" => "Bearer ".$this->getAuthToken()
    ];

    $response = $this->request("GET", "/api/reports?quiz=".$this->findOneIriBy(Quiz::class, ["title" => "Test User1 Quiz!"]), null, $headers);
    $this->assertEquals(200, $response->getStatusCode());

    $json = json_decode($response->getContent(), true);
    $this->assertEquals(1, $json["hydra:totalItems"]);
  }

  public function testRetriveReportWithResolvedParameter() : void
  {
    $headers = [
      "Authorization" => "Bearer ".$this->getAuthToken()
    ];

    $response = $this->request("GET", "/api/reports?resolved=true", null, $headers);
    $this->assertEquals(200, $response->getStatusCode());

    $json = json_decode($response->getContent(), true);
    $this->assertEquals(0, $json["hydra:totalItems"]);
  }

}
