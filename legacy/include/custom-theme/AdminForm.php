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
     * The page, drawn with the colours it is editing so the choice can be seen
     * while it is made.
     *
     * @param array<string, string> $values as Palette::settings returns them
     */
    public static function page(array $values, string $action, bool $saved): string
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

        $notice = $saved ? '<p class="saved">Couleurs enregistrées. Rechargez le CRM pour les voir.</p>' : '';
        $primary = self::escape($values['primary'] ?? '#0284c5');
        $from = self::escape($values['header_from'] ?? '#0369a0');
        $to = self::escape($values['header_to'] ?? '#3abff8');
        $surface = self::escape($values['surface'] ?? '#f1f5f9');
        $url = self::escape($action);

        return <<<HTML
            <!DOCTYPE html>
            <html lang="fr">
            <head>
            <meta charset="utf-8">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <title>Couleurs du thème</title>
            <style>
            body { margin: 0; font-family: system-ui, sans-serif; background: {$surface}; color: #1d283a; }
            header { background: linear-gradient(to right top, {$from}, {$to}); color: #fff; padding: 20px 28px; }
            header h1 { margin: 0; font-size: 20px; font-weight: 600; }
            main { max-width: 560px; margin: 28px auto; background: #fff; border-radius: 10px; padding: 24px 28px; }
            form { display: grid; grid-template-columns: 1fr auto; gap: 14px 20px; align-items: center; }
            label { font-size: 14px; }
            span { display: flex; align-items: center; gap: 10px; }
            input[type=color] { width: 44px; height: 30px; padding: 0; border: 1px solid #cbd5e1; border-radius: 6px; background: none; }
            code { font-size: 12px; color: #64748b; }
            .actions { grid-column: 1 / -1; display: flex; gap: 12px; margin-top: 10px; }
            button { font: inherit; padding: 9px 18px; border-radius: 8px; border: 0; cursor: pointer; }
            .keep { background: {$primary}; color: #fff; }
            .back { background: none; border: 1px solid #cbd5e1; color: #475569; }
            .saved { background: #ecfdf5; color: #065f46; padding: 10px 14px; border-radius: 8px; font-size: 14px; }
            </style>
            </head>
            <body>
            <header><h1>Couleurs du thème</h1></header>
            <main>
            {$notice}
            <form method="post" action="{$url}">
            {$rows}<div class="actions">
              <button type="submit" class="keep">Enregistrer</button>
              <button type="submit" name="reset" value="1" class="back">Rétablir les couleurs d'origine</button>
            </div>
            </form>
            </main>
            <script>
            for (const input of document.querySelectorAll('input[type=color]')) {
              input.addEventListener('input', () => {
                const shown = input.parentElement.querySelector('code');
                if (shown) { shown.textContent = input.value; }
              });
            }
            </script>
            </body>
            </html>
            HTML;
    }

    private static function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
