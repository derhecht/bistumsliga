<?php

namespace Bistumsliga\Bistumsliga\DataProcessor;

use Bistumsliga\Bistumsliga\Domain\Model\Competition;
use Bistumsliga\Bistumsliga\Domain\Model\CompetitionPenalty;
use Bistumsliga\Bistumsliga\Domain\Model\Game;
use Bistumsliga\Bistumsliga\Domain\Model\Team;
use Bistumsliga\Bistumsliga\Domain\Repository\CompetitionPenaltyRepository;
use Bistumsliga\Bistumsliga\Domain\Repository\CompetitionRepository;
use Bistumsliga\Bistumsliga\Domain\Repository\GameRepository;
use Bistumsliga\Bistumsliga\Domain\Repository\ProfileRepository;
use Bistumsliga\Bistumsliga\Domain\Repository\TeamRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;

class GameReportDataProcessor extends \In2code\Powermail\DataProcessor\AbstractDataProcessor
{
    /**
     * @var PersistenceManager
     */
    protected $persistenceManager;

    /**
     * @var CompetitionPenaltyRepository
     */
    protected $competitionPenaltyRepository;

    /**
     * @var CompetitionRepository
     */
    protected $competitionRepository;

    /**
     * @var TeamRepository
     */
    protected $teamRepository;

    /**
     * @var GameRepository
     */
    protected $gameRepository;

    /**
     * @var ProfileRepository
     */
    protected $profileRepository;

    public function __construct(CompetitionRepository $competitionRepository, TeamRepository $teamRepository, CompetitionPenaltyRepository $competitionPenaltyRepository, GameRepository $gameRepository, ProfileRepository $profileRepository, PersistenceManager $persistenceManager)
    {
        $this->competitionRepository = $competitionRepository;
        $this->teamRepository = $teamRepository;
        $this->competitionPenaltyRepository = $competitionPenaltyRepository;
        $this->gameRepository = $gameRepository;
        $this->profileRepository = $profileRepository;
        $this->persistenceManager = $persistenceManager;
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

        $game->setGoalsHome1(intval($halfHome, 10));
        $game->setGoalsHome2(intval($finalHome, 10));
        $game->setGoalsGuest1(intval($halfGuest, 10));
        $game->setGoalsGuest2(intval($finalGuest, 10));

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
            $refereeLastName = $refereeProfile ? $refereeProfile->getLastName() : null;
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
