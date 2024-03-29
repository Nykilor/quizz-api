<?php
// api/src/Serializer/BookContextBuilder.php

namespace App\Serializer;

use ApiPlatform\Core\Serializer\SerializerContextBuilderInterface;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

final class AdminFieldAccessContextBuilder implements SerializerContextBuilderInterface
{
    private $decorated;
    private $authorizationChecker;

    public function __construct(SerializerContextBuilderInterface $decorated, AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->decorated = $decorated;
        $this->authorizationChecker = $authorizationChecker;
    }

    public function createFromRequest(Request $request, bool $normalization, ?array $extractedAttributes = null): array
    {
        $context = $this->decorated->createFromRequest($request, $normalization, $extractedAttributes);
        $subject = $request->attributes->get('data');

        if (isset($context['groups']) && $this->authorizationChecker->isGranted('ROLE_ADMIN')) {
          $context['groups'][] = $normalization ? 'admin_read' : 'admin_write';
        }

        return $context;
    }
}
