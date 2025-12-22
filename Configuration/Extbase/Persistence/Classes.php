<?php

declare(strict_types=1);

return [
    \Derhecht\Bistumsliga\Domain\Model\Game::class => [
        'tableName' => 'tx_cfcleague_games'
    ],
    \Derhecht\Bistumsliga\Domain\Model\CompetitionPenalty::class => [
        'tableName' => 'tx_cfcleague_competition_penalty'
    ],
    \Derhecht\Bistumsliga\Domain\Model\Competition::class => [
        'tableName' => 'tx_cfcleague_competition'
    ],
    \Derhecht\Bistumsliga\Domain\Model\Team::class => [
        'tableName' => 'tx_cfcleague_teams'
    ],
    \Derhecht\Bistumsliga\Domain\Model\Profile::class => [
        'tableName' => 'tx_cfcleague_profiles'
    ]
];