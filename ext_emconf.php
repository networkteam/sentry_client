<?php

$EM_CONF[$_EXTKEY] = array(
	'title' => 'Sentry Client',
	'description' => 'Sentry Client for TYPO3 - https://www.getsentry.com/',
	'category' => 'services',
	'version' => '1.1.0',
	'state' => 'stable',
	'uploadfolder' => false,
	'createDirs' => '',
	'clearcacheonload' => true,
	'author' => 'Christoph Lehmann',
	'author_email' => 'christoph.lehmann@networkteam.com',
	'author_company' => 'networkteam GmbH',
	'constraints' =>
		array(
			'depends' =>
				array(
					'typo3' => '7.6.0-7.6.99',
				),
			'conflicts' =>
				array(),
			'suggests' =>
				array(),
		),
);
