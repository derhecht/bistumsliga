<?php

namespace Derhecht\Bistumsliga\Search;

use Sys25\RnBase\Database\Query\Join;
use System25\T3sports\Search\ProfileSearch;
use Sys25\RnBase\Utility\Misc;

class BistumsligaProfileSearch extends ProfileSearch
{
    protected function getTableMappings()
    {
        $tableMapping = [
            'PROFILE' => 'tx_cfcleague_profiles',
            'TEAM' => 'tx_cfcleague_teams',
            'TEAMNOTE' => 'tx_cfcleague_team_notes',
        ];
        // Hook to append other tables
        Misc::callHook('cfc_league', 'search_Profile_getTableMapping_hook', [
            'tableMapping' => &$tableMapping,
        ], $this);

        return $tableMapping;
    }

    protected function getJoins($tableAliases)
    {
        $join = [];
        if (isset($tableAliases['TEAM'])) {
            $join[] = new Join('PROFILE', 'tx_cfcleague_teams', 'FIND_IN_SET(PROFILE.uid, TEAM.players)', 'TEAM');
        }
        if (isset($tableAliases['TEAMNOTE'])) {
            $join[] = new Join('PROFILE', 'tx_cfcleague_team_notes', 'PROFILE.uid = TEAMNOTE.player', 'TEAMNOTE');
        }

        // Hook to append other tables
        Misc::callHook('cfc_league', 'search_Profile_getJoins_hook', [
            'join' => &$join,
            'tableAliases' => $tableAliases,
        ], $this);

        return $join;
    }
}