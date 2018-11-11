<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Sentry Client',
    'description' => 'Sentry Client for TYPO3 - https://www.getsentry.com/',
    'category' => 'services',
    'version' => '2.0.1',
    'state' => 'stable',
    'uploadfolder' => false,
    'createDirs' => '',
    'clearcacheonload' => true,
    'author' => 'Christoph Lehmann',
    'author_email' => 'christoph.lehmann@networkteam.com',
    'author_company' => 'networkteam GmbH',
    'constraints' =>
        [
            'depends' =>
                [
                    'typo3' => '8.7.0-9.5.99',
                ],
            'conflicts' => [],
            'suggests' => []
        ],
];

