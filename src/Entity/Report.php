<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ApiResource()
 * @ORM\Entity(repositoryClass="App\Repository\ReportRepository")
 */
class Report
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Quiz")
     * @ORM\JoinColumn(nullable=false)
     */
    private $Quiz_ID;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="reports")
     * @ORM\JoinColumn(nullable=false)
     */
    private $User_ID;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $Reason;

    /**
     * @ORM\Column(type="text")
     */
    private $Description;

    /**
     * @ORM\Column(type="boolean")
     */
    private $Resolved;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     */
    private $Resolved_by;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getQuizID(): ?Quiz
    {
        return $this->Quiz_ID;
    }

    public function setQuizID(?Quiz $Quiz_ID): self
    {
        $this->Quiz_ID = $Quiz_ID;

        return $this;
    }

    public function getUserID(): ?User
    {
        return $this->User_ID;
    }

    public function setUserID(?User $User_ID): self
    {
        $this->User_ID = $User_ID;

        return $this;
    }

    public function getReason(): ?string
    {
        return $this->Reason;
    }

    public function setReason(string $Reason): self
    {
        $this->Reason = $Reason;

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

    public function getResolved(): ?bool
    {
        return $this->Resolved;
    }

    public function setResolved(bool $Resolved): self
    {
        $this->Resolved = $Resolved;

        return $this;
    }

    public function getResolvedBy(): ?User
    {
        return $this->Resolved_by;
    }

    public function setResolvedBy(?User $Resolved_by): self
    {
        $this->Resolved_by = $Resolved_by;

        return $this;
    }
}
