<?php

namespace Derhecht\Bistumsliga\DataProcessor;

use Derhecht\Bistumsliga\Domain\Model\Competition;
use Derhecht\Bistumsliga\Domain\Model\CompetitionPenalty;
use Derhecht\Bistumsliga\Domain\Model\Game;
use Derhecht\Bistumsliga\Domain\Model\Team;
use Derhecht\Bistumsliga\Domain\Repository\CompetitionPenaltyRepository;
use Derhecht\Bistumsliga\Domain\Repository\CompetitionRepository;
use Derhecht\Bistumsliga\Domain\Repository\GameRepository;
use Derhecht\Bistumsliga\Domain\Repository\ProfileRepository;
use Derhecht\Bistumsliga\Domain\Repository\TeamRepository;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;

class GameReportDataProcessor extends \In2code\Powermail\DataProcessor\AbstractDataProcessor
{
    /**
     * @var PersistenceManager
     */
    protected PersistenceManager $persistenceManager;

    /**
     * @var CompetitionPenaltyRepository
     */
    protected CompetitionPenaltyRepository $competitionPenaltyRepository;

    /**
     * @var CompetitionRepository
     */
    protected CompetitionRepository $competitionRepository;

    /**
     * @var TeamRepository
     */
    protected TeamRepository $teamRepository;

    /**
     * @var GameRepository
     */
    protected GameRepository $gameRepository;

    /**
     * @var ProfileRepository
     */
    protected ProfileRepository $profileRepository;

    public function __construct(CompetitionRepository $competitionRepository, TeamRepository $teamRepository, CompetitionPenaltyRepository $competitionPenaltyRepository, GameRepository $gameRepository, ProfileRepository $profileRepository, PersistenceManager $persistenceManager)
    {
        $this->competitionRepository = $competitionRepository;
        $this->teamRepository = $teamRepository;
        $this->competitionPenaltyRepository = $competitionPenaltyRepository;
        $this->gameRepository = $gameRepository;
        $this->profileRepository = $profileRepository;
        $this->persistenceManager = $persistenceManager;
    }

    public function process(): void
    {
        $this->gameReportDataProcessor();
    }

    public function gameReportDataProcessor(): void
    {
        $answers = $this->getMail()->getAnswersByFieldMarker();
        if (!isset($answers['liga'])) {
            return;
        }

        $get = function (string $key) use ($answers) {
            $answer = $answers[$key] ?? null;
            return is_object($answer) && method_exists($answer, 'getValue') ? $answer->getValue() : null;
        };

        $competition = $get('liga') ?? '';
        $round = $get('spieltag') ?? '';
        $home = $get('heimmannschaft') ?? '';
        $guest = $get('gastmannschaft') ?? '';
        $finalHome = $get('endstand') ?? '';
        $finalGuest = $get('endstand_gast') ?? '';
        $halfHome = $get('halbzeitstand') ?? '';
        $halfGuest = $get('halbzeitstand_gast') ?? '';
        $referee = $get('schiedsrichter') ?? '';
        $report = $get('bemerkungen') ?? '';
        $author = trim(($get('vorname') ?? '') . ' ' . ($get('nachname') ?? ''));
        $authorTeam = $get('mannschaft') ?? '';
        $assessment = $get('wertung') ?? '';

        /**
         * @var Competition $competitionObject
         */
        $competitionObject = $this->competitionRepository->findOneByName($competition);
        $homeTeam = $this->teamRepository->findOneByName($home);
        $guestTeam = $this->teamRepository->findOneByName($guest);

        /**
         * @var Game $game
         */
        $game = $this->gameRepository->findOneByCompetitionRoundHomeGuest($competitionObject, $round, $homeTeam, $guestTeam);

        if (empty($game)) {
            return;
        }

        if (!empty($game->getGoalsHome1())) {
            //game already processed - only add additional comment if present
            if (!empty($report)) {
                $existingReport = (string)$game->getGameReport();
                $existingAuthor = (string)$game->getGameReportAuthor();
                $game->setGameReport($existingReport . PHP_EOL . PHP_EOL . $report . PHP_EOL . " von " . $author . ", " . $authorTeam);
                $game->setGameReportAuthor($existingAuthor ? ($existingAuthor . ", " . $author) : $author);
                $this->gameRepository->update($game);
                $this->persistenceManager->persistAll();
            }

            return;
        }

        $game->setGoalsHome1(intval($halfHome));
        $game->setGoalsHome2(intval($finalHome));
        $game->setGoalsGuest1(intval($halfGuest));
        $game->setGoalsGuest2(intval($finalGuest));

        if (!empty($assessment)) {
            $game->setAddinfo("Wertung");
        }

        if (!empty($report)) {
            $game->setGameReport($report . PHP_EOL . " von " . $author . ", " . $authorTeam);
            $game->setGameReportAuthor($author);
        }

        //set finished
        $game->setStatus(2);

        $this->gameRepository->update($game);

        if ($referee === "Nicht anwesend") {
            $refereeProfile = $game->getReferee();
            $refereeLastName = $refereeProfile?->getLastName();
            if ($refereeLastName) {
                /**
                 * @var Team $teamToPenalty
                 */
                $teamToPenalty = $this->teamRepository->findOneByName($refereeLastName);

                if (!empty($teamToPenalty)) {
                    $penalty = new CompetitionPenalty();
                    $penalty->setTeam($teamToPenalty);
                    $penalty->setCompetition($competitionObject);
                    $penalty->setGame($game);
                    $penalty->setPointsPos(1);
                    $penalty->setComment("-1 Punkt, " . $authorTeam . " meldete: " . $teamToPenalty->getName() . " hat den Schiedsrichter nicht gestellt, " . $round);
                    $penalty->setPid($competitionObject->getPid());

                    $this->competitionPenaltyRepository->add($penalty);
                }
            }
        }

        $this->persistenceManager->persistAll();
    }
}
