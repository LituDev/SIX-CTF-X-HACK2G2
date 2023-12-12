<?php

namespace App\Entity;

use App\Repository\orm\CellRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use GraphAware\Neo4j\OGM\Annotations as OGM;

#[ORM\Entity(repositoryClass: CellRepository::class)]
#[ORM\Table(uniqueConstraints: [
    new ORM\UniqueConstraint(name: 'cell_party', columns: ['x', 'z', 'party_id'])
])]
#[ODM\EmbeddedDocument]
/**
 * @OGM\Node(label="Cell", repository="App\Repository\ogm\CellRepository")
 */
class Cell implements \JsonSerializable
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
    private ?int $z = null;

    #[ORM\Column]
    #[ODM\Field(type: "int")]
    /**
     * @OGM\Property(type="int")
     */
    private ?int $x = null;

    #[ORM\ManyToOne(inversedBy: 'cells', cascade: ['persist'])]
    /**
     * @OGM\Relationship(type="REFER_TO_ROUND", direction="OUTGOING", collection=false, mappedBy="cells", targetEntity="Round")
     */
    private ?Round $round = null;

    #[ORM\OneToMany(mappedBy: 'cell', targetEntity: PlayerCard::class)]
    #[ODM\EmbedMany(targetDocument: PlayerCard::class, strategy: 'setArray')]
    /**
     * @OGM\Relationship(type="REFER_TO_CELL", direction="INCOMING", collection=true, mappedBy="cell", targetEntity="PlayerCard")
     */
    private Collection $playerCards;

    public function __construct()
    {
        $a = OGM\Node::class;
        $this->playerCards = new ArrayCollection();
    }

    /**
     * @return UuidInterface|string|null
     */
    public function getId(): UuidInterface|string|null
    {
        if(is_int($this->id))
            return Uuid::fromInteger($this->id);
        if(is_string($this->id))
            return Uuid::fromString($this->id);
        return $this->id;
    }

    /**
     * @param UuidInterface|string|null $id
     */
    public function setId(UuidInterface|string|int|null $id): void
    {
        $this->id = $id;
    }

    public function getZ(): ?int
    {
        return $this->z;
    }

    public function setZ(int $z): static
    {
        $this->z = $z;

        return $this;
    }

    public function getX(): ?int
    {
        return $this->x;
    }

    public function setX(int $x): static
    {
        $this->x = $x;

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

    public function jsonSerialize(): mixed
    {
        return [
            'x' => $this->getX(),
            'z' => $this->getZ(),
            "id" => $this->id,
            'cards' => $this->playerCards->toArray()
        ];
    }

    public function toArray() : array {
        return [
            'x' => $this->getX(),
            'z' => $this->getZ(),
            "id" => $this->id
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
        if (!$this->playerCards->contains($playerCard)) {
            $this->playerCards->add($playerCard);
            $playerCard->setCell($this);
        }

        return $this;
    }

    public function removePlayerCard(PlayerCard $playerCard): static
    {
        if ($this->playerCards->removeElement($playerCard)) {
            // set the owning side to null (unless already changed)
            if ($playerCard->getCell() === $this) {
                $playerCard->setCell(null);
            }
        }

        return $this;
    }

    public function cardsSortedByNumber() : array {
        $cards = $this->playerCards->toArray();
        usort($cards, function (PlayerCard $a, PlayerCard $b){
            return $a->getNumber() <=> $b->getNumber();
        });
        $cards = array_reverse($cards);
        return $cards;
    }

    public function getFrontCard() : ?PlayerCard{
        $cards = $this->cardsSortedByNumber();
        $card = array_shift($cards);
        if($card === false){
            return null;
        }
        return $card;
    }
}
