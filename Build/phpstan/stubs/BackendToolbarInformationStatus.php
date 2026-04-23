<?php
declare(strict_types=1);

namespace TYPO3\CMS\Backend\Toolbar;

/** PHPStan stub: introduced as enum in TYPO3 14, doesn't exist in TYPO3 < 14 */
enum InformationStatus: string
{
    case NOTICE = '';
    case INFO = 'info';
    case OK = 'success';
    case WARNING = 'warning';
    case ERROR = 'danger';
}
