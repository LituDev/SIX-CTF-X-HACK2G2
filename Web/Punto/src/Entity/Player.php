<?php

namespace App\Entity;

use App\Repository\odm\PlayerRepository as ODMPlayerRepository;
use App\Repository\orm\PlayerRepository as ORMPlayerRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use GraphAware\Neo4j\OGM\Annotations as OGM;

#[ORM\Entity(repositoryClass: ORMPlayerRepository::class)]
#[ODM\Document(repositoryClass: ODMPlayerRepository::class)]
/**
 * @OGM\Node(label="Player", repository="App\Repository\ogm\PlayerRepository")
 */
class Player implements UserInterface, \JsonSerializable
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "CUSTOM")]
    #[ORM\Column(type: "uuid", unique: true)]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    #[ODM\Id(strategy: "CUSTOM", options: [
        "class" => Type\UuidGenerator::class
    ], type: "uuid")]
    /**
     * @OGM\GraphId()
     */
    private string|int|null|UuidInterface $id = null;

    #[ORM\Column(length: 255)]
    #[ODM\Field(type: "string")]
    /**
     * @OGM\Property(type="string")
     */
    private ?string $name = null;

    #[ORM\Column]
    #[ODM\Field(type: "date_immutable")]
    /**
     * @OGM\Property()
     * @OGM\Convert(type="datetime_immutable", options={"format":"long_timestamp"})
     */
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\OneToMany(mappedBy: 'player', targetEntity: PlayerCard::class)]
    /**
     * @OGM\Relationship(type="REFER_TO_PLAYER", direction="INCOMING", targetEntity="PlayerCard", collection=true, mappedBy="player")
     */
    private Collection $playerCards;

    public function __construct()
    {
        $a = OGM\Node::class;
        $this->playerCards = new ArrayCollection();
    }

    public function getId(): ?UuidInterface
    {
        if(is_int($this->id)) {
            $this->id = Uuid::fromInteger($this->id);
        }
        if(is_string($this->id)) {
            $this->id = Uuid::fromString($this->id);
        }
        return $this->id;
    }

    /**
     * @param UuidInterface|string|null $id
     */
    public function setId(UuidInterface|string|null|int $id): void
    {
        if(is_string($id)) {
            $id = Uuid::fromString($id);
        }
        $this->id = $id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeImmutable $created_at): static
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getRoles(): array
    {
        return [];
    }

    public function eraseCredentials()
    {
    }

    public function getUserIdentifier(): string
    {
        return $this->getId()->toString();
    }

    public function getPassword()
    {
        return "";
    }

    public function getSalt()
    {
        return "";
    }

    public function getUsername()
    {
        return $this->getName();
    }

    public function jsonSerialize(): mixed
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'createdAt' => $this->getCreatedAt(),
        ];
    }

    /**
     * @return Collection<int, PlayerCard>
     */
    public function getPlayerCards(): Collection
    {
        return $this->playerCards;
    }

    public function addPlayerCard(PlayerCard $playerCard): static
    {
        if(!isset($this->playerCards)){
            $this->playerCards = new ArrayCollection();
        }
        if (!$this->playerCards->contains($playerCard)) {
            $this->playerCards->add($playerCard);
            $playerCard->setPlayer($this);
        }

        return $this;
    }

    public function removePlayerCard(PlayerCard $playerCard): static
    {
        if ($this->playerCards->removeElement($playerCard)) {
            // set the owning side to null (unless already changed)
            if ($playerCard->getPlayer() === $this) {
                $playerCard->setPlayer(null);
            }
        }

        return $this;
    }
}
