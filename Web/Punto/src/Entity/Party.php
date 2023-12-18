<?php

namespace App\Entity;

use App\Repository\odm\PartyRepository as ODMPartyRepository;
use App\Repository\orm\PartyRepository as ORMPartyRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use GraphAware\Neo4j\OGM\Annotations as OGM;

#[ORM\Entity(repositoryClass: ORMPartyRepository::class)]
#[ODM\Document(repositoryClass: ODMPartyRepository::class)]
/**
 * @OGM\Node(label="Party", repository="App\Repository\ogm\PartyRepository")
 */
class Party implements \JsonSerializable
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
    private null|int|string|UuidInterface $id = null;

    #[ORM\Column]
    #[ODM\Field(type: "date_immutable", nullable: true)]
    /**
     * @OGM\Property()
     * @OGM\Convert(type="datetime_immutable", options={"format":"long_timestamp"})
     */
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\OneToMany(mappedBy: 'party', targetEntity: PartyPlayer::class, cascade: ["persist", "remove"])]
    #[ODM\EmbedMany(targetDocument: PartyPlayer::class)]
    /**
     * @OGM\Relationship(targetEntity="PartyPlayer", type="PARTICIPATE", direction="INCOMING", collection=true, mappedBy="party")
     */
    private Collection $partyPlayers;

    #[ORM\Column(nullable: true)]
    #[ODM\Field(type: "date_immutable", nullable: true)]
    /**
     * @OGM\Property()
     * @OGM\Convert(type="datetime_immutable", options={"format":"long_timestamp"})
     */
    private ?\DateTimeImmutable $finished_at = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    #[ODM\EmbedOne(targetDocument: PartyPlayer::class)]
    /**
     * @OGM\Relationship(type="WINNER", direction="OUTGOING", targetEntity="PartyPlayer", collection=false, mappedBy="party")
     */
    private ?PartyPlayer $winner = null;

    #[ORM\OneToMany(mappedBy: 'party', targetEntity: Round::class)]
    #[ODM\ReferenceMany(targetDocument: Round::class, options: [
        "cascade" => ["persist"]
    ])]
    /**
     * @OGM\Relationship(type="ROUND", direction="OUTGOING", collection=true, targetEntity="Round", mappedBy="party")
     */
    private Collection $rounds;

    #[ORM\Column]
    #[ODM\Field(type: "integer")]
    /**
     * @OGM\Property(type="int")
     */
    private ?int $roundNumber = null;

    public function __construct()
    {
        $a = OGM\Node::class;
        $this->partyPlayers = new ArrayCollection();
        $this->rounds = new ArrayCollection();
    }

    public function getId(): ?UuidInterface
    {
        if (is_int($this->id)){
            return Uuid::fromInteger($this->id);
        }
        if(is_string($this->id)){
            return Uuid::fromString($this->id);
        }
        return $this->id;
    }

    public function setId(UuidInterface|string|int $id): static
    {
        $this->id = $id;

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

    public function addPlayer(Player $player) : ?PartyPlayer
    {
        if($this->isPlayerInParty($player)) {
            return null;
        }
        $partyPlayer = new PartyPlayer();
        $partyPlayer->setPlayer($player);
        $partyPlayer->setConnected(true);
        $partyPlayer->setParty($this);
        $this->addPartyPlayer($partyPlayer);

        return $partyPlayer;
    }

    public function removePlayer(Player $player) : static
    {
        $partyPlayer = $this->getPartyPlayers()->filter(fn(PartyPlayer $partyPlayer) => $partyPlayer->getPlayer()->getId()->toString() === $player->getId()->toString())->first();
        $this->removePartyPlayer($partyPlayer);

        return $this;
    }

    public function isPlayerInParty(Player $player) : bool
    {
        return $this->getPartyPlayers()->filter(fn(PartyPlayer $partyPlayer) => $partyPlayer->getPlayer()->getId()->toString() === $player->getId()->toString())->count() > 0;
    }

    /**
     * @return Collection<int, PartyPlayer>
     */
    public function getPartyPlayers(): Collection
    {
        return $this->partyPlayers;
    }

    /**
     * @return PartyPlayer[]
     */
    public function getOrderedPartyPlayers() : array
    {
        $ret = [];
        foreach ($this->getPartyPlayers() as $partyPlayer) {
            $ret[$partyPlayer->getPosition()] = $partyPlayer;
        }
        return $ret;
    }

    public function addPartyPlayer(PartyPlayer $partyPlayer): static
    {
        if (!$this->partyPlayers->contains($partyPlayer)) {
            $this->partyPlayers->add($partyPlayer);
            $partyPlayer->setParty($this);
        }

        return $this;
    }

    public function removePartyPlayer(PartyPlayer $partyPlayer): static
    {
        $this->partyPlayers->removeElement($partyPlayer);

        return $this;
    }

    public function getPartyPlayer(Player $player) : bool|PartyPlayer{
        return $this->getPartyPlayers()->filter(fn(PartyPlayer $partyPlayer) => $partyPlayer->getPlayer()->getId()->toString() === $player->getId()->toString())->first();
    }

    public function getFinishedAt(): ?\DateTimeImmutable
    {
        return $this->finished_at;
    }

    public function setFinishedAt(?\DateTimeImmutable $finished_at): static
    {
        $this->finished_at = $finished_at;

        return $this;
    }

    public function isFinished(): bool
    {
        return $this->finished_at !== null;
    }

    /**
     * @return Collection<int, Round>
     */
    public function getRounds(): Collection
    {
        return $this->rounds;
    }

    public function addRound(Round $round): static
    {
        if (!$this->rounds->contains($round)) {
            $this->rounds->add($round);
            $round->setParty($this);
        }

        return $this;
    }

    public function removeRound(Round $round): static
    {
        if ($this->rounds->removeElement($round)) {
            // set the owning side to null (unless already changed)
            if ($round->getParty()->getId()->toString() === $this->getId()->toString()) {
                $round->setParty(null);
            }
        }

        return $this;
    }

    public function getCurrentRound() : ?Round
    {
        $rounds = $this->getRounds()->filter(fn(Round $round) => $round->isStarted() && !$round->isFinished());
        if($rounds->count() === 0){
            return null;
        }
        return $rounds->first();
    }

    public function createRound() : Round
    {
        $round = new Round();
        $round->setParty($this);
        $round->setCreatedAt(new \DateTimeImmutable());
        $this->addRound($round);
        return $round;
    }

    public function getFinishedRounds() : Collection
    {
        return $this->getRounds()->filter(fn(Round $round) => $round->isFinished());
    }

    public function isStarted() : bool {
        $round = $this->getCurrentRound();
        return $round !== null && $round->isStarted();
    }

    public function getRoundNumber(): ?int
    {
        return $this->roundNumber;
    }

    public function setRoundNumber(int $roundNumber): static
    {
        $this->roundNumber = $roundNumber;

        return $this;
    }

    public function board() : ?Board{
        $round = $this->getCurrentRound();
        if($round === null){
            return null;
        }
        return new Board($round);
    }

    /**
     * @return PartyPlayer|null
     */
    public function getWinner(): ?PartyPlayer
    {
        return $this->winner;
    }

    /**
     * @param PartyPlayer|null $winner
     */
    public function setWinner(?PartyPlayer $winner): void
    {
        $this->winner = $winner;
    }

    public function jsonSerialize(): mixed
    {
        return [
            "id" => $this->getId()->toString(),
            "created_at" => $this->getCreatedAt()?->format("Y-m-d H:i:s"),
            "finished_at" => $this->getFinishedAt()?->format("Y-m-d H:i:s"),
            "partyPlayers" => $this->getPartyPlayers()->toArray(),
            "round_number" => $this->getRoundNumber(),
            "rounds" => $this->getRounds()->toArray(),
            "winner" => $this->getWinner()?->getPlayer()
        ];
    }

    public static function fromJson(array $json) : Party{
        $party = new Party();
        $party->setId(Uuid::fromString($json["id"]));
        $party->setCreatedAt(new \DateTimeImmutable($json["created_at"]));
        $party->setFinishedAt(new \DateTimeImmutable($json["finished_at"]));
        $party->setRoundNumber($json["round_number"]);
        $party->setWinner($json["winner"]);
        foreach ($json["partyPlayers"] as $partyPlayer) {
            $party->addPartyPlayer(PartyPlayer::fromJson($partyPlayer));
        }
        foreach ($json["rounds"] as $round) {
            $party->addRound(Round::fromJson($round));
        }
        return $party;
    }

    public function calculateWinner() : ?PartyPlayer {
        $winners = [];
        foreach ($this->getFinishedRounds() as $finishedRound){
            $winners[$finishedRound->getWinner()->getId()->toString()] = $finishedRound->getWinner();
        }
        $winnersByNumber = [];
        foreach ($winners as $winner){
            if(!isset($winnersByNumber[$winner->getId()->toString()])){
                $winnersByNumber[$winner->getId()->toString()] = 0;
            }
            $winnersByNumber[$winner->getId()->toString()]++;
        }

        $winnerOfTheParty = null;
        $winnerNumber = 0;
        foreach ($winnersByNumber as $key => $value){
            if($value > $winnerNumber){
                $winnerOfTheParty = $key;
                $winnerNumber = $value;
            }
        }

        if($winnerOfTheParty === null){
            return null;
        }

        return $winners[$winnerOfTheParty];
    }
}
