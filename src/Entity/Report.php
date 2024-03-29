<?php

namespace App\Entity;

use DateTime;

use ApiPlatform\Core\Annotation\ApiFilter;

use App\Entity\HasOwnerInterface;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\BooleanFilter;

/**
 * @ApiResource(
 * subresourceOperations={
 *   "api_users_reports_get_subresource"= {
 *     "access_control"="is_granted('ROLE_ADMIN') or (user != 'anon.' and user.getId() == id)"
 *   }
 *  },
 *  normalizationContext={
 *    "groups"={"report_read"}, "enable_max_depth"=true
 *  },
 *  attributes={
 *    "access_control"="is_granted('ROLE_USER')",
 *    "access_control_message"="Only registered users can access this endpoint."
 *  },
 *  itemOperations={
 *    "get"={
 *      "access_control"="is_granted('ROLE_ADMIN') or object.getUser() == user"
 *    },
 *    "resolve_report"={
 *      "method"="PUT",
 *      "access_control"="is_granted('ROLE_ADMIN')",
 *      "denormalization_context"={
 *        "groups"={"report_put"}
 *      },
 *      "path"="/reports/{id}/resolve"
 *    },
 *    "delete"={
 *      "access_control"="is_granted('ROLE_ADMIN')"
 *    }
 *  },
 *  collectionOperations={
 *    "get"={
 *      "access_control"="is_granted('ROLE_ADMIN')"
 *    },
 *    "post"={
 *      "denormalization_context"={
 *        "groups"={"report_save"}
 *      }
 *    }
 *  }
 * )
 * @ApiFilter(SearchFilter::class, properties={"reason": "partial", "description": "partial", "user": "exact", "quiz": "exact"})
 * @ApiFilter(BooleanFilter::class, properties={"resolved"})
 * @ORM\Entity(repositoryClass="App\Repository\ReportRepository")
 */
class Report implements HasOwnerInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"report_read"})
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Quiz")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"report_save", "report_read"})
     * @MaxDepth(1)
     */
    private $quiz;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="reports")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"report_read"})
     * @MaxDepth(1)
     */
    private $user;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"report_save", "report_read"})
     */
    private $reason;

    /**
     * @ORM\Column(type="text")
     * @Groups({"report_save", "report_read"})
     */
    private $description;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"report_read"})
     */
    private $resolved = false;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @Groups({"report_read"})
     * @MaxDepth(1)
     */
    private $resolvedBy;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"report_read", "report_put"})
     */
    private $resolveResponse;

    /**
     * @ORM\Column(type="date")
     * @Groups({"report_read"})
     */
    private $creationDate;

    public function __construct()
    {
      $this->creationDate = new DateTime;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getQuiz(): ?Quiz
    {
        return $this->quiz;
    }

    public function setQuiz(?Quiz $quiz): self
    {
        $this->quiz = $quiz;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): HasOwnerInterface
    {
        $this->user = $user;

        return $this;
    }

    public function getReason(): ?string
    {
        return $this->reason;
    }

    public function setReason(string $reason): self
    {
        $this->reason = $reason;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getResolved(): ?bool
    {
        return $this->resolved;
    }

    public function setResolved(bool $resolved): self
    {
        $this->resolved = $resolved;

        return $this;
    }

    public function getResolvedBy(): ?User
    {
        return $this->resolvedBy;
    }

    public function setResolvedBy(?User $resolvedBy): self
    {
        $this->resolvedBy = $resolvedBy;

        return $this;
    }

    public function getResolveResponse(): ?string
    {
        return $this->resolveResponse;
    }

    public function setResolveResponse(?string $resolveResponse): self
    {
        $this->resolveResponse = $resolveResponse;

        return $this;
    }

    public function getCreationDate(): ?\DateTimeInterface
    {
        return $this->creationDate;
    }
}
