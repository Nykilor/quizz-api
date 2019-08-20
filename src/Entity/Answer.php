<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ApiResource()
 * @ORM\Entity(repositoryClass="App\Repository\AnswerRepository")
 */
class Answer
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Question", inversedBy="answers")
     * @ORM\JoinColumn(nullable=false)
     */
    private $Question;

    /**
     * @ORM\Column(type="text")
     */
    private $Text;

    /**
     * @var MediaObject|null
     *
     * @ORM\ManyToOne(targetEntity=MediaObject::class)
     * @ORM\JoinColumn(nullable=true)
     * @ApiProperty(iri="http://schema.org/image")
     */
    private $Photo;

    /**
     * @ORM\Column(type="boolean")
     */
    private $Is_answer = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getQuestion(): ?Question
    {
        return $this->Question;
    }

    public function setQuestion(?Question $Question): self
    {
        $this->Question = $Question;

        return $this;
    }

    public function getText(): ?string
    {
        return $this->Text;
    }

    public function setText(string $Text): self
    {
        $this->Text = $Text;

        return $this;
    }

    public function getPhoto(): ?MediaObject
    {
        return $this->Photo;
    }

    public function setPhoto(?MediaObject $Photo): self
    {
        $this->Photo = $Photo;

        return $this;
    }

    public function getIsAnswer(): ?bool
    {
        return $this->Is_answer;
    }

    public function setIsAnswer(bool $Is_answer): self
    {
        $this->Is_answer = $Is_answer;

        return $this;
    }
}
