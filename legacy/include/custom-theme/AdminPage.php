<?php

declare(strict_types=1);

// Thème vertical (extension custom-theme): the administration screen where
// the colours are chosen. Reached from Administration, and only by an
// administrator: SuiteCRM has already checked the session when it hands over
// here (auth true in the registry), and is_admin decides the rest.
//
// It writes nothing but five colours, in the config table, in the category of
// this extension. Nothing of SuiteCRM is touched.

use SuiteCrmExt\CustomTheme\AdminForm;
use SuiteCrmExt\CustomTheme\ConfigTable;
use SuiteCrmExt\CustomTheme\Palette;
use SuiteCrmExt\CustomTheme\Reply;

require_once __DIR__ . '/Palette.php';
require_once __DIR__ . '/Settings.php';
require_once __DIR__ . '/ConfigTable.php';
require_once __DIR__ . '/AdminForm.php';
require_once __DIR__ . '/Reply.php';

/** @var \User|null $current_user Set by SuiteCRM */
global $current_user;

if (!is_admin($current_user)) {
    Reply::send([
        'status' => 403,
        'headers' => ['Content-Type' => 'text/plain; charset=utf-8', 'Cache-Control' => 'no-cache'],
        'body' => "Cette page est réservée aux administrateurs.\n",
    ]);

    return;
}

$settings = ConfigTable::forSuiteCrm();
$saved = false;

if (strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET')) === 'POST') {
    $settings->replace(AdminForm::fromPost($_POST));
    $saved = true;
}

Reply::send(Reply::text(
    'text/html',
    AdminForm::page(Palette::settings($settings->read()), 'index.php?entryPoint=customThemeAdmin', $saved),
));
