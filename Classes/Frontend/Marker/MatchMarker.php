<?php

namespace Derhecht\Bistumsliga\Frontend\Marker;

use System25\T3sports\Frontend\Marker\MatchMarker;

class BistumsligaMatchMarker extends MatchMarker
{
    protected function prepareTemplate($template, $match, $formatter, $confId, $marker)
    {
        $this->prepareFields($match, $formatter, $confId);
        Misc::callHook('cfc_league_fe', 'matchMarker_initRecord', [
            'match' => $match,
            'template' => &$template,
            'confid' => $confId,
            'marker' => $marker,
            'formatter' => $formatter,
        ], $this);

        // Jetzt die dynamischen Werte setzen, dafür müssen die Ticker vorbereitet werden
        // Der Report wird in der Action gesetzt
        $report = $match->getMatchReport();
        if (is_object($report)) {
            $this->pushTT('addDynamicMarkers');
            $this->addDynamicMarkers($template, $report, $formatter, $confId, $marker);
            $this->pullTT();
        }

        $this->pushTT('parse referee');
        if (self::containsMarker($template, $marker.'_REFEREE_NAME') && $match->getReferee() instanceof Profile) {
            $template = str_replace('###MATCH_REFEREE_NAME###', $match->getReferee()->getName(), $template);
        }
        $this->pushTT('parse stadium');
        if (self::containsMarker($template, $marker.'_STADIUM_NAME')) {
            $template = str_replace('###MATCH_STADIUM_NAME###', $match->getStadium(), $template);
        }
        $this->pullTT();


        $this->pushTT('parse home team');
        if (self::containsMarker($template, $marker.'_HOME')) {
            $template = $this->teamMarker->parseTemplate($template, $match->getHome(), $formatter, $confId.'home.', $marker.'_HOME');
        }
        $this->pullTT();
        $this->pushTT('parse guest team');
        if (self::containsMarker($template, $marker.'_GUEST')) {
            $template = $this->teamMarker->parseTemplate($template, $match->getGuest(), $formatter, $confId.'guest.', $marker.'_GUEST');
        }
        $this->pushTT('parse arena');
        if (self::containsMarker($template, $marker.'_ARENA_')) {
            $template = $this->_addArena($template, $match, $formatter, $confId.'arena.', $marker.'_ARENA');
        }
        if (self::containsMarker($template, $marker.'_SETRESULTS')) {
            $template = $this->_addSetResults($template, $match, $formatter, $confId.'setresults.', $marker.'_SETRESULT');
        }
        $this->pullTT();

        $template = $this->addTickerLists($template, $match, $formatter, $confId, $marker);

        $this->pushTT('add media');
        $template = $this->_addMedia($match, $formatter, $template, $confId, $marker);
        $this->pullTT();

        // Add competition
        if (self::containsMarker($template, $marker.'_COMPETITION_')) {
            $template = $this->competitionMarker->parseTemplate($template, $match->getCompetition(), $formatter, $confId.'competition.', $marker.'_COMPETITION');
        }

        return $template;
    }
}