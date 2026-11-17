<?php

// Thème vertical (extension custom-theme): declares its Angular module to
// SuiteCRM 8, which loads it when the interface starts (module federation).
// The module (public/, built from app/ by make front@build) is under
// extensions/CustomTheme/ in SuiteCRM: the slug in PascalCase.

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Container;

if (!isset($container)) {
    return;
}

/** @var Container $container */
$extensions = $container->getParameter('extensions');
$extensions = is_array($extensions) ? $extensions : [];

$extensions['CustomTheme'] = [
    'remoteEntry' => '../extensions/CustomTheme/remoteEntry.js',
    'remoteName' => 'CustomTheme',
    'enabled' => true,
    'extension_name' => 'Thème vertical',
    'extension_uri' => 'https://github.com/david-grosskost/suitecrm-ext-custom-theme',
    'description' => "Un rail vertical d'icônes à la place du menu horizontal, et l'interface aux couleurs de l'entreprise.",
    'version' => '0.1.0',
    'author' => 'David Grosskost',
    'author_uri' => 'https://david-grosskost.fr',
    'license' => 'proprietary',
];

$container->setParameter('extensions', $extensions);
