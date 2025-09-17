<?php

namespace Bistumsliga\Bistumsliga\DataProcessor;

use Bistumsliga\Bistumsliga\Domain\Model\Competition;
use Bistumsliga\Bistumsliga\Domain\Model\CompetitionPenalty;
use Bistumsliga\Bistumsliga\Domain\Model\Match;
use Bistumsliga\Bistumsliga\Domain\Model\Team;
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

    public __construct(CompetitionRepository $competitionRepository, TeamRepository $teamRepository, CompetitionPenaltyRepository $competitionPenaltyRepository, MatchRepository $matchRepository, ProfileRepository $profileRepository, PersistenceManager $persistenceManager)
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
        if (is_null($this->getMail()->getAnswersByFieldMarker()['liga'])) {
            return;
        }

        $competition = $this->getMail()->getAnswersByFieldMarker()['liga']->getValue();
        $round = $this->getMail()->getAnswersByFieldMarker()['spieltag']->getValue();
        $home = $this->getMail()->getAnswersByFieldMarker()['heimmannschaft']->getValue();
        $guest = $this->getMail()->getAnswersByFieldMarker()['gastmannschaft']->getValue();
        $finalHome = $this->getMail()->getAnswersByFieldMarker()['endstand']->getValue();
        $finalGuest = $this->getMail()->getAnswersByFieldMarker()['endstand_gast']->getValue();
        $halfHome = $this->getMail()->getAnswersByFieldMarker()['halbzeitstand']->getValue();
        $halfGuest = $this->getMail()->getAnswersByFieldMarker()['halbzeitstand_gast']->getValue();
        $referee = $this->getMail()->getAnswersByFieldMarker()['schiedsrichter']->getValue();
        $report = $this->getMail()->getAnswersByFieldMarker()['bemerkungen']->getValue();
        $author = $this->getMail()->getAnswersByFieldMarker()['vorname']->getValue() . " " . $this->getMail()->getAnswersByFieldMarker()['nachname']->getValue();
        $authorTeam = $this->getMail()->getAnswersByFieldMarker()['mannschaft']->getValue();
        $assessment = $this->getMail()->getAnswersByFieldMarker()['wertung']->getValue();

        /**
         * @var Competition $competitionObject
         */
        $competitionObject = $this->competitionRepository->findOneByName($competition);
        $homeTeam = $this->teamRepository->findOneByName($home);
        $guestTeam = $this->teamRepository->findOneByName($guest);

        /**
         * @var Match $match
         */
        $match = $this->matchRepository->findOneByCompetitionRoundHomeGuest($competitionObject, $round, $homeTeam, $guestTeam);

        if (empty($match)) {
            return;
        }

        if (!empty($match->getGoalsHome1())) {
            //match already processed - only add additional comment if present
            if (!empty($report)) {
                $match->setGameReport($match->getGameReport() . PHP_EOL . PHP_EOL . $report . PHP_EOL . " von " . $author . ", " . $authorTeam);
                $match->setGameReportAuthor($match->getGameReportAuthor() . ", " . $author);
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

            /**
             * @var Team $teamToPenalty
             */
            $teamToPenalty = $this->teamRepository->findOneByName($match->getReferee()->getLastName());

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

        $this->persistenceManager->persistAll();
    }
}
