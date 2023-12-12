<?php

namespace App\Entity;

use App\Repository\orm\RoundRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use GraphAware\Neo4j\OGM\Annotations as OGM;

#[ORM\Entity(repositoryClass: RoundRepository::class)]
#[ODM\Document]
/**
 * @OGM\Node(label="Round")
 */
class Round implements \JsonSerializable
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
    #[ODM\Field(type: "date_immutable")]
    /**
     * @OGM\Property()
     * @OGM\Convert(type="datetime_immutable", options={"format":"long_timestamp"})
     */
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\ManyToOne(inversedBy: 'rounds')]
    #[ORM\JoinColumn(nullable: false)]
    #[ODM\ReferenceOne(targetDocument: Party::class, inversedBy: 'rounds', storeAs: "id", cascade: ["persist"], strategy: "set")]
    /**
     * @OGM\Relationship(type="ROUND", direction="INCOMING", collection=false, mappedBy="rounds", targetEntity="Party")
     */
    private ?Party $party = null;

    #[ORM\Column(nullable: true)]
    #[ODM\Field(type: "date_immutable")]
    /**
     * @OGM\Property()
     * @OGM\Convert(type="datetime_immutable", options={"format":"long_timestamp"})
     */
    private ?\DateTimeImmutable $startedAt = null;

    #[ORM\Column(nullable: true)]
    #[ODM\Field(type: "date_immutable")]
    /**
     * @OGM\Property()
     * @OGM\Convert(type="datetime_immutable", options={"format":"long_timestamp"})
     */
    private ?\DateTimeImmutable $finishedAt = null;

    #[ORM\ManyToOne(inversedBy: 'rounds')]
    #[ODM\ReferenceOne(targetDocument: Player::class, storeAs: "id", cascade: ["persist"], strategy: "set", nullable: true)]
    /**
     * @OGM\Relationship(type="LAST_PLAYED", direction="OUTGOING", collection=false, targetEntity="Player")
     */
    private ?Player $lastPlayedPlayer = null;

    #[ORM\ManyToOne(inversedBy: 'winnedRounds', cascade: ["persist"])]
    #[ODM\ReferenceOne(targetDocument: PartyPlayer::class, storeAs: "id", strategy: "set", cascade: ["persist"])]
    /**
     * @OGM\Relationship(type="WINNER", direction="OUTGOING", collection=false, targetEntity="PartyPlayer", mappedBy="winnedRounds")
     */
    private ?PartyPlayer $winner = null;

    #[ORM\OneToMany(mappedBy: 'round', targetEntity: PlayerCard::class, cascade: ["persist"])]
    #[ODM\EmbedMany(targetDocument: PlayerCard::class)]
    /**
     * @OGM\Relationship(type="CARDS_OF_ROUND", direction="INCOMING", collection=true, mappedBy="round", targetEntity="PlayerCard")
     */
    private Collection $playerCards;

    #[ORM\OneToMany(mappedBy: 'round', targetEntity: Cell::class)]
    #[ODM\EmbedMany(targetDocument: Cell::class)]
    /**
     * @OGM\Relationship(type="CELL", direction="OUTGOING", collection=true, mappedBy="round", targetEntity="Cell")
     */
    private Collection $cells;

    #[ORM\Column(nullable: true)]
    #[ODM\Field(type: "integer")]
    /**
     * @OGM\Property(type="int")
     */
    private ?int $commonColor = null;

    public function __construct()
    {
        $a = OGM\Node::class;
        $this->playerCards = new ArrayCollection();
        $this->cells = new ArrayCollection();
    }

    public function getId(): ?UuidInterface
    {
        if(is_int($this->id))
            return Uuid::fromInteger($this->id);
        if(is_string($this->id)){
            return Uuid::fromString($this->id);
        }
        return $this->id;
    }

    public function setId(UuidInterface|int|string|null $id): void
    {
        $this->id = $id;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
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

    public function getStartedAt(): ?\DateTimeImmutable
    {
        return $this->startedAt;
    }

    public function setStartedAt(?\DateTimeImmutable $startedAt): static
    {
        $this->startedAt = $startedAt;

        return $this;
    }

    public function isStarted(): bool
    {
        return $this->startedAt !== null;
    }

    public function getFinishedAt(): ?\DateTimeImmutable
    {
        return $this->finishedAt;
    }

    public function setFinishedAt(?\DateTimeImmutable $finishedAt): static
    {
        $this->finishedAt = $finishedAt;

        return $this;
    }

    public function isFinished(): bool
    {
        return $this->finishedAt !== null;
    }

    public function getLastPlayedPlayer(): ?Player
    {
        return $this->lastPlayedPlayer;
    }

    public function setLastPlayedPlayer(?Player $lastPlayedPlayer): static
    {
        $this->lastPlayedPlayer = $lastPlayedPlayer;

        return $this;
    }

    public function getWinner(): ?PartyPlayer
    {
        return $this->winner;
    }

    public function setWinner(?PartyPlayer $winner): static
    {
        $this->winner = $winner;

        return $this;
    }

    public function getCommonColor(): ?int
    {
        return $this->commonColor;
    }

    public function setCommonColor(?int $commonColor): static
    {
        $this->commonColor = $commonColor;

        return $this;
    }

    /**
     * @return Collection<int, PlayerCard>
     */
    public function getPlayerCards(): Collection
    {
        return $this->playerCards;
    }

    /**
     * @return Collection<int, PlayerCard>
     */
    public function getCardsForPlayer(Player $player) : Collection
    {
        return $this->getPlayerCards()->filter(fn(PlayerCard $playerCard) => $playerCard->getPlayer()->getId()->toString() === $player->getId()->toString());
    }

    public function addPlayerCard(PlayerCard $playerCard): static
    {
        if (!$this->playerCards->contains($playerCard)) {
            $this->playerCards->add($playerCard);
            $playerCard->setRound($this);
        }

        return $this;
    }

    public function removePlayerCard(PlayerCard $playerCard): static
    {
        if ($this->playerCards->removeElement($playerCard)) {
            // set the owning side to null (unless already changed)
            if ($playerCard->getRound() === $this) {
                $playerCard->setRound(null);
            }
        }

        return $this;
    }

    public function getNextPlayer() : ?PartyPlayer {
        $found = false;
        $players = array_reverse($this->getParty()->getOrderedPartyPlayers());
        if($this->lastPlayedPlayer !== null){
            foreach ($players as $partyPlayer) {
                if($found){
                    return $partyPlayer;
                }
                if($partyPlayer->getPlayer()->getId()->toString() === $this->getLastPlayedPlayer()?->getId()->toString()) {
                    $found = true;
                }
            }
        }

        return $players[0] ?? null;
    }

    /**
     * @return Collection<int, Cell>
     */
    public function getCells(): Collection
    {
        return $this->cells;
    }


    public function addCell(Cell $cell): static
    {
        if (!$this->cells->contains($cell)) {
            $this->cells->add($cell);
            $cell->setRound($this);
        }

        return $this;
    }

    public function removeCell(Cell $cell): static
    {
        if ($this->cells->removeElement($cell)) {
            // set the owning side to null (unless already changed)
            if ($cell->getRound() === $this) {
                $cell->setRound(null);
            }
        }

        return $this;
    }

    public function getNextCardFor(Player $player) : ?PlayerCard {
        $cards = $this->getCardsForPlayer($player)->toArray();
        usort($cards, fn(PlayerCard $a, PlayerCard $b) => $a->getPosition() <=> $b->getPosition());
        $card = $cards[0];
        while($this->isCardUsed($card)){
            array_shift($cards);
            if(count($cards) === 0){
                return null;
            }
            $card = $cards[0];
        }

        return $card;
    }

    public function isCardUsed(PlayerCard $card) : bool{
        foreach ($this->cells as $cell){
            /** @var Cell $cell */
            foreach ($cell->getPlayerCards() as $c){
                if($c->getId()->toString() === $card->getId()->toString()){
                    return true;
                }
            }
        }

        return false;
    }

    public function jsonSerialize(): mixed
    {
        return [
            'id' => $this->getId(),
            'createdAt' => $this->getCreatedAt(),
            'party' => $this->getParty()->getId(),
            'startedAt' => $this->getStartedAt(),
            'finishedAt' => $this->getFinishedAt(),
            'lastPlayedPlayer' => $this->getLastPlayedPlayer(),
            'winner' => $this->getWinner(),
            'commonColor' => $this->getCommonColor(),
            'playerCards' => $this->getPlayerCards()->toArray(),
            'cells' => $this->getCells()->toArray(),
        ];
    }
}
