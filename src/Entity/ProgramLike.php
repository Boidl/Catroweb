<?php

namespace App\Entity;

use App\Utils\TimeUtils;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Exception;

/**
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="program_like")
 * @ORM\Entity(repositoryClass="App\Repository\ProgramLikeRepository")
 */
class ProgramLike
{
  const TYPE_NONE = 0;
  const TYPE_THUMBS_UP = 1;
  const TYPE_SMILE = 2;
  const TYPE_LOVE = 3;
  const TYPE_WOW = 4;

  const ACTION_ADD = 'add';
  const ACTION_REMOVE = 'remove';
  // -> new types go here...

  public static array $VALID_TYPES = [
    self::TYPE_THUMBS_UP,
    self::TYPE_SMILE,
    self::TYPE_LOVE,
    self::TYPE_WOW,
    // -> ... and here ...
  ];

  public static array $TYPE_NAMES = [
    self::TYPE_THUMBS_UP => 'thumbs_up',
    self::TYPE_SMILE => 'smile',
    self::TYPE_LOVE => 'love',
    self::TYPE_WOW => 'wow',
    // -> ... and here
  ];

  /**
   * -----------------------------------------------------------------------------------------------------------------
   * NOTE: this entity uses a Doctrine workaround in order to allow using foreign keys as primary keys.
   *
   * @see{http://stackoverflow.com/questions/6383964/primary-key-and-foreign-key-with-doctrine-2-at-the-same-time}
   * -----------------------------------------------------------------------------------------------------------------
   */

  /**
   * @ORM\Id
   * @ORM\Column(type="guid", nullable=false)
   */
  protected string $program_id;

  /**
   * @ORM\ManyToOne(targetEntity="\App\Entity\Program", inversedBy="likes", fetch="LAZY")
   * @ORM\JoinColumn(name="program_id", referencedColumnName="id")
   */
  protected Program $program;

  /**
   * @ORM\Id
   * @ORM\Column(type="guid", nullable=false)
   */
  protected string $user_id;

  /**
   * @ORM\ManyToOne(targetEntity="\App\Entity\User", inversedBy="likes", fetch="LAZY")
   * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
   */
  protected User $user;

  /**
   * @ORM\Id
   * @ORM\Column(type="integer", nullable=false, options={"default": 0})
   */
  protected int $type = self::TYPE_THUMBS_UP;

  /**
   * @ORM\Column(type="datetime")
   */
  protected ?DateTime $created_at = null;

  public function __construct(Program $program, User $user, int $type)
  {
    $this->setProgram($program);
    $this->setUser($user);
    $this->setType($type);
    $this->created_at = null;
  }

  public function __toString(): string
  {
    return $this->program.'';
  }

  public static function isValidType(int $type): bool
  {
    return in_array($type, self::$VALID_TYPES, true);
  }

  /**
   * @ORM\PrePersist
   *
   * @throws Exception
   */
  public function updateTimestamps(): void
  {
    if (null === $this->getCreatedAt())
    {
      $this->setCreatedAt(TimeUtils::getDateTime());
    }
  }

  public function setProgram(Program $program): ProgramLike
  {
    $this->program = $program;
    $this->program_id = $program->getId();

    return $this;
  }

  public function getProgram(): Program
  {
    return $this->program;
  }

  public function getProgramId(): string
  {
    return $this->program_id;
  }

  public function setUser(User $user): ProgramLike
  {
    $this->user = $user;
    $this->user_id = $user->getId();

    return $this;
  }

  public function getUser(): User
  {
    return $this->user;
  }

  public function getUserId(): string
  {
    return $this->user_id;
  }

  public function setType(int $type): ProgramLike
  {
    $this->type = $type;

    return $this;
  }

  public function getType(): int
  {
    return $this->type;
  }

  public function getTypeAsString(): ?string
  {
    try
    {
      return self::$TYPE_NAMES[$this->type];
    }
    catch (Exception $exception)
    {
      return null;
    }
  }

  public function getCreatedAt(): ?DateTime
  {
    return $this->created_at;
  }

  public function setCreatedAt(DateTime $created_at): ProgramLike
  {
    $this->created_at = $created_at;

    return $this;
  }
}
