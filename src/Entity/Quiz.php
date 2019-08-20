<?php

namespace App\Entity;

use DateTime;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ApiResource()
 * @ORM\Entity(repositoryClass="App\Repository\QuizRepository")
 */
class Quiz
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="quizzes")
     * @ORM\JoinColumn(nullable=false)
     */
    private $User;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $Title;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $Tags;

    /**
     * @ORM\Column(type="text")
     */
    private $Description;

    /**
     * @ORM\Column(type="integer")
     */
    private $Type = 0;

    /**
     * @var MediaObject|null
     *
     * @ORM\ManyToOne(targetEntity=MediaObject::class)
     * @ORM\JoinColumn(nullable=true)
     * @ApiProperty(iri="http://schema.org/image")
     */
    private $Photo;

    /**
     * @ORM\Column(type="date")
     */
    private $Creation_date;

    /**
     * @ORM\Column(type="date")
     */
    private $Update_date;

    /**
     * @ORM\Column(type="boolean")
     */
    private $Is_public = true;

    /**
     * @ORM\Column(type="boolean")
     */
    private $Disabled = false;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $Disabling_reason;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Question", mappedBy="Quiz", orphanRemoval=true)
     */
    private $questions;

    public function __construct()
    {
        $this->questions = new ArrayCollection();
        $date = new DateTime();
        $this->Creation_date = $date;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->User;
    }

    public function setUser(?User $User): self
    {
        $this->User = $User;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->Title;
    }

    public function setTitle(string $Title): self
    {
        $this->Title = $Title;

        return $this;
    }

    public function getTags(): ?string
    {
        return $this->Tags;
    }

    public function setTags(?string $Tags): self
    {
        $this->Tags = $Tags;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->Description;
    }

    public function setDescription(string $Description): self
    {
        $this->Description = $Description;

        return $this;
    }

    public function getType(): ?int
    {
        return $this->Type;
    }

    public function setType(int $Type): self
    {
        $this->Type = $Type;

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

    public function getCreationDate(): ?\DateTimeInterface
    {
        return $this->Creation_date;
    }

    public function setCreationDate(\DateTimeInterface $Creation_date): self
    {
        $this->Creation_date = $Creation_date;

        return $this;
    }

    public function getUpdateDate(): ?\DateTimeInterface
    {
        return $this->Update_date;
    }

    public function setUpdateDate(\DateTimeInterface $Update_date): self
    {
        $this->Update_date = $Update_date;

        return $this;
    }

    public function getIsPublic(): ?bool
    {
        return $this->Is_public;
    }

    public function setIsPublic(bool $Is_public): self
    {
        $this->Is_public = $Is_public;

        return $this;
    }

    public function getDisabled(): ?bool
    {
        return $this->Disabled;
    }

    public function setDisabled(bool $Disabled): self
    {
        $this->Disabled = $Disabled;

        return $this;
    }

    public function getDisablingReason(): ?string
    {
        return $this->Disabling_reason;
    }

    public function setDisablingReason(?string $Disabling_reason): self
    {
        $this->Disabling_reason = $Disabling_reason;

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