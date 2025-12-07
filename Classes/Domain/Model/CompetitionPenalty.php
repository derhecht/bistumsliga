<?php

namespace Bistumsliga\Bistumsliga\Domain\Model;

class CompetitionPenalty extends \TYPO3\CMS\Extbase\DomainObject\AbstractDomainObject
{
    /**
     * @var int
     */
    protected $pointsPos;

    /**
     * @return int
     */
    public function getPointsPos(): int
    {
        return $this->pointsPos;
    }

    /**
     * @param int $pointsPos
     */
    public function setPointsPos(int $pointsPos): void
    {
        $this->pointsPos = $pointsPos;
    }

    /**
     * @return string
     */
    public function getComment(): string
    {
        return $this->comment;
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
     * @return Team
     */
    public function getTeam(): Team
    {
        return $this->team;
    }

    /**
     * @param Team $team
     */
    public function setTeam(Team $team): void
    {
        $this->team = $team;
    }

    /**
     * @return Competition
     */
    public function getCompetition(): Competition
    {
        return $this->competition;
    }

    /**
     * @param Competition $competition
     */
    public function setCompetition(Competition $competition): void
    {
        $this->competition = $competition;
    }

    /**
     * @return Match
     */
    public function getGame(): Match
    {
        return $this->game;
    }

    /**
     * @param Match $game
     */
    public function setGame(Match $game): void
    {
        $this->game = $game;
    }

    /**
     * @var Match
     */
    protected $game;
}
