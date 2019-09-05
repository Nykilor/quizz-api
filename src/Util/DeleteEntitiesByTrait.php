<?php
namespace App\Util;

use Doctrine\Common\Persistence\ObjectManager;

trait DeleteEntitiesByTrait
{
  /**
   * Removes found entities from database for given query
   * @param  string        $resourceClass The resource class f.i. User::class
   * @param  array         $findBy        The query to findBy
   * @param  ObjectManager $em            The Doctrine ObjectManager
   */
  public function removeEntietiesBy(string $resourceClass, array $findBy, ObjectManager $em) : void
  {
    $repostiory = $em->getRepository($resourceClass);

    $entities = $repostiory->findBy($findBy);

    if(!(count($entities) > 0)) return;

    foreach ($entities as $key => $entity)
    {
      $this->em->remove($entity);

      if(($key+1 % 5) === 0)
      {
        $em->flush();
      }
    }

    $em->flush();
  }
}
