<?php

$EM_CONF['sentry_client'] = [
    'title' => 'Sentry Client',
    'description' => 'Sentry Client for TYPO3 - https://www.getsentry.com/',
    'category' => 'services',
    'version' => '2.1.2',
    'state' => 'stable',
    'author' => 'Christoph Lehmann',
    'author_email' => 'christoph.lehmann@networkteam.com',
    'author_company' => 'networkteam GmbH',
    'constraints' =>
        [
            'depends' =>
                [
                    'typo3' => '8.7.0-9.5.99',
                ],
        ],
];
