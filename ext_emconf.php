<?php

$EM_CONF['sentry_client'] = [
    'title' => 'Sentry Client',
    'description' => 'A Sentry client for TYPO3. It forwards errors and exceptions to Sentry - https://sentry.io/',
    'category' => 'services',
    'version' => '5.0.2',
    'state' => 'stable',
    'author' => 'Christoph Lehmann',
    'author_email' => 'christoph.lehmann@networkteam.com',
    'author_company' => 'networkteam GmbH',
    'constraints' =>
        [
            'depends' =>
                [
                    'typo3' => '11.5-12.99.99'
                ],
        ],
];
