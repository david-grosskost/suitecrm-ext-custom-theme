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
     * The dark mode's colours: the same fields, stored with the "dark_"
     * prefix. Shown worked out from the light ones until chosen.
     */
    public const string DARK = DarkPalette::PREFIX;

    /** What a dark field's hidden companions are called. */
    public const string SHOWN = 'shown_';

    public const string KEPT = 'kept_';

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

        // "Recalculate" forgets the dark choices: they are worked out again.
        if (isset($post['recalculate'])) {
            return $values;
        }

        foreach (array_keys(self::FIELDS) as $field) {
            $name = self::DARK . $field;
            $value = $post[$name] ?? null;
            if (!Palette::isColour($value)) {
                continue;
            }
            /** @var string $value */
            $value = strtolower($value);
            // A colour is a choice when it was one already, or when it is not
            // the one the form showed: a shade worked out and left untouched
            // is not stored, so it keeps following the light palette.
            $shown = $post[self::SHOWN . $name] ?? null;
            if (isset($post[self::KEPT . $name]) || !is_string($shown) || strtolower($shown) !== $value) {
                $values[$name] = $value;
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
     * @param array<string, string> $dark   as DarkPalette::settings returns them
     * @param list<string>          $chosen the dark fields the administrator chose
     */
    public static function body(array $values, string $action, bool $saved, array $dark = [], array $chosen = []): string
    {
        $rows = '';
        foreach (self::FIELDS as $field => $label) {
            $rows .= self::row($field, $label, $values[$field] ?? '#000000', '');
        }

        $darkRows = '';
        foreach (self::FIELDS as $field => $label) {
            $value = $dark[$field] ?? DarkPalette::settings([])[$field];
            $name = self::escape(self::DARK . $field);
            $extra = '<input type="hidden" name="' . self::SHOWN . $name . '" value="' . self::escape($value) . '">';
            if (in_array($field, $chosen, true)) {
                $extra .= '<input type="hidden" name="' . self::KEPT . $name . '" value="1">';
            }
            $darkRows .= self::row(self::DARK . $field, $label, $value, $extra);
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
            .ct-admin h3 { grid-column: 1 / -1; margin: 14px 0 0; font-size: 16px; font-weight: 600; }
            .ct-admin .ct-hint { grid-column: 1 / -1; margin: 0; font-size: 13px; color: #64748b; }
            .ct-admin .ct-saved { background: #ecfdf5; color: #065f46; padding: 10px 14px; border-radius: 8px; font-size: 14px; margin: 0 0 16px; }
            </style>
            <div class="ct-head"><h2>Couleurs du thème</h2></div>
            <div class="ct-card">
            {$notice}
            <form method="post" action="{$url}">
            {$rows}<h3>Mode sombre</h3>
            <p class="ct-hint">Calculées à partir des couleurs ci-dessus tant que vous ne les changez pas.</p>
            {$darkRows}<div class="ct-actions">
              <button type="submit" class="ct-keep">Enregistrer</button>
              <button type="submit" name="recalculate" value="1" class="ct-back">Recalculer le mode sombre</button>
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

    /** One line of the form: its label, its colour, and what it shows of it. */
    private static function row(string $field, string $label, string $value, string $extra): string
    {
        $value = self::escape($value);
        $name = self::escape($field);

        return <<<ROW
            <label for="{$name}">{$label}</label>
            <span>
              <input type="color" id="{$name}" name="{$name}" value="{$value}">
              <code>{$value}</code>{$extra}
            </span>

            ROW;
    }

    private static function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
