<?php

namespace Derhecht\Bistumsliga\Frontend\View;

use Sys25\RnBase\Frontend\Marker\Templates;
use Sys25\RnBase\Frontend\Request\RequestInterface;
use System25\T3sports\Frontend\Marker\TeamMarker;
use System25\T3sports\Frontend\View\TeamListView;
use tx_rnbase;

class BistumsligaTeamListView extends TeamListView
{
    /**
     * Erstellen des Frontend-Outputs.
     */
    public function createOutput($template, RequestInterface $request, $formatter)
    {
        $viewData = $request->getViewContext();
        // Die ViewData bereitstellen
        $teams = $viewData->offsetGet('teams');
        $teamsAfterFilter = [];

        //filter teams
        foreach ($teams as $team) {
            if($team->getClub()) {
                $teamsAfterFilter[] = $team;
            }
        }

        $listBuilder = tx_rnbase::makeInstance('tx_rnbase_util_ListBuilder');

        $template = $listBuilder->render($teamsAfterFilter, $viewData, $template, TeamMarker::class, 'teamlist.team.', 'TEAM', $formatter);

        $out = Templates::substituteMarkerArrayCached($template);

        return $out;
    }
}