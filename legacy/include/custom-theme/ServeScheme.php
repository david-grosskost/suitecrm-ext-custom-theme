<?php

// Entry point customThemeScheme (legacy/index.php?entryPoint=customThemeScheme),
// declared by legacy/Extension/application/Ext/EntryPointRegistry/custom-theme.php,
// for logged-in users only. It reads (GET) or changes (POST, field "scheme")
// the current user's light or dark choice, kept in their own preferences:
// nothing of anyone else, nothing of the CRM data.

declare(strict_types=1);

use SuiteCrmExt\CustomTheme\Reply;
use SuiteCrmExt\CustomTheme\Scheme;

require_once __DIR__ . '/Scheme.php';
require_once __DIR__ . '/Reply.php';

/** @var mixed $user */
$user = $GLOBALS['current_user'] ?? null;
if (!$user instanceof User) {
    Reply::send(['status' => 401, 'headers' => ['Content-Type' => 'application/json; charset=utf-8'], 'body' => '{}']);

    return;
}

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
if (is_string($method) && strtoupper($method) === 'POST') {
    $user->setPreference(Scheme::PREFERENCE, Scheme::of($_POST['scheme'] ?? null), 0, Scheme::CATEGORY);
    $user->savePreferencesToDB();
}

Reply::send(Reply::text('application/json', Scheme::json(Scheme::of($user->getPreference(Scheme::PREFERENCE, Scheme::CATEGORY)))));
