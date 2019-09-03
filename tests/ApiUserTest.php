<?php

namespace App\Tests;

use App\Util\ApiPlatformFunctionalTestTrait;

use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;


class ApiUserTest extends WebTestCase
{
    use ApiPlatformFunctionalTestTrait;

    /** @var Client */
    protected $client;

    public function testLogin() : void
    {
      $response = $this->request('POST', '/api/login', [
        "username" => "root",
        "password" => "root"
      ]);

      $this->assertEquals(200, $response->getStatusCode());
      $this->assertArrayHasKey('token', $json);
    }

    public function testRegister() : void
    {
      $response = $this->request('POST', '/api/register', [
        "username" => "test",
        "password" => "root",
        "email" => "test@test.pl"
      ]);

      $this->assertEquals(201, $response->getStatusCode());
    }
}
