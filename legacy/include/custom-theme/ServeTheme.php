<?php

// Entry point customTheme (legacy/index.php?entryPoint=customTheme), declared
// by legacy/Extension/application/Ext/EntryPointRegistry/custom-theme.php,
// open without login so the theme also applies to the login screen. It gives
// only the colours chosen in Administration and the accent of SuiteCRM's
// standard modules: nothing of this instance, nothing of the CRM data.

declare(strict_types=1);

use SuiteCrmExt\CustomTheme\ConfigTable;
use SuiteCrmExt\CustomTheme\Reply;
use SuiteCrmExt\CustomTheme\Theme;

require_once __DIR__ . '/Settings.php';
require_once __DIR__ . '/ConfigTable.php';
require_once __DIR__ . '/Palette.php';
require_once __DIR__ . '/Overlay.php';
require_once __DIR__ . '/LegacyTheme.php';
require_once __DIR__ . '/AngularTheme.php';
require_once __DIR__ . '/Theme.php';
require_once __DIR__ . '/Reply.php';

Reply::send(Reply::text('application/json', Theme::json(ConfigTable::forSuiteCrm()->read())));
