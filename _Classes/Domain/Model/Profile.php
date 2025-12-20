<?php

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
