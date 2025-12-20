<?php

namespace Bistumsliga\Bistumsliga\DataProcessor;

use Derhecht\Bistumsliga\Domain\Model\Competition;
use Derhecht\Bistumsliga\Domain\Model\CompetitionPenalty;
use Derhecht\Bistumsliga\Domain\Model\Game;
use Derhecht\Bistumsliga\Domain\Model\Team;
use Bistumsliga\Bistumsliga\Domain\Repository\CompetitionPenaltyRepository;
use Bistumsliga\Bistumsliga\Domain\Repository\CompetitionRepository;
use Bistumsliga\Bistumsliga\Domain\Repository\MatchRepository;
use Bistumsliga\Bistumsliga\Domain\Repository\ProfileRepository;
use Bistumsliga\Bistumsliga\Domain\Repository\TeamRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;

class MatchReportDataProcessor extends \In2code\Powermail\DataProcessor\AbstractDataProcessor
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
     * @var MatchRepository
     */
    protected $matchRepository;

    /**
     * @var ProfileRepository
     */
    protected $profileRepository;

    public function __construct(CompetitionRepository $competitionRepository, TeamRepository $teamRepository, CompetitionPenaltyRepository $competitionPenaltyRepository, MatchRepository $matchRepository, ProfileRepository $profileRepository, PersistenceManager $persistenceManager)
    {
        $this->competitionRepository = $competitionRepository;
        $this->teamRepository = $teamRepository;
        $this->competitionPenaltyRepository = $competitionPenaltyRepository;
        $this->matchRepository = $matchRepository;
        $this->profileRepository = $profileRepository;
        $this->persistenceManager = $persistenceManager;
    }

    public function matchReportDataProcessor(): void
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
         * @var Game $match
         */
        $match = $this->matchRepository->findOneByCompetitionRoundHomeGuest($competitionObject, $round, $homeTeam, $guestTeam);

        if (empty($match)) {
            return;
        }

        if (!empty($match->getGoalsHome1())) {
            //match already processed - only add additional comment if present
            if (!empty($report)) {
                $existingReport = (string)$match->getGameReport();
                $existingAuthor = (string)$match->getGameReportAuthor();
                $match->setGameReport($existingReport . PHP_EOL . PHP_EOL . $report . PHP_EOL . " von " . $author . ", " . $authorTeam);
                $match->setGameReportAuthor($existingAuthor ? ($existingAuthor . ", " . $author) : $author);
                $this->matchRepository->update($match);
                $this->persistenceManager->persistAll();
            }

            return;
        }

        $match->setGoalsHome1(intval($halfHome, 10));
        $match->setGoalsHome2(intval($finalHome, 10));
        $match->setGoalsGuest1(intval($halfGuest, 10));
        $match->setGoalsGuest2(intval($finalGuest, 10));

        if (!empty($assessment)) {
            $match->setAddinfo("Wertung");
        }

        if (!empty($report)) {
            $match->setGameReport($report . PHP_EOL . " von " . $author . ", " . $authorTeam);
            $match->setGameReportAuthor($author);
        }

        //set finished
        $match->setStatus(2);

        $this->matchRepository->update($match);

        if ($referee === "Nicht anwesend") {
            $refereeProfile = $match->getReferee();
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
                    $penalty->setGame($match);
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
