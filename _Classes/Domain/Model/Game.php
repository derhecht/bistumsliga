<?php

namespace Bistumsliga\Bistumsliga\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
final class Game extends AbstractEntity
{
    protected int $goalsHome1;

    protected int $goalsHome2;

    public function getGoalsHome2(): int
    {
        return $this->goalsHome2;
    }

    public function setGoalsHome2(int $goalsHome2): void
    {
        $this->goalsHome2 = $goalsHome2;
    }

    public function getGoalsGuest1(): int
    {
        return $this->goalsGuest1;
    }

    public function setGoalsGuest1(int $goalsGuest1): void
    {
        $this->goalsGuest1 = $goalsGuest1;
    }

    public function getGoalsGuest2(): int
    {
        return $this->goalsGuest2;
    }

    /**
     * @param int $goalsGuest2
     */
    public function setGoalsGuest2(int $goalsGuest2): void
    {
        $this->goalsGuest2 = $goalsGuest2;
    }

    /**
     * @return string|null
     */
    public function getGameReport(): ?string
    {
        return $this->gameReport;
    }

    /**
     * @param string $gameReport
     */
    public function setGameReport(string $gameReport): void
    {
        $this->gameReport = $gameReport;
    }

    /**
     * @return string|null
     */
    public function getGameReportAuthor(): ?string
    {
        return $this->gameReportAuthor;
    }

    /**
     * @var string
     */
    protected $addinfo;

    /**
     * @return string|null
     */
    public function getAddinfo(): ?string
    {
        return $this->addinfo;
    }

    /**
     * @param string $addinfo
     */
    public function setAddinfo(string $addinfo): void
    {
        $this->addinfo = $addinfo;
    }

    /**
     * @var int
     */
    protected $status;

    /**
     * @return int|null
     */
    public function getStatus(): ?int
    {
        return $this->status;
    }

    /**
     * @param int $status
     */
    public function setStatus(int $status): void
    {
        $this->status = $status;
    }

    /**
     * @param string $gameReportAuthor
     */
    public function setGameReportAuthor(string $gameReportAuthor): void
    {
        $this->gameReportAuthor = $gameReportAuthor;
    }

    /**
     * @var int
     */
    protected $goalsGuest1;

    /**
     * @var int
     */
    protected $goalsGuest2;

    /**
     * @var string
     */
    protected $gameReport;

    /**
     * @var string
     */
    protected $gameReportAuthor;

    /**
     * @return int|null
     */
    public function getGoalsHome1(): ?int
    {
        return $this->goalsHome1;
    }

    /**
     * @param int $goalsHome1
     */
    public function setGoalsHome1(int $goalsHome1): void
    {
        $this->goalsHome1 = $goalsHome1;
    }

    /**
     * @var Profile
     */
    protected $referee;

    /**
     * @return Profile|null
     */
    public function getReferee(): ?Profile
    {
        return $this->referee;
    }

    /**
     * @param Profile $referee
     */
    public function setReferee(Profile $referee): void
    {
        $this->referee = $referee;
    }
}
