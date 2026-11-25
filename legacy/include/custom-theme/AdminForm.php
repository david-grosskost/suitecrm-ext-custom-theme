<?php

declare(strict_types=1);

namespace SuiteCrmExt\CustomTheme;

/**
 * The administration form: what it accepts, and what it draws. Kept apart from
 * the SuiteCRM page itself so it can be tested without SuiteCRM.
 */
final class AdminForm
{
    /** The fields, in the order they are shown, with their French label. */
    public const array FIELDS = [
        'primary' => 'Couleur principale',
        'header_from' => 'Bandeau : début du dégradé',
        'header_to' => 'Bandeau : fin du dégradé',
        'surface' => 'Fond des écrans',
        'rail' => 'Fond du menu',
        'blue' => 'Couleur des boutons, liens et champs actifs',
    ];

    /**
     * The colours to store, read from what was posted. Anything that is not a
     * known field or not a colour is dropped rather than corrected: a value
     * this refuses falls back to the default, which is always readable.
     *
     * @param array<mixed> $post
     *
     * @return array<string, string>
     */
    public static function fromPost(array $post): array
    {
        if (isset($post['reset'])) {
            return [];
        }

        $values = [];
        foreach (array_keys(self::FIELDS) as $field) {
            $value = $post[$field] ?? null;
            if (Palette::isColour($value)) {
                /** @var string $value */
                $values[$field] = strtolower($value);
            }
        }

        return $values;
    }

    /**
     * The form, drawn with the colours it is editing so the choice can be seen
     * while it is made. A fragment, not a page: it is shown inside SuiteCRM's
     * administration screen, so every rule is scoped to its own class and
     * nothing of the page around it is touched.
     *
     * @param array<string, string> $values as Palette::settings returns them
     */
    public static function body(array $values, string $action, bool $saved): string
    {
        $rows = '';
        foreach (self::FIELDS as $field => $label) {
            $value = self::escape($values[$field] ?? '#000000');
            $name = self::escape($field);
            $rows .= <<<ROW
                <label for="{$name}">{$label}</label>
                <span>
                  <input type="color" id="{$name}" name="{$name}" value="{$value}">
                  <code>{$value}</code>
                </span>

                ROW;
        }

        $notice = $saved ? '<p class="ct-saved">Couleurs enregistrées. Rechargez le CRM pour les voir.</p>' : '';
        $primary = self::escape($values['primary'] ?? '#0284c5');
        $from = self::escape($values['header_from'] ?? '#0369a0');
        $to = self::escape($values['header_to'] ?? '#3abff8');
        $url = self::escape($action);

        return <<<HTML
            <div class="ct-admin">
            <style>
            .ct-admin { font-family: system-ui, sans-serif; color: #1d283a; max-width: 600px; }
            .ct-admin .ct-head { background: linear-gradient(to right top, {$from}, {$to}); color: #fff; padding: 16px 24px; border-radius: 10px 10px 0 0; }
            .ct-admin .ct-head h2 { margin: 0; font-size: 18px; font-weight: 600; color: #fff; }
            .ct-admin .ct-card { background: #fff; border: 1px solid #e2e8f0; border-top: 0; border-radius: 0 0 10px 10px; padding: 22px 24px; }
            .ct-admin form { display: grid; grid-template-columns: 1fr auto; gap: 14px 20px; align-items: center; margin: 0; }
            .ct-admin label { font-size: 14px; margin: 0; font-weight: normal; }
            .ct-admin form > span { display: flex; align-items: center; gap: 10px; }
            .ct-admin input[type=color] { width: 44px; height: 30px; padding: 0; border: 1px solid #cbd5e1; border-radius: 6px; background: none; }
            .ct-admin code { font-size: 12px; color: #64748b; background: none; padding: 0; }
            .ct-admin .ct-actions { grid-column: 1 / -1; display: flex; gap: 12px; margin-top: 10px; }
            .ct-admin button { font: inherit; padding: 9px 18px; border-radius: 8px; border: 0; cursor: pointer; }
            .ct-admin .ct-keep { background: {$primary}; color: #fff; }
            .ct-admin .ct-back { background: none; border: 1px solid #cbd5e1; color: #475569; }
            .ct-admin .ct-saved { background: #ecfdf5; color: #065f46; padding: 10px 14px; border-radius: 8px; font-size: 14px; margin: 0 0 16px; }
            </style>
            <div class="ct-head"><h2>Couleurs du thème</h2></div>
            <div class="ct-card">
            {$notice}
            <form method="post" action="{$url}">
            {$rows}<div class="ct-actions">
              <button type="submit" class="ct-keep">Enregistrer</button>
              <button type="submit" name="reset" value="1" class="ct-back">Rétablir les couleurs d'origine</button>
            </div>
            </form>
            </div>
            </div>
            <script>
            for (const input of document.querySelectorAll('.ct-admin input[type=color]')) {
              input.addEventListener('input', () => {
                const shown = input.parentElement.querySelector('code');
                if (shown) { shown.textContent = input.value; }
              });
            }
            </script>
            HTML;
    }

    private static function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
