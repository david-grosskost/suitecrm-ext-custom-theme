import {NgModule} from '@angular/core';
import {CommonModule} from '@angular/common';
import {provideHttpClient, withInterceptorsFromDi} from '@angular/common/http';

// custom-theme: loaded by SuiteCRM 8 when the interface starts (module
// federation, declared by config/extension.php). It changes the frame around
// SuiteCRM's screens, never the screens themselves:
// - the horizontal menu bar becomes a vertical rail of icons on the left,
//   which opens on hover and lies over the content;
// - each module gets the icon SuiteCRM already ships
//   (themes/suite8/images/<Module>.svg), tinted with its own accent;
// - the colours chosen in Administration are written as CSS variables, which
//   is all SuiteCRM 8 needs to be re-tinted.
//
// Everything comes from one legacy entry point, open without login so the
// login screen is themed too. It serves only colours: nothing of the data,
// nothing of this instance.

const THEME_URL = 'legacy/index.php?entryPoint=customTheme';
const ICON_URL = (module: string): string => `legacy/themes/suite8/images/${module}.svg`;

// The grey SuiteCRM paints its module icons with. Replaced by the module's
// accent; the white inner detail is kept, which is what gives the icons their
// relief once they are coloured.
const ICON_GREY = /#778591/gi;

const STYLE_ID = 'custom-theme-style';
const MARK = 'data-custom-theme';

interface Rail {
    width: number;
    open: number;
}

interface Payload {
    variables: Record<string, string>;
    accents: Record<string, string>;
    rail: Rail;
}

/** The stylesheet of the theme, created once and rewritten when needed. */
const styleSheet = (): HTMLStyleElement => {
    let style = document.getElementById(STYLE_ID) as HTMLStyleElement | null;
    if (!style) {
        style = document.createElement('style');
        style.id = STYLE_ID;
        document.head.appendChild(style);
    }

    return style;
};

/**
 * The layout: the navbar leaves the flow and becomes a rail against the left
 * edge, the content moves aside by its width. Written against SuiteCRM's
 * component names (scrm-…), which are stable, rather than against generated
 * class names, which are not.
 */
const layout = (rail: Rail): string => `
:root { --ct-rail: ${rail.width}px; --ct-rail-open: ${rail.open}px; }

nav.navbar {
  position: fixed; top: 0; left: 0; bottom: 0;
  width: var(--ct-rail); height: 100vh;
  flex-direction: column; align-items: stretch; justify-content: flex-start;
  flex-wrap: nowrap; padding: 0; gap: 0;
  background: var(--st-rail, #fff);
  border-right: 1px solid rgba(15, 23, 42, .08);
  z-index: 1040; overflow: visible;
  transition: width .15s ease;
}
nav.navbar:hover { width: var(--ct-rail-open); box-shadow: 0 0 24px rgba(15, 23, 42, .12); }

nav.navbar scrm-menu-items-list ul {
  flex-direction: column; align-items: stretch; width: 100%; margin: 0; padding: 0;
}
nav.navbar scrm-menu-items-list ul > li { width: 100%; }
nav.navbar scrm-menu-items-list ul > li > * { width: 100%; }

/* One row: the tinted icon, then the label, hidden while the rail is closed. */
nav.navbar [${MARK}-item] {
  display: flex; align-items: center; gap: 12px;
  height: var(--ct-rail); padding: 0 ${(56 - 30) / 2}px;
  white-space: nowrap; overflow: hidden;
}
nav.navbar [${MARK}-icon] { flex: 0 0 30px; width: 30px; height: 30px; display: block; }
nav.navbar [${MARK}-icon] svg { width: 30px; height: 30px; display: block; }
nav.navbar [${MARK}-letter] {
  flex: 0 0 30px; width: 30px; height: 30px; border-radius: 8px;
  display: flex; align-items: center; justify-content: center;
  font-weight: 700; font-size: 13px; color: #fff;
}
nav.navbar [${MARK}-label] { opacity: 0; transition: opacity .12s ease; }
nav.navbar:hover [${MARK}-label] { opacity: 1; }

/* The accent, as on the reference: a strip on the edge of every row, filled
   in when the module is the one being looked at. */
nav.navbar [${MARK}-item]::before {
  content: ''; position: absolute; left: 0; width: 3px; height: var(--ct-rail);
  background: var(--ct-accent, transparent);
}
nav.navbar [${MARK}-item] { position: relative; }

/* What the rail has no room for. The search keeps its own place in the
   screens of SuiteCRM. */
nav.navbar scrm-search-bar, nav.navbar .navbar-toggler { display: none; }

/* The content, moved aside by the closed width: the rail opens over it, so
   nothing reflows when it does. */
body > app-root > *:not(scrm-navbar-ui) { margin-left: var(--ct-rail); }
body { background: var(--st-surface, #f1f5f9); }
`;

