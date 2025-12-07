<?php

namespace Bistumsliga\Bistumsliga\Domain\Model;

class Profile extends \TYPO3\CMS\Extbase\DomainObject\AbstractDomainObject
{
    /**
     * @var string
     */
    protected $lastName;

    /**
     * @return string
     */
    public function getLastName(): string
    {
        return $this->lastName;
    }

    /**
     * @param string $lastName
     */
    public function setLastName(string $lastName): void
    {
        $this->lastName = $lastName;
    }
}
