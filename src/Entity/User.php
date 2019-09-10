<?php
namespace App\Entity;

use App\Controller\RegisterUserController;
use App\Controller\DeleteUserController;

use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiSubresource;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

use Symfony\Component\Security\Core\User\UserInterface;

use Symfony\Component\Serializer\Annotation\Groups;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @UniqueEntity(
 *  fields={"email"},
 *  message="This e-mail address is already registered."
 * )
 * @ApiResource(
 *  collectionOperations={
 *    "get"={
 *      "normalization_context"={
 *        "groups"={"user_read_collection"}
 *      }
 *    },
 *    "register"={
 *      "method"="POST",
 *      "denormalization_context"={
 *        "groups"={"user_post_save"}
 *      },
 *      "normalization_context"={
 *        "groups"={"user_post_read"}
 *      },
 *      "controller"=RegisterUserController::class,
 *      "path"="/register"
 *    },
 *    "login"={
 *      "path"="/login",
 *      "method"="POST",
 *      "swagger_context"={
 *        "summary"="Performs a login attempt, returning a valid token on success",
 *        "parameters"={
 *          {
 *            "in": "body",
 *            "schema": {
 *              "type": "object",
 *              "description": "",
 *              "properties": {
 *                "username": {
 *                    "type": "string"
 *                },
 *                "password": {
 *                    "type": "string"
 *                },
 *                "email": {
 *                    "type": "string"
 *                }
 *              }
 *            }
 *          }
 *        },
 *        "responses"={
 *          200: {
 *            "description": "Successful login attempt, returning a new token",
 *            "schema": {
 *              "type": "object",
 *              "properties": {
 *                "token": {
 *                  "type": "string"
 *                }
 *              }
 *            }
 *          },
 *          401: {
 *            "description": "Bad credentials",
 *            "schema": {
 *              "type": "object",
 *              "properties": {
 *                "code": {
 *                  "type": "integer",
 *                  "example": 401
 *                },
 *                "message": {
 *                  "type": "string",
 *                  "example": "Bad credentials"
 *                }
 *              }
 *            }
 *          }
 *        },
 *        "consumes"={
 *          "application/json"
 *        },
 *        "produces"={
 *          "application/json"
 *        }
 *      }
 *    }
 *  },
 *  itemOperations={
 *    "get"={
 *      "normalization_context"={
 *        "groups"={"user_read_single"}
 *      }
 *    },
 *    "delete"={
 *      "access_control"="is_granted('ROLE_ADMIN') or object == user",
 *      "controller"=DeleteUserController::class
 *    },
 *   }
 * )
 */
class User implements UserInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"quizzes_read_all", "quizzes_read_single", "post_quizz", "report_read", "user_read_collection", "user_read_single"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     * @Groups({"quizzes_read_all", "quizzes_read_single", "post_quizz", "report_read", "user_post_save", "user_post_read", "user_read_collection", "user_read_single"})
     */
    private $username;

    /**
     * @ORM\Column(type="json")
     * @Groups({"admin_write"})
     */
    private $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     * @Groups({"user_post_save"})
     */
    private $password;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Quiz", mappedBy="user", orphanRemoval=true)
     * @ApiSubresource(maxDepth=1)
     * @Groups({"user_read_collection", "user_read_single"})
     */
    private $quizzes;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Report", mappedBy="user")
     * @ApiSubresource(maxDepth=1)
     */
    private $reports;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\MediaObject", mappedBy="user", orphanRemoval=true)
     * @ApiSubresource(maxDepth=1)
     */
    private $mediaObjects;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Email(
     *     message = "The email '{{ value }}' is not a valid email."
     * )
     * @Groups({"user_post_save", "user_post_read", "admin_read"})
     */
    private $email;

    public function __construct()
    {
        $this->quizzes = new ArrayCollection();
        $this->reports = new ArrayCollection();
        $this->mediaObjects = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string) $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string) $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    /**
     * @return Collection|Quiz[]
     */
    public function getQuizzes(): Collection
    {
        return $this->quizzes;
    }

    public function addQuiz(Quiz $quiz): self
    {
        if (!$this->quizzes->contains($quiz)) {
            $this->quizzes[] = $quiz;
            $quiz->setUserID($this);
        }

        return $this;
    }

    public function removeQuiz(Quiz $quiz): self
    {
        if ($this->quizzes->contains($quiz)) {
            $this->quizzes->removeElement($quiz);
            // set the owning side to null (unless already changed)
            if ($quiz->getUserID() === $this) {
                $quiz->setUserID(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Report[]
     */
    public function getReports(): Collection
    {
        return $this->reports;
    }

    public function addReport(Report $report): self
    {
        if (!$this->reports->contains($report)) {
            $this->reports[] = $report;
            $report->setUserID($this);
        }

        return $this;
    }

    public function removeReport(Report $report): self
    {
        if ($this->reports->contains($report)) {
            $this->reports->removeElement($report);
            // set the owning side to null (unless already changed)
            if ($report->getUserID() === $this) {
                $report->setUserID(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|MediaObject[]
     */
    public function getMediaObjects(): Collection
    {
        return $this->mediaObjects;
    }

    public function addMediaObject(MediaObject $mediaObject): self
    {
        if (!$this->mediaObjects->contains($mediaObject)) {
            $this->mediaObjects[] = $mediaObject;
            $mediaObject->setUser($this);
        }

        return $this;
    }

    public function removeMediaObject(MediaObject $mediaObject): self
    {
        if ($this->mediaObjects->contains($mediaObject)) {
            $this->mediaObjects->removeElement($mediaObject);
            // set the owning side to null (unless already changed)
            if ($mediaObject->getUser() === $this) {
                $mediaObject->setUser(null);
            }
        }

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }
}
