<?php

$EM_CONF['sentry_client'] = [
    'title' => 'Sentry Client',
    'description' => 'Sentry Client for TYPO3 - https://www.getsentry.com/',
    'category' => 'services',
    'version' => '3.0.0',
    'state' => 'stable',
    'author' => 'Christoph Lehmann',
    'author_email' => 'christoph.lehmann@networkteam.com',
    'author_company' => 'networkteam GmbH',
    'constraints' =>
        [
            'depends' =>
                [
                    'typo3' => '9.5.0-10.5.99'
                ],
        ],
];
