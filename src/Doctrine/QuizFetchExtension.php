<?php
// api/src/Doctrine/CurrentUserExtension.php

namespace App\Doctrine;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use App\Entity\Quiz;
use Doctrine\ORM\QueryBuilder;

use Symfony\Component\Routing\RequestContext;

use Symfony\Component\Security\Core\Security;

final class QuizFetchExtension implements QueryCollectionExtensionInterface, QueryItemExtensionInterface
{
    private $security;
    private $request;

    public function __construct(Security $security, RequestContext $request)
    {
        $this->security = $security;
        $this->request = $request;
    }

    public function applyToCollection(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, string $operationName = null)
    {
        $this->addWhere($queryBuilder, $resourceClass, $operationName);
    }

    public function applyToItem(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, array $identifiers, string $operationName = null, array $context = [])
    {
        $this->addWhere($queryBuilder, $resourceClass, $operationName);
    }

    private function addWhere(QueryBuilder $queryBuilder, string $resourceClass, string $operationName): void
    {
        if (Quiz::class !== $resourceClass && $this->security->isGranted('ROLE_ADMIN')) {
            return;
        }

        $rootAlias = $queryBuilder->getRootAliases()[0];
        
        //Show only public and not disabled Quizzes for all, and private, public and disabled for the owner.
        if($this->request->getPathInfo() === "/api/quizzes") {
          $queryBuilder->andWhere(sprintf('%s.isPublic = :public', $rootAlias));
          $queryBuilder->setParameter('public', "1");
          $queryBuilder->andWhere(sprintf('%s.disabled = :disabled', $rootAlias));
          $queryBuilder->setParameter('disabled', "0");
        }
    }
}
