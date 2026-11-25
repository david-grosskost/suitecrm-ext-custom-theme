<?php

// Uninstall script of custom-theme, run before the files are removed, in
// SuiteCRM, as the administrator, from public/legacy.
//
// It takes away the stylesheet laid over the legacy theme and clears what
// SuiteCRM cached of the two together, which would otherwise go on being
// served with the client's colours in it.
//
// The cap on the menu is left as it is: it holds no reference to this
// extension, an administrator may have set it on purpose, and putting back a
// value nobody asked for is worse than leaving a larger menu.
//
// Its message is returned, not printed (echo is refused by the scanner of
// the Module Loader): the caller shows it.

declare(strict_types=1);

use SuiteCrmExt\CustomTheme\LegacyTheme;

require_once __DIR__ . '/../legacy/include/custom-theme/Palette.php';
require_once __DIR__ . '/../legacy/include/custom-theme/LegacyTheme.php';

if (is_file(LegacyTheme::TARGET)) {
    unlink(LegacyTheme::TARGET);
}
LegacyTheme::forgetTheCache();

return 'custom-theme : les écrans historiques ont repris les couleurs de SuiteCRM.';