/** The icons already fetched, so a menu of fifteen modules asks once each. */
const icons = new Map<string, Promise<string | null>>();

const iconFor = (module: string): Promise<string | null> => {
    const known = icons.get(module);
    if (known) {
        return known;
    }
    const asked = fetch(ICON_URL(module))
        .then(answer => (answer.ok ? answer.text() : null))
        .then(text => (text && text.includes('<svg') ? text : null))
        .catch(() => null);
    icons.set(module, asked);

    return asked;
};

/** The module a menu entry points at, as SuiteCRM's own list spells it. */
const moduleOf = (item: Element, accents: Record<string, string>): string | null => {
    const link = item.querySelector('a[href]') as HTMLAnchorElement | null;
    const href = link?.getAttribute('href') ?? '';
    const asked = href.split(/[#/?]/).filter(Boolean).pop() ?? '';
    if (!asked) {
        return null;
    }

    return Object.keys(accents).find(name => name.toLowerCase() === asked.toLowerCase()) ?? null;
};

/** A tile with the first letter, for a module SuiteCRM ships no icon for. */
const letterTile = (label: string, accent: string): HTMLElement => {
    const tile = document.createElement('span');
    tile.setAttribute(`${MARK}-letter`, '');
    tile.style.background = accent;
    tile.textContent = (label.trim()[0] ?? '?').toUpperCase();

    return tile;
};

@NgModule({
    declarations: [],
    imports: [CommonModule],
    providers: [provideHttpClient(withInterceptorsFromDi())]
})
export class ExtensionModule {
    // SuiteCRM 8.10.2 creates the module (createNgModule) but never calls
    // init(): the work starts here, when it is loaded at start.
    constructor() {
        this.apply().catch(error => console.warn('custom-theme : non appliqué', error));
    }

    init(): void {
    }

    private async apply(): Promise<void> {
        const answer = await fetch(THEME_URL, {credentials: 'same-origin'});
        if (!answer.ok) {
            throw new Error(`entryPoint customTheme: ${answer.status}`);
        }
        const theme = await answer.json() as Payload;

        for (const [name, value] of Object.entries(theme.variables ?? {})) {
            document.documentElement.style.setProperty(name, value);
        }
        styleSheet().textContent = layout(theme.rail ?? {width: 56, open: 240});

        // The navbar is drawn by Angular after this module is loaded, and
        // redrawn whenever the menu changes: watching is the only way to
        // decorate it without touching SuiteCRM's components.
        const decorate = (): void => {
            void this.decorate(theme.accents ?? {});
        };
        decorate();
        new MutationObserver(decorate).observe(document.body, {childList: true, subtree: true});
    }

    private async decorate(accents: Record<string, string>): Promise<void> {
        const items = document.querySelectorAll('nav.navbar scrm-menu-items-list li');
        for (const item of Array.from(items)) {
            const link = item.querySelector('a') as HTMLElement | null;
            if (!link || link.hasAttribute(`${MARK}-item`)) {
                continue;
            }
            const label = (link.textContent ?? '').trim();
            if (!label) {
                continue;
            }

            const module = moduleOf(item, accents);
            const accent = module ? accents[module] : '#94a3b8';
            link.setAttribute(`${MARK}-item`, '');
            link.style.setProperty('--ct-accent', accent);

            const text = document.createElement('span');
            text.setAttribute(`${MARK}-label`, '');
            text.textContent = label;

            const svg = module ? await iconFor(module) : null;
            let mark: HTMLElement;
            if (svg) {
                mark = document.createElement('span');
                mark.setAttribute(`${MARK}-icon`, '');
                mark.innerHTML = svg.replace(ICON_GREY, accent);
            } else {
                mark = letterTile(label, accent);
            }

            link.textContent = '';
            link.append(mark, text);
        }
    }
}
