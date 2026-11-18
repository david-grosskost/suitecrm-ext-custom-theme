import {NgModule} from '@angular/core';
import {CommonModule} from '@angular/common';

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
:root { --ct-rail: ${rail.width}px; --ct-rail-open: ${rail.open}px; --ct-bar: ${rail.width}px; }

/* The bar leaves the flow and stands against the left edge. !important all
   the way: SuiteCRM's own rules are more specific than ours. */
nav.navbar {
  position: fixed !important; top: var(--ct-bar) !important; left: 0 !important; bottom: 0 !important;
  width: var(--ct-rail) !important; height: calc(100vh - var(--ct-bar)) !important;
  flex-direction: column !important; align-items: stretch !important;
  justify-content: flex-start !important; flex-wrap: nowrap !important;
  padding: 0 !important; overflow: visible !important;
  background: var(--st-rail, #fff) !important;
  border-right: 1px solid rgba(15, 23, 42, .08);
  z-index: 1040; transition: width .15s ease;
}
nav.navbar:hover { width: var(--ct-rail-open) !important; box-shadow: 0 0 24px rgba(15, 23, 42, .12); }

/* Pinned open: the rail keeps its full width and the content stands beside
   it rather than under it, so nothing is hidden. */
html[${MARK}-pinned] nav.navbar { width: var(--ct-rail-open) !important; box-shadow: none; }
html[${MARK}-pinned] app-root { padding-left: var(--ct-rail-open); }
html[${MARK}-pinned] nav.navbar [${MARK}-label] { opacity: 1; }

[${MARK}-pin] {
  flex: 0 0 auto; height: var(--ct-rail); width: 100%;
  display: flex; align-items: center; gap: 12px; padding: 0 13px;
  border: 0; background: none; cursor: pointer; color: #64748b;
  border-bottom: 1px solid rgba(15, 23, 42, .06);
}
[${MARK}-pin]:hover { background: rgba(15, 23, 42, .04); }
[${MARK}-pin] span:first-child {
  flex: 0 0 30px; display: flex; align-items: center; justify-content: center;
  font-size: 18px; line-height: 1;
}
[${MARK}-pin] span:last-child { font-size: 13px; opacity: 0; white-space: nowrap; transition: opacity .12s ease; }
nav.navbar:hover [${MARK}-pin] span:last-child,
html[${MARK}-pinned] [${MARK}-pin] span:last-child { opacity: 1; }

nav.navbar .navbar-collapse { display: block !important; width: 100% !important; flex-basis: auto !important; }
nav.navbar ul.navbar-nav { flex-direction: column !important; width: 100% !important; margin: 0 !important; padding: 0 !important; }
nav.navbar li.top-nav, nav.navbar li.nav-item { width: 100% !important; position: relative !important; }
nav.navbar .menu-item-wrapper { width: 100% !important; }

/* One row per module: the tinted icon, then the label, which only shows
   once the rail is open. */
nav.navbar [${MARK}-item] {
  display: flex !important; align-items: center; gap: 12px;
  height: var(--ct-rail); padding: 0 13px;
  white-space: nowrap; overflow: hidden; position: relative;
}
nav.navbar [${MARK}-item]::before {
  content: ''; position: absolute; left: 0; top: 0; width: 3px; height: 100%;
  background: var(--ct-accent, transparent);
}
nav.navbar [${MARK}-icon] { flex: 0 0 30px; width: 30px; height: 30px; display: block; }
nav.navbar [${MARK}-icon] svg { width: 30px; height: 30px; display: block; }
nav.navbar [${MARK}-letter] {
  flex: 0 0 30px; width: 30px; height: 30px; border-radius: 8px;
  display: flex; align-items: center; justify-content: center;
  font-weight: 700; font-size: 13px; color: #fff;
}
nav.navbar [${MARK}-item], nav.navbar [${MARK}-item]:hover {
  color: #334155 !important; text-decoration: none !important;
}
nav.navbar [${MARK}-item]:hover { background: rgba(15, 23, 42, .04); }
nav.navbar [${MARK}-label] { font-size: 13px; font-weight: 500; opacity: 0; transition: opacity .12s ease; }
nav.navbar:hover [${MARK}-label] { opacity: 1; }

/* The submenu of a module opens to the right of the rail, not under it. */
nav.navbar .dropdown-menu.submenu {
  position: absolute !important; left: 100% !important; top: 0 !important;
  margin: 0 !important; border: 1px solid rgba(15, 23, 42, .1); border-radius: 8px;
  box-shadow: 0 8px 24px rgba(15, 23, 42, .12);
}

/* The bar across the top. It carries what the rail is too narrow for and
   what is not a module: the search, the quick actions, the user's menu. */
[${MARK}-bar] {
  position: fixed; top: 0; left: 0; right: 0; height: var(--ct-bar);
  display: flex; align-items: center; gap: 8px; padding: 0 16px;
  background: linear-gradient(to right top, var(--st-header-from, #0369a0), var(--st-header-to, #3abff8));
  color: #fff; z-index: 1050;
}
[${MARK}-bar] [${MARK}-brand] { font-size: 15px; font-weight: 600; letter-spacing: .01em; margin-right: 8px; }
[${MARK}-bar] [${MARK}-gap] { flex: 1 1 auto; }

/* The moved controls keep their behaviour; only their colours change, so
   they can be read on the gradient. */
[${MARK}-bar] a, [${MARK}-bar] button, [${MARK}-bar] .action-link { color: #fff !important; }
[${MARK}-bar] svg, [${MARK}-bar] svg * { fill: currentColor; }
[${MARK}-bar] .dropdown-menu { color: #1d283a; }
[${MARK}-bar] .dropdown-menu a { color: #1d283a !important; }
[${MARK}-bar] scrm-search-bar { display: block !important; }
[${MARK}-bar] .search-bar-term {
  background: rgba(255, 255, 255, .16); border: 0; border-radius: 8px;
  color: #fff; min-width: 220px;
}
[${MARK}-bar] .search-bar-term::placeholder { color: rgba(255, 255, 255, .75); }

/* The rail has no room for these: they live in the bar now. */
nav.navbar .navbar-toggler, nav.navbar .search-mobile-view { display: none !important; }

/* The content moves aside by the closed width: the rail opens over it, so
   nothing reflows when it does. Padding on app-root rather than a margin on
   each child, because SuiteCRM adds children as it goes. */
app-root { display: block; padding-left: var(--ct-rail); padding-top: var(--ct-bar); }
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

/**
 * What the bar carries, in the order it shows them. These are SuiteCRM's own
 * elements: they are moved, never copied, so they keep their behaviour and
 * their bindings.
 */
const BAR_PARTS = [
    'scrm-search-bar',
    `[${MARK}-gap]`,
    'div.action-group.navbar-action-group',
    'div.global-links',
];

/** The bar across the top, created once. */
const topBar = (): HTMLElement => {
    const known = document.querySelector<HTMLElement>(`[${MARK}-bar]`);
    if (known) {
        return known;
    }

    const bar = document.createElement('header');
    bar.setAttribute(`${MARK}-bar`, '');
    const gap = document.createElement('span');
    gap.setAttribute(`${MARK}-gap`, '');
    bar.append(gap);
    document.body.prepend(bar);

    return bar;
};

/**
 * Puts the controls that are not modules into the bar. Angular redraws the
 * navbar as the menu changes, so a moved control can come back where it was:
 * when any of them is out of place, all are set down again, in order.
 */
const fillBar = (): void => {
    const bar = topBar();
    const strayed = BAR_PARTS.some(selector => {
        const part = document.querySelector(selector);

        return part !== null && part.parentElement !== bar;
    });
    if (!strayed) {
        return;
    }

    for (const selector of BAR_PARTS) {
        const part = document.querySelector(selector);
        if (part) {
            bar.append(part);
        }
    }
};

/**
 * Whether the rail is pinned open. Kept per browser, because it is a habit of
 * the person sitting there, not a setting of the instance.
 */
const PIN_KEY = 'custom-theme.pinned';

const readPin = (): boolean => {
    try {
        return window.localStorage.getItem(PIN_KEY) === 'yes';
    } catch {
        return false;
    }
};

const writePin = (pinned: boolean): void => {
    try {
        window.localStorage.setItem(PIN_KEY, pinned ? 'yes' : 'no');
    } catch {
        // A browser that refuses storage still gets a working rail, it just
        // forgets the choice between visits.
    }
};

const applyPin = (pinned: boolean): void => {
    if (pinned) {
        document.documentElement.setAttribute(`${MARK}-pinned`, '');
    } else {
        document.documentElement.removeAttribute(`${MARK}-pinned`);
    }
};

/** The button at the top of the rail that holds it open. */
const pinButton = (): HTMLElement => {
    const button = document.createElement('button');
    button.setAttribute(`${MARK}-pin`, '');
    button.type = 'button';
    button.title = 'Garder le menu ouvert';

    const glyph = document.createElement('span');
    glyph.textContent = '\u00AB';
    const label = document.createElement('span');
    label.textContent = 'Garder ouvert';
    button.append(glyph, label);

    button.addEventListener('click', () => {
        const pinned = !document.documentElement.hasAttribute(`${MARK}-pinned`);
        applyPin(pinned);
        writePin(pinned);
        glyph.textContent = pinned ? '\u00BB' : '\u00AB';
        label.textContent = pinned ? 'Replier' : 'Garder ouvert';
    });

    return button;
};

/**
 * The module a menu entry points at, spelled as SuiteCRM spells it. Read from
 * the links of its submenu, which carry return_module=Accounts: the entry's
 * own address only gives the lowercase route.
 */
const moduleOf = (link: Element, accents: Record<string, string>): string | null => {
    const entry = link.closest('li.top-nav, li.nav-item') ?? link.parentElement;
    const hrefs = entry ? Array.from(entry.querySelectorAll('a[href]')).map(a => a.getAttribute('href') ?? '') : [];
    for (const href of hrefs) {
        const named = /return_module=([A-Za-z0-9_]+)/.exec(href);
        if (named) {
            const match = Object.keys(accents).find(name => name.toLowerCase() === named[1].toLowerCase());
            if (match) {
                return match;
            }
        }
    }

    const own = (link.getAttribute('href') ?? '').split(/[#/?]/).filter(Boolean).pop() ?? '';

    return own ? Object.keys(accents).find(name => name.toLowerCase() === own.toLowerCase()) ?? null : null;
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
        applyPin(readPin());

        // The navbar is drawn by Angular after this module is loaded, and
        // redrawn whenever the menu changes: watching is the only way to
        // decorate it without touching SuiteCRM's components.
        //
        // Watching costs care. Angular's first render fires mutations by the
        // thousand, so the work is held back until they stop, and one pass
        // never starts while another is still running: without that, hundreds
        // of passes overlap inside Angular's own rendering.
        let waiting = false;
        let running = false;
        const pass = async (): Promise<void> => {
            if (running) {
                return;
            }
            running = true;
            try {
                await this.decorate(theme.accents ?? {});
            } finally {
                running = false;
            }
        };
        const later = (): void => {
            if (waiting) {
                return;
            }
            waiting = true;
            window.setTimeout(() => {
                waiting = false;
                void pass();
            }, 120);
        };

        later();
        new MutationObserver(later).observe(document.body, {childList: true, subtree: true});
    }

    private async decorate(accents: Record<string, string>): Promise<void> {
        // The modules sit in li.top-nav, Home in the separate component next
        // to them. The link to dress is the one at the top of the entry, not
        // those of the submenu underneath.
        fillBar();

        const bar = document.querySelector('nav.navbar');
        if (bar && !bar.querySelector(`[${MARK}-pin]`)) {
            const button = pinButton();
            if (document.documentElement.hasAttribute(`${MARK}-pinned`)) {
                const spans = button.querySelectorAll('span');
                spans[0].textContent = '\u00BB';
                spans[1].textContent = 'Replier';
            }
            bar.prepend(button);
        }

        const entries = document.querySelectorAll(
            'nav.navbar li.top-nav a.top-nav-link, nav.navbar a.home-nav-link'
        );
        for (const link of Array.from(entries) as HTMLElement[]) {
            if (link.hasAttribute(`${MARK}-item`)) {
                continue;
            }
            const label = (link.textContent ?? '').trim();
            if (!label) {
                continue;
            }

            const module = moduleOf(link, accents);
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
                // The icons SuiteCRM ships carry <desc>Created with Sketch.</desc>,
                // which a screen reader would read out before the module's name.
                // The label next to it says what the entry is.
                mark.setAttribute('aria-hidden', 'true');
                for (const noise of Array.from(mark.querySelectorAll('title, desc'))) {
                    noise.remove();
                }
            } else {
                mark = letterTile(label, accent);
            }

            link.textContent = '';
            link.append(mark, text);
        }
    }
}
