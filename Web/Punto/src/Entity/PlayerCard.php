<?php

namespace App\Entity;

use App\Repository\orm\PlayerCardRepository;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use GraphAware\Neo4j\OGM\Annotations as OGM;

#[ORM\Entity(repositoryClass: PlayerCardRepository::class)]
#[ORM\Table(uniqueConstraints: [
    new ORM\UniqueConstraint(name: "position_party", columns: ["position", "player_id", "party_id"]),
])]
#[ODM\EmbeddedDocument]
/**
 * @OGM\Node(label="PlayerCard")
 */
class PlayerCard implements \JsonSerializable
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

    #[ORM\Column]
    #[ODM\Field(type: "int")]
    /**
     * @OGM\Property(type="int")
     */
    private ?int $number = null;

    #[ORM\Column]
    #[ODM\Field(type: "int")]
    /**
     * @OGM\Property(type="int")
     */
    private ?int $color = null;

    #[ORM\ManyToOne(inversedBy: 'playerCards')]
    #[ORM\JoinColumn(nullable: false)]
    #[ODM\EmbedOne(targetDocument: Player::class)]
    /**
     * @OGM\Relationship(type="REFER_TO_PLAYER", direction="OUTGOING", targetEntity="Player", collection=false, mappedBy="playerCards")
     */
    private ?Player $player = null;

    #[ORM\ManyToOne(inversedBy: 'playerCards')]
    #[ORM\JoinColumn(nullable: false)]
    /**
     * @OGM\Relationship(type="WON_BY_ROUND", direction="INCOMING", targetEntity="Round", collection=false)
     */
    private ?Round $round = null;

    #[ORM\ManyToOne(inversedBy: 'playerCards')]
    #[ORM\JoinColumn(nullable: true)]
    /**
     * @OGM\Relationship(type="REFER_TO_CELL", direction="OUTGOING", targetEntity="Cell", collection=false)
     */
    private ?Cell $cell = null;

    #[ORM\Column]
    #[ODM\Field(type: "int")]
    /**
     * @OGM\Property(type="int")
     */
    private ?int $position = null;

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
    public function setId(UuidInterface|int|string|null $id): void
    {
        if(is_string($id)) {
            $id = Uuid::fromString($id);
        }
        $this->id = $id;
    }

    public function getNumber(): ?int
    {
        return $this->number;
    }

    public function setNumber(int $number): static
    {
        $this->number = $number;

        return $this;
    }

    public function getColor(): ?int
    {
        return $this->color;
    }

    public function getColorAsHtml() : string{
        # color is the color representation in int
        # let colorHexa = card.color.toString(16).padStart(6, '0')
        $colorHexa = dechex($this->getColor());
        $colorHexa = str_pad($colorHexa, 6, '0', STR_PAD_LEFT);
        return "#$colorHexa";
    }

    public function setColor(int $color): static
    {
        $this->color = $color;

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

    public function getRound(): ?Round
    {
        return $this->round;
    }

    public function setRound(?Round $round): static
    {
        $this->round = $round;

        return $this;
    }

    public function getCell(): ?Cell
    {
        return $this->cell;
    }

    public function setCell(?Cell $cell): static
    {
        $this->cell = $cell;

        return $this;
    }

    public function jsonSerialize(): mixed
    {
        return [
            "id" => $this->getId()->toString(),
            "number" => $this->getNumber(),
            "color" => $this->getColor(),
            "player" => $this->getPlayer(),
            "position" => $this->getPosition(),
            "cell" => $this->getCell()?->toArray()
        ];
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(int $position): static
    {
        $this->position = $position;

        return $this;
    }
}
