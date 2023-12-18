<?php

namespace App\Entity;

use App\Repository\orm\PartyPlayerRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use GraphAware\Neo4j\OGM\Annotations as OGM;

#[ORM\Entity(repositoryClass: PartyPlayerRepository::class)]
#[ORM\Table(name: 'party_players')]
#[ODM\EmbeddedDocument]
/**
 * @OGM\Node(label="PartyPlayer")
 */
class PartyPlayer implements \JsonSerializable
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
    private string|null|int|UuidInterface $id = null;

    #[ORM\ManyToOne(inversedBy: 'partyPlayers', cascade: ["persist"])]
    /**
     * @OGM\Relationship(type="PARTICIPATE", direction="OUTGOING", targetEntity="Party", collection=false, mappedBy="partyPlayers")
     */
    private ?Party $party = null;

    #[ORM\ManyToOne(inversedBy: 'partyPlayers', cascade: ["persist"])]
    #[ORM\JoinColumn(nullable: false)]
    #[ODM\ReferenceOne(targetDocument: Player::class, storeAs: "id", strategy: "set")]
    /**
     * @OGM\Relationship(type="REFER_TO_PLAYER", direction="OUTGOING", targetEntity="Player", collection=false)
     */
    private ?Player $player = null;

    #[ORM\Column(nullable: true)]
    #[ODM\Field(type: "int", nullable: true)]
    /**
     * @OGM\Property(type="int")
     */
    private ?int $position = null;

    #[ORM\Column]
    #[ODM\Field(type: "bool", nullable: true)]
    /**
     * @OGM\Property(type="boolean")
     */
    private ?bool $connected = null;

    #[ORM\OneToMany(mappedBy: 'winner', targetEntity: Round::class, cascade: ["persist"])]
    #[ODM\ReferenceMany(targetDocument: Round::class, notSaved: true)]
    /**
     * @OGM\Relationship(type="WINNER", direction="INCOMING", targetEntity="Round", collection=true, mappedBy="winner")
     */
    private Collection $winnedRounds;

    public function __construct()
    {
        $a = OGM\Node::class;
        $this->winnedRounds = new ArrayCollection();
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

    public function setId(UuidInterface|int|string|null $id): void
    {
        if(is_string($id)) {
            $id = Uuid::fromString($id);
        }
        $this->id = $id;
    }

    public function getParty(): ?Party
    {
        return $this->party;
    }

    public function setParty(?Party $party): static
    {
        $this->party = $party;

        return $this;
    }

    public function getPlayer(): ?Player
    {
        return $this->player;
    }

    public function setPlayer(?Player $player): static
    {
        $this->player = $player;

        return $this;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(?int $position): static
    {
        $this->position = $position;

        return $this;
    }

    public function isConnected(): ?bool
    {
        return $this->connected;
    }

    public function setConnected(bool $connected): static
    {
        $this->connected = $connected;

        return $this;
    }

    public function getWinnedRounds(): Collection
    {
        return $this->winnedRounds;
    }

    public function addWinnedRound(Round $round): static
    {
        if (!$this->winnedRounds->contains($round)) {
            $this->winnedRounds->add($round);
            $round->setWinner($this);
            $round->setFinishedAt(new \DateTimeImmutable());
        }

        return $this;
    }

    public function removeWinnedRound(Round $round): static
    {
        if ($this->winnedRounds->removeElement($round)) {
            // set the owning side to null (unless already changed)
            if ($round->getWinner() === $this) {
                $round->setWinner(null);
                $round->setFinishedAt(null);
            }
        }

        return $this;
    }

    public function jsonSerialize(): mixed
    {
        return [
            "player" => $this->getPlayer(),
            "connected" => $this->isConnected(),
            "position" => $this->getPosition(),
            "id" => $this->getId()
        ];
    }

    public static function fromJson(array $json) : static {
        $partyPlayer = new PartyPlayer();
        $partyPlayer->setConnected($json["connected"]);
        $partyPlayer->setPosition($json["position"]);
        $partyPlayer->setPlayer(Player::fromJson($json["player"]));
        return $partyPlayer;
    }
}
