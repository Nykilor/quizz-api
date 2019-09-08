<?php
// api/src/Serializer/BookAttributeNormalizer.php

namespace App\Serializer;

use App\Entity\HasOwnerInterface;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class OwnerFieldNormalizer implements ContextAwareNormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    private const ALREADY_CALLED = 'OWNER_ATTRIBUTE_NORMALIZER_ALREADY_CALLED';

    private $tokenStorage;

    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    public function normalize($object, $format = null, array $context = [])
    {
        if ($this->userIsOwner($object)) {
            $context['groups'][] = 'owner_read';
        }

        $context[self::ALREADY_CALLED] = true;

        return $this->normalizer->normalize($object, $format, $context);
    }

    public function supportsNormalization($data, $format = null, array $context = [])
    {
        // Make sure we're not called twice
        if (isset($context[self::ALREADY_CALLED])) {
            return false;
        }

        return $data instanceof HasOwnerInterface;
    }

    private function userIsOwner($object): bool
    {
      $user = $this->tokenStorage->getToken()->getUser();

      $user_id = ($user !== "anon.") ? $user->getId() : null;
      $owner_id = $object->getUser()->getId();

      return ($user_id === $owner_id) ? true : false;
    }
}
