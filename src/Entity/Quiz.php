<?php

namespace App\Entity;

use DateTime;
use App\Entity\HasOwnerInterface;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiSubresource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Controller\CreateQuizController;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;

/**
 * @ApiResource(
 *  itemOperations={
 *    "PUT",
 *    "DELETE",
 *    "GET"={
 *      "normalization_context"={
 *        "groups"={"quizzes_read_single"}, "enable_max_depth"=true
 *      },
 *    }
 *  },
 *  collectionOperations={
 *    "GET"={
 *      "normalization_context"={
 *        "groups"={"quizzes_read_all"}, "enable_max_depth"=true
 *      }
 *    },
 *    "post_quizz"={
 *      "method"="POST",
 *      "access_control"="is_granted('ROLE_USER')",
 *      "access_control_message"="Only registered users can create Quizzes.",
 *      "denormalization_context"={
 *        "groups"={"quizzes_save_user_single"},
 *      },
 *      "normalization_context"={
 *        "groups"={"post_quizz"}, "enable_max_depth"=true
 *      }
 *    }
 *  }
 * )
 * @ORM\Entity(repositoryClass="App\Repository\QuizRepository")
 */
class Quiz implements HasOwnerInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"quizzes_read_all", "quizzes_read_single", "post_quizz"})
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="quizzes")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"quizzes_read_all", "quizzes_read_single", "post_quizz"})
     * @MaxDepth(1)
     */
    private $user;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"quizzes_read_all", "quizzes_read_single", "quizzes_save_user_single", "post_quizz"})
     */
    private $title;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"quizzes_read_all", "quizzes_read_single", "quizzes_save_user_single", "post_quizz"})
     */
    private $tags;

    /**
     * @ORM\Column(type="text")
     * @Groups({"quizzes_read_all", "quizzes_read_single", "quizzes_save_user_single", "post_quizz"})
     */
    private $description;

    /**
     * @ORM\Column(type="integer")
     * @Groups({"quizzes_read_all", "quizzes_read_single", "quizzes_save_user_single", "post_quizz"})
     */
    private $type = 0;

    /**
     * @var MediaObject|null
     *
     * @ORM\ManyToOne(targetEntity=MediaObject::class)
     * @ORM\JoinColumn(nullable=true)
     * @ApiProperty(iri="http://schema.org/image")
     * @Groups({"quizzes_read_all", "quizzes_read_single", "quizzes_save_user_single", "post_quizz"})
     */
    private $photo;

    /**
     * @ORM\Column(type="date")
     * @Groups({"quizzes_read_all", "quizzes_read_single", "post_quizz"})
     */
    private $creation_date;

    /**
     * @ORM\Column(type="date")
     * @Groups({"quizzes_read_all", "quizzes_read_single", "post_quizz"})
     */
    private $update_date;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"quizzes_read_all", "quizzes_read_single", "quizzes_save_user_single", "post_quizz"})
     */
    private $is_public = true;

    /**
     * @ORM\Column(type="boolean")
     */
    private $disabled = false;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $disabling_reason;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Question", mappedBy="Quiz", orphanRemoval=true)
     * @Groups({"quizzes_read_single"})
     * @MaxDepth(3)
     * @ApiSubresource
     */
    private $questions;

    public function __construct()
    {
        $this->questions = new ArrayCollection();
        $date = new DateTime();
        $this->creation_date = $date;
        $this->update_date = $date;
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getTags(): ?string
    {
        return $this->tags;
    }

    public function setTags(?string $tags): self
    {
        $this->tags = $tags;

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

    public function getType(): ?int
    {
        return $this->type;
    }

    public function setType(int $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getPhoto(): ?MediaObject
    {
        return $this->photo;
    }

    public function setPhoto(?MediaObject $photo): self
    {
        $this->photo = $photo;

        return $this;
    }

    public function getCreationDate(): ?\DateTimeInterface
    {
        return $this->creation_date;
    }

    public function getUpdateDate(): ?\DateTimeInterface
    {
        return $this->update_date;
    }

    public function setUpdateDate(\DateTimeInterface $update_date): self
    {
        $this->update_date = $update_date;

        return $this;
    }

    public function getIsPublic(): ?bool
    {
        return $this->is_public;
    }

    public function setIsPublic(bool $is_public): self
    {
        $this->is_public = $is_public;

        return $this;
    }

    public function getDisabled(): ?bool
    {
        return $this->disabled;
    }

    public function setDisabled(bool $disabled): self
    {
        $this->disabled = $disabled;

        return $this;
    }

    public function getDisablingReason(): ?string
    {
        return $this->disabling_reason;
    }

    public function setDisablingReason(?string $disabling_reason): self
    {
        $this->disabling_reason = $disabling_reason;

        return $this;
    }

    /**
     * @return Collection|Question[]
     */
    public function getQuestions(): Collection
    {
        return $this->questions;
    }

    public function addQuestion(Question $question): self
    {
        if (!$this->questions->contains($question)) {
            $this->questions[] = $question;
            $question->setQuizID($this);
        }

        return $this;
    }

    public function removeQuestion(Question $question): self
    {
        if ($this->questions->contains($question)) {
            $this->questions->removeElement($question);
            // set the owning side to null (unless already changed)
            if ($question->getQuizID() === $this) {
                $question->setQuizID(null);
            }
        }

        return $this;
    }
}
