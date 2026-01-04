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
use In2code\Powermail\Domain\Model\Mail;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;

class GameReportDataProcessor extends \In2code\Powermail\DataProcessor\AbstractDataProcessor
{
    protected CompetitionRepository $competitionRepository;
    protected TeamRepository $teamRepository;
    protected CompetitionPenaltyRepository $competitionPenaltyRepository;
    protected GameRepository $gameRepository;
    protected ProfileRepository $profileRepository;
    protected \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager $persistenceManager;

    public function __construct(
        \In2code\Powermail\Domain\Model\Mail $mail,
        array $configuration,
        array $settings,
        string $actionMethodName,
        \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer $contentObject,
        ?CompetitionRepository $competitionRepository = null,
        ?TeamRepository $teamRepository = null,
        ?CompetitionPenaltyRepository $competitionPenaltyRepository = null,
        ?GameRepository $gameRepository = null,
        ?ProfileRepository $profileRepository = null,
        ?\TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager $persistenceManager = null
    ) {
        parent::__construct($mail, $configuration, $settings, $actionMethodName, $contentObject);
        $this->contentObject = $contentObject;

        // Fallback für Powermail Instanziierung
        $this->competitionRepository = $competitionRepository ?? \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(CompetitionRepository::class);
        $this->teamRepository = $teamRepository ?? \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(TeamRepository::class);
        $this->competitionPenaltyRepository = $competitionPenaltyRepository ?? \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(CompetitionPenaltyRepository::class);
        $this->gameRepository = $gameRepository ?? \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(GameRepository::class);
        $this->profileRepository = $profileRepository ?? \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(ProfileRepository::class);
        $this->persistenceManager = $persistenceManager ?? \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager::class);
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
        $report = $get('bemerkungen') ?? '';
        $author = trim(($get('vorname') ?? '') . ' ' . ($get('nachname') ?? ''));
        $authorTeam = $get('mannschaft') ?? '';
        $assessment = $get('wertung') ?? '';
        $penalty = $get('neunmeterschiessen') ?? '';
        $penaltyHome = $get('standnachneunmeterschiessenheim') ?? '';
        $penaltyGuest = $get('standnachneunmeterschiessengaeste') ?? '';

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
            $game->setLinkReport(1);
        }

        if(intval($penalty) > 0) {
            $game->setIsPenalty(1);
            $game->setGoalsHomeAp(intval($penaltyHome));
            $game->setGoalsGuestAp(intval($penaltyGuest));
        }

        //set finished
        $game->setStatus(2);

        $this->gameRepository->update($game);

        $this->persistenceManager->persistAll();
    }
}
