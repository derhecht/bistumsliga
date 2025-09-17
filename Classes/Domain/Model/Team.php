<?php

namespace Bistumsliga\Bistumsliga\Domain\Model;

class Team extends \TYPO3\CMS\Extbase\DomainObject\AbstractDomainObject
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }
}
