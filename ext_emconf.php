<?php

$EM_CONF['sentry_client'] = [
    'title' => 'Sentry Client',
    'description' => 'Sentry Client for TYPO3 - https://www.getsentry.com/',
    'category' => 'services',
    'version' => '3.1.0',
    'state' => 'stable',
    'author' => 'Christoph Lehmann',
    'author_email' => 'christoph.lehmann@networkteam.com',
    'author_company' => 'networkteam GmbH',
    'constraints' =>
        [
            'depends' =>
                [
                    'typo3' => '10.4.0-11.5.99'
                ],
        ],
];
