<?php
declare(strict_types = 1);

return [
    \Bistumsliga\Bistumsliga\Domain\Model\Match::class => [
        'tableName' => 'tx_cfcleague_games'
    ],
    \Bistumsliga\Bistumsliga\Domain\Model\CompetitionPenalty::class => [
        'tableName' => 'tx_cfcleague_competition_penalty'
    ],
    \Bistumsliga\Bistumsliga\Domain\Model\Competition::class => [
        'tableName' => 'tx_cfcleague_competition'
    ],
    \Bistumsliga\Bistumsliga\Domain\Model\Team::class => [
        'tableName' => 'tx_cfcleague_teams'
    ],
    \Bistumsliga\Bistumsliga\Domain\Model\Profile::class => [
        'tableName' => 'tx_cfcleague_profiles'
    ]
];
