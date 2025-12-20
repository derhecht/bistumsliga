<?php

declare(strict_types=1);

/*
 * This file is part of the package derhecht/bistumsliga.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */
namespace Derhecht\Bistumsliga\Domain\Repository;

use Derhecht\Bistumsliga\Domain\Model\Game;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\Storage\Typo3DbQueryParser;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

final class GameRepository extends AbstractRepository
{
    public function findOneByCompetitionRoundHomeGuest($competition, string $round, $home, $guest): ?Game
    {
        $query = $this->createQuery();

        $query->matching(
            $query->logicalOr(
                $query->logicalAnd(
                    $query->equals('competition', $competition),
                    $query->equals('round_name', $round),
                    $query->equals('home', $home),
                    $query->equals('guest', $guest)
                ),
                $query->logicalAnd(
                    $query->equals('competition', $competition),
                    $query->equals('round_name', $round),
                    $query->equals('home', $guest),
                    $query->equals('guest', $home)
                ))
        );

        return $query->execute()->getFirst();
    }
}