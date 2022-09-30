<?php

$EM_CONF['sentry_client'] = [
    'title' => 'Sentry Client',
    'description' => 'A Sentry client for TYPO3. It forwards errors and exceptions to Sentry - https://sentry.io/',
    'category' => 'services',
    'version' => '4.2.0',
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
