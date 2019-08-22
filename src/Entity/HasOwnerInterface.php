<?php
namespace App\Entity;
/**
 * Entity with this interface has got to have an owner.
 */
interface HasOwnerInterface
{
  public function setUser(?User $User) : HasOwnerInterface;
  public function getUser() : ?User;
}
