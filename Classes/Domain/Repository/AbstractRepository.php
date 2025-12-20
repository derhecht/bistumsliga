<?php

declare(strict_types=1);

/*
 * This file is part of the package derhecht/bistumsliga.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Derhecht\Bistumsliga\Domain\Repository;

use TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings;

use TYPO3\CMS\Extbase\Persistence\Repository;
abstract class AbstractRepository extends Repository
{
    public function __construct(Typo3QuerySettings $querySettings)
    {
        parent::__construct();
        $querySettings->setRespectStoragePage(false);
        $this->setDefaultQuerySettings($querySettings);
    }
}
