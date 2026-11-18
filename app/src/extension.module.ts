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
:root { --ct-rail: ${rail.width}px; --ct-rail-open: ${rail.open}px; }

/* The bar leaves the flow and stands against the left edge. !important all
   the way: SuiteCRM's own rules are more specific than ours. */
nav.navbar {
  position: fixed !important; top: 0 !important; left: 0 !important; bottom: 0 !important;
  width: var(--ct-rail) !important; height: 100vh !important;
  flex-direction: column !important; align-items: stretch !important;
  justify-content: flex-start !important; flex-wrap: nowrap !important;
  padding: 0 !important; overflow: visible !important;
  background: var(--st-rail, #fff) !important;
  border-right: 1px solid rgba(15, 23, 42, .08);
  z-index: 1040; transition: width .15s ease;
}
nav.navbar:hover { width: var(--ct-rail-open) !important; box-shadow: 0 0 24px rgba(15, 23, 42, .12); }

/* Every module is in the rail, so the list is longer than the screen and the
   rail scrolls. A box that scrolls clips what sticks out of it, which is why
   the submenus are taken out of the flow below. */
nav.navbar { overflow-y: auto !important; overflow-x: hidden !important; scrollbar-width: thin; }

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

/* The submenu of a module opens beside the rail. Fixed rather than absolute:
   the rail scrolls, and an absolute submenu would be cut off at its edge. The
   code sets top and left as the pointer arrives. */
nav.navbar .dropdown-menu.submenu {
  position: fixed !important; left: var(--ct-rail) !important; margin: 0 !important;
  border: 1px solid rgba(15, 23, 42, .1); border-radius: 8px;
  box-shadow: 0 8px 24px rgba(15, 23, 42, .12);
}

/* SuiteCRM folds the modules that do not fit its horizontal bar into an
   "Options" menu. A column has room for all of them, so the fold is undone:
   the menu becomes part of the rail and its own entry disappears. */
nav.navbar ul.nav-collapsed-items > li.top-nav > a.dropdown-toggle { display: none !important; }
nav.navbar .dropdown-menu.more-menu {
  position: static !important; display: block !important; float: none !important;
  width: 100% !important; min-width: 0 !important; margin: 0 !important; padding: 0 !important;
  border: 0 !important; box-shadow: none !important; background: none !important;
  transform: none !important;
}
nav.navbar .dropdown-menu.more-menu .nav-item { width: 100% !important; }

/* What the rail has no room for. */
nav.navbar scrm-search-bar, nav.navbar .navbar-toggler { display: none !important; }

/* The content moves aside by the closed width: the rail opens over it, so
   nothing reflows when it does. Padding on app-root rather than a margin on
   each child, because SuiteCRM adds children as it goes. */
app-root { display: block; padding-left: var(--ct-rail); }
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
 * Places a module's submenu beside its entry. The rail scrolls, so the
 * submenu is fixed rather than absolute, and nothing but the code knows how
 * far down the entry has ended up.
 */
const placeSubmenu = (entry: HTMLElement): void => {
    const submenu = entry.parentElement?.querySelector<HTMLElement>('.dropdown-menu.submenu');
    if (!submenu) {
        return;
    }
    const box = entry.getBoundingClientRect();
    const room = window.innerHeight - box.top;
    submenu.style.top = `${Math.max(0, box.top - Math.max(0, submenu.offsetHeight - room))}px`;
};

/**
 * Waits for SuiteCRM to have drawn its menu, and says whether it did.
 *
 * Two reasons to wait. SuiteCRM measures the bar to decide how many modules
 * fit, and it measures once: a rail of 56 px applied first means no module
 * fits, and the menu stays empty for good. And the login screen has no menu
 * at all, so it keeps SuiteCRM's own layout rather than an empty rail.
 */
const menuDrawn = async (): Promise<boolean> => {
    for (let waited = 0; waited < 20000; waited += 200) {
        if (document.querySelector('nav.navbar li.top-nav a.top-nav-link') !== null) {
            return true;
        }
        await new Promise(resolve => window.setTimeout(resolve, 200));
    }

    return false;
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
    // An entry freed from the overflow menu shares its li with every other
    // one: only its own address says which module it is.
    const alone = link.closest('.dropdown-menu.more-menu') !== null;
    const entry = alone ? null : (link.closest('li.top-nav, li.nav-item') ?? link.parentElement);
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
        // The colours are set at once, so the login screen is themed too.
        // The rail waits for the menu: see menuDrawn.
        if (!await menuDrawn()) {
            return;
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
            'nav.navbar li.top-nav a.top-nav-link,'
            + ' nav.navbar a.home-nav-link,'
            + ' nav.navbar .dropdown-menu.more-menu a.nav-link'
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
            link.addEventListener('mouseenter', () => placeSubmenu(link));
        }
    }
}
