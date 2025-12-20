<?php

declare(strict_types=1);

/*
 * This file is part of the package derhecht/bistumsliga.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */
namespace Derhecht\Bistumsliga\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
final class Team extends AbstractEntity
{
    protected string $name = '';
    public function getName(): string
    {
        return $this->name;
    }
    public function setName(string $name): void
    {
        $this->name = $name;
    }
}
