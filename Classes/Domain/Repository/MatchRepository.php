<?php

namespace Bistumsliga\Bistumsliga\Domain\Repository;

use Bistumsliga\Bistumsliga\Domain\Model\Game;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\Storage\Typo3DbQueryParser;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

class MatchRepository extends AbstractRepository
{

    public function findOneByCompetitionRoundHomeGuest($competition, $round, $home, $guest)
    {
        $query = $this->createQuery();

        $query->matching(
            $query->logicalOr(
                $query->logicalAnd(
                    $query->equals("competition", $competition->getUid()),
                    $query->equals("round_name", $round),
                    $query->equals("home", $home->getUid()),
                    $query->equals("guest", $guest->getUid())
                ),
                $query->logicalAnd(
                    $query->equals("competition", $competition->getUid()),
                    $query->equals("round_name", $round),
                    $query->equals("home", $guest->getUid()),
                    $query->equals("guest", $home->getUid())
                ))
        );

        return $query->execute()->getFirst();
    }
}
