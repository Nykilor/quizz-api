<?php
namespace App\Util;

use Symfony\Component\HttpFoundation\Response;

use Doctrine\Common\DataFixtures\Executor\ORMExecutor;

use Doctrine\Common\DataFixtures\Purger\ORMPurger;

use App\DataFixtures\DatabaseDummyValuesFixtures;

trait ApiPlatformFunctionalTestTrait
{
  protected function setUp()
  {
       parent::setUp();

       $this->client = static::createClient();

       $em = static::$container->get('doctrine.orm.default_entity_manager');
       $encoder = static::$container->get("security.user_password_encoder.generic");
       $fixture = new DatabaseDummyValuesFixtures($encoder);
       $purger = new ORMPurger($em);
       $executor = new ORMExecutor($em, $purger);
       $executor->execute([$fixture]);
  }

  protected function findOneIriBy(string $resourceClass, array $criteria) : string
  {
       $resource = static::$container->get('doctrine')->getRepository($resourceClass)->findOneBy($criteria);
       return static::$container->get('api_platform.iri_converter')->getIriFromitem($resource);
  }

  protected function getResourceBy(string $resourceClass, array $criteria) {
       $resource = static::$container->get('doctrine')->getRepository($resourceClass)->findOneBy($criteria);

       return $resource;
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
}
