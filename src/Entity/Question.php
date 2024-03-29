<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ApiResource(
 *  collectionOperations={
 *    "post"={
 *      "method"="POST",
 *      "access_control"="is_granted('ROLE_ADMIN') or object.getQuiz().getUser() == user",
 *      "normalization_context"={
 *        "groups"={"questions_read_create"}
 *      },
 *      "denormalization_context"={
 *        "groups"={"questions_write_create"}
 *      }
 *    }
 *  },
 *  itemOperations={
 *    "get",
 *    "put"={
 *      "method"="PUT",
 *      "access_control"="is_granted('ROLE_ADMIN') or object.getQuiz().getUser() == user",
 *      "normalization_context"={
 *        "groups"={"questions_read_update"}
 *      },
 *      "denormalization_context"={
 *        "groups"={"questions_write_update"}
 *      }
 *    },
 *    "delete"={
 *      "method"="DELETE",
 *      "access_control"="is_granted('ROLE_ADMIN') or object.getQuiz().getUser() == user"
 *    }
 *  }
 * )
 * @ORM\Entity(repositoryClass="App\Repository\QuestionRepository")
 */
class Question
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"quizzes_read_single", "questions_read_create"})
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Quiz", inversedBy="questions")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"questions_write_create", "questions_write_update"})
     */
    private $quiz;

    /**
     * @ORM\Column(type="text")
     * @Groups({"quizzes_read_single", "questions_read_create", "questions_write_create", "questions_write_update"})
     */
    private $text;

    /**
     * @var MediaObject|null
     *
     * @ORM\ManyToOne(targetEntity=MediaObject::class)
     * @ORM\JoinColumn(nullable=true)
     * @ApiProperty(iri="http://schema.org/image")
     * @Groups({"quizzes_read_single", "questions_read_create", "questions_write_create", "questions_write_update"})
     */
    private $photo;

    /**
     * @ORM\Column(type="json", nullable=true)
     * @Groups({"quizzes_read_single", "questions_read_create", "questions_write_create", "questions_write_update"})
     */
    private $chart = [];

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Answer", mappedBy="question", orphanRemoval=true)
     * @Groups({"quizzes_read_single"})
     */
    private $answers;

    public function __construct()
    {
        $this->answers = new ArrayCollection();
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

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(string $text): self
    {
        $this->text = $text;

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

    public function getChart(): ?array
    {
        return $this->chart;
    }

    public function setChart(?array $chart): self
    {
        $this->chart = $chart;

        return $this;
    }

    /**
     * @return Collection|Answer[]
     */
    public function getAnswers(): Collection
    {
        return $this->answers;
    }

    public function addAnswer(Answer $answer): self
    {
        if (!$this->answers->contains($answer)) {
            $this->answers[] = $answer;
            $answer->setQuestionID($this);
        }

        return $this;
    }

    public function removeAnswer(Answer $answer): self
    {
        if ($this->answers->contains($answer)) {
            $this->answers->removeElement($answer);
            // set the owning side to null (unless already changed)
            if ($answer->getQuestionID() === $this) {
                $answer->setQuestionID(null);
            }
        }

        return $this;
    }
}
