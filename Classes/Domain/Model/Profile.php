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
final class Profile extends AbstractEntity
{
    protected string $lastName = '';

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): void
    {
        $this->lastName = $lastName;
    }
}
