<?php

namespace Bistumsliga\Bistumsliga\Domain\Model;

#[\AllowDynamicProperties]
class CompetitionPenalty extends \TYPO3\CMS\Extbase\DomainObject\AbstractDomainObject
{
    /**
     * @var int
     */
    protected $pointsPos;

    /**
     * @return int|null
     */
    public function getPointsPos(): ?int
    {
        return $this->pointsPos ?? null;
    }

    /**
     * @param int $pointsPos
     */
    public function setPointsPos(int $pointsPos): void
    {
        $this->pointsPos = $pointsPos;
    }

    /**
     * @return string|null
     */
    public function getComment(): ?string
    {
        return $this->comment ?? '';
    }

    /**
     * @param string $comment
     */
    public function setComment(string $comment): void
    {
        $this->comment = $comment;
    }

    /**
     * @var string
     */
    protected $comment;

    /**
     * @var Team
     */
    protected $team;

    /**
     * @var Competition
     */
    protected $competition;

    /**
     * @return Team|null
     */
    public function getTeam(): ?Team
    {
        return $this->team ?? null;
    }

    /**
     * @param Team $team
     */
    public function setTeam(Team $team): void
    {
        $this->team = $team;
    }

    /**
     * @return Competition|null
     */
    public function getCompetition(): ?Competition
    {
        return $this->competition ?? null;
    }

    /**
     * @param Competition $competition
     */
    public function setCompetition(Competition $competition): void
    {
        $this->competition = $competition;
    }

    /**
     * @return Game|null
     */
    public function getGame(): ?Game
    {
        return $this->game ?? null;
    }

    /**
     * @param Game $game
     */
    public function setGame(Game $game): void
    {
        $this->game = $game;
    }

    /**
     * @var Game
     */
    protected $game;
}
