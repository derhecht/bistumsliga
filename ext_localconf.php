<?php

$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][System25\T3sports\Search\ProfileSearch::class] = [
        'className' => \Derhecht\Bistumsliga\Search\BistumsligaProfileSearch::class
];

$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][System25\T3sports\Frontend\Marker\MatchMarker::class] = [
    'className' => \Derhecht\Bistumsliga\Frontend\Marker\BistumsligaMatchMarker::class
];