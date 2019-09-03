<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ApiResource(
 *  collectionOperations={
 *    "post"={
 *      "method"="POST",
 *      "access_control"="is_granted('ROLE_ADMIN') or object.getQuestion().getQuiz().getUser() == user",
 *      "normalization_context"={
 *        "groups"={"answer_read_create"}
 *      },
 *      "denormalization_context"={
 *        "groups"={"answer_write_create"}
 *      }
 *    },
 *  },
 *  itemOperations={
 *    "get",
 *    "put"={
 *      "method"="PUT",
 *      "access_control"="is_granted('ROLE_ADMIN') or object.getQuestion().getQuiz().getUser() == user",
 *      "normalization_context"={
 *        "groups"={"answer_read_update"}
 *      },
 *      "denormalization_context"={
 *        "groups"={"answer_write_update"}
 *      }
 *    },
 *    "delete"={
 *      "method"="DELETE",
 *      "access_control"="is_granted('ROLE_ADMIN') or object.getQuestion().getQuiz().getUser() == user"
 *    }
 *  }
 * )
 * @ORM\Entity(repositoryClass="App\Repository\AnswerRepository")
 */
class Answer
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"quizzes_read_single", "answer_read_create"})
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Question", inversedBy="answers")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"answer_write_update", "answer_write_create"})
     */
    private $question;

    /**
     * @ORM\Column(type="text")
     * @Groups({"quizzes_read_single", "answer_write_update", "answer_read_create", "answer_write_create"})
     */
    private $text;

    /**
     * @var MediaObject|null
     *
     * @ORM\ManyToOne(targetEntity=MediaObject::class)
     * @ORM\JoinColumn(nullable=true)
     * @ApiProperty(iri="http://schema.org/image")
     * @Groups({"quizzes_read_single", "answer_write_update", "answer_read_create", "answer_write_create"})
     */
    private $photo;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"quizzes_read_single", "answer_write_update", "answer_read_create", "answer_write_create"})
     */
    private $isAnswer = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getQuestion(): ?Question
    {
        return $this->question;
    }

    public function setQuestion(?Question $question): self
    {
        $this->question = $question;

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

    public function getIsAnswer(): ?bool
    {
        return $this->isAnswer;
    }

    public function setIsAnswer(bool $isAnswer): self
    {
        $this->isAnswer = $isAnswer;

        return $this;
    }
}
