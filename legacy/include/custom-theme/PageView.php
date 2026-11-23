<?php

declare(strict_types=1);

namespace SuiteCrmExt\CustomTheme;

/**
 * Shows the colours screen inside SuiteCRM's page (header, breadcrumb,
 * footer), as the legacy administration screens are. AdminPage.php loads
 * SuiteCRM's SugarView before this file.
 */
final class PageView extends \SugarView
{
    /** The template, where SuiteCRM runs (public/legacy). */
    public const string TEMPLATE = 'custom/include/custom-theme/page.tpl';

    public function __construct(private readonly string $title, private readonly string $body)
    {
        parent::__construct();
    }

    public function display(): void
    {
        $administration = \translate('LBL_MODULE_NAME', 'Administration');
        $link = '<a href="index.php?module=Administration&amp;action=index">'
            . htmlspecialchars(is_string($administration) ? $administration : 'Administration', ENT_QUOTES, 'UTF-8')
            . '</a>';
        // Shown by the view's Smarty, as SuiteCRM's views do (echo is refused
        // by the scanner of the Module Loader).
        $this->ss->assign('CT_TITLE', \getClassicModuleTitle(
            'Administration',
            [$link, htmlspecialchars($this->title, ENT_QUOTES, 'UTF-8')],
            false,
        ));
        $this->ss->assign('CT_BODY', $this->body);
        $this->ss->display(self::TEMPLATE);
    }
}
