<?php

namespace Derhecht\Bistumsliga\Frontend\Marker;

use System25\T3sports\Frontend\Marker\MatchMarker;
use System25\T3sports\Model\Profile;

class BistumsligaMatchMarker extends MatchMarker
{
    protected function prepareTemplate($template, $match, $formatter, $confId, $marker)
    {
        $template = parent::prepareTemplate($template, $match, $formatter, $confId, $marker);

        $this->pushTT('parse referee');
        if (self::containsMarker($template, $marker.'_REFEREE_NAME') && $match->getReferee() instanceof Profile) {
            $template = str_replace('###MATCH_REFEREE_NAME###', $match->getReferee()->getName(), $template);
        }
        $this->pushTT('parse stadium');
        if (self::containsMarker($template, $marker.'_STADIUM_NAME')) {
            $template = str_replace('###MATCH_STADIUM_NAME###', $match->getStadium(), $template);
        }
        $this->pullTT();

        return $template;
    }
}