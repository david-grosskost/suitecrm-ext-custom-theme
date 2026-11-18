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
/* The rail opens on a click, never on the pointer passing by: a menu that
   unfolds by itself gets in the way of the screen behind it. */

/* Every module is in the rail, so the list is longer than the screen and the
   rail scrolls. A box that scrolls clips what sticks out of it, which is why
   the submenus are taken out of the flow below. */
nav.navbar { overflow-y: auto !important; overflow-x: hidden !important; scrollbar-width: thin; }

/* Pinned open: the rail keeps its full width and the content stands beside
   it rather than under it, so nothing is hidden. */
html[${MARK}-pinned] nav.navbar { width: var(--ct-rail-open) !important; box-shadow: 0 0 24px rgba(15, 23, 42, .12); }
html[${MARK}-pinned] app-root { padding-left: var(--ct-rail-open); }
html[${MARK}-pinned] nav.navbar [${MARK}-label] { opacity: 1; }

[${MARK}-pin] {
  flex: 0 0 auto; height: var(--ct-rail); width: 100%;
  display: flex; align-items: center; gap: 12px; padding: 0 13px;
  border: 0; background: none; cursor: pointer; color: #64748b;
  border-bottom: 1px solid rgba(15, 23, 42, .06);
}
[${MARK}-pin]:hover { background: rgba(15, 23, 42, .04); }
[${MARK}-pin] span {
  flex: 0 0 30px; display: flex; flex-direction: column; align-items: center;
  justify-content: center; gap: 4px; height: 30px;
}
[${MARK}-pin] i {
  display: block; width: 18px; height: 2px; border-radius: 2px; background: currentColor;
  transition: transform .15s ease, opacity .15s ease;
}
/* Open, the three lines fold into a cross. */
html[${MARK}-pinned] [${MARK}-pin] i:first-child { transform: translateY(6px) rotate(45deg); }
html[${MARK}-pinned] [${MARK}-pin] i:nth-child(2) { opacity: 0; }
html[${MARK}-pinned] [${MARK}-pin] i:last-child { transform: translateY(-6px) rotate(-45deg); }

nav.navbar .navbar-collapse { display: block !important; width: 100% !important; flex-basis: auto !important; }
nav.navbar ul.navbar-nav { flex-direction: column !important; width: 100% !important; margin: 0 !important; padding: 0 !important; }
nav.navbar li.top-nav, nav.navbar li.nav-item { width: 100% !important; position: relative !important; }
nav.navbar .menu-item-wrapper { width: 100% !important; }

/* One row per module: the tinted icon, then the label, which only shows
   once the rail is open. */
nav.navbar [${MARK}-item] {
  display: flex !important; align-items: center; gap: 12px;
  height: var(--ct-rail); padding: 0 13px; margin: 0 !important;
  white-space: nowrap; overflow: hidden; position: relative;
}
/* Bootstrap draws a caret with a border on ::after; the three dots say it
   better, and emptying the link does not remove a pseudo-element. */
nav.navbar [${MARK}-item]::after { display: none !important; }

nav.navbar [${MARK}-item]::before {
  content: ''; position: absolute; left: 0; top: 0; width: 3px; height: 100%;
  background: var(--ct-accent, transparent);
}
nav.navbar [${MARK}-icon] { flex: 0 0 30px; width: 30px; height: 30px; display: block; }
nav.navbar [${MARK}-icon] svg { width: 30px; height: 30px; display: block; }

/* Home keeps the icon SuiteCRM draws for it, which is white and invisible on
   a light rail: it takes the entry's colour. */
nav.navbar a.home-nav-link scrm-image svg,
nav.navbar a.home-nav-link scrm-image svg * { fill: var(--ct-accent, #64748b) !important; }
nav.navbar a.home-nav-link scrm-image { flex: 0 0 30px; display: flex; justify-content: center; }
nav.navbar [${MARK}-letter] {
  flex: 0 0 30px; width: 30px; height: 30px; border-radius: 8px;
  display: flex; align-items: center; justify-content: center;
  font-weight: 700; font-size: 13px; color: #fff;
}
nav.navbar [${MARK}-item], nav.navbar [${MARK}-item]:hover {
  color: #334155 !important; text-decoration: none !important;
}
/* The entry stays where it is under the pointer: SuiteCRM's own hover adds a
   margin at the top, which makes the row jump away. It grows instead. */
nav.navbar [${MARK}-item]:hover { background: rgba(15, 23, 42, .06); }
nav.navbar [${MARK}-item]:hover [${MARK}-icon],
nav.navbar [${MARK}-item]:hover [${MARK}-letter] { transform: scale(1.14); }
nav.navbar [${MARK}-item]:hover [${MARK}-label] { font-weight: 600; }
nav.navbar [${MARK}-icon], nav.navbar [${MARK}-letter] { transition: transform .12s ease; }

/* Three dots on the right say an entry has a submenu, in place of the caret
   wiped when the entry is dressed. They show once the rail is open. */
nav.navbar [${MARK}-more] {
  margin-left: auto; display: flex; gap: 3px; opacity: 0; transition: opacity .12s ease;
}
html[${MARK}-pinned] nav.navbar [${MARK}-more] { opacity: .45; }
nav.navbar [${MARK}-item]:hover [${MARK}-more] { opacity: .9; }
nav.navbar [${MARK}-more] i { width: 3px; height: 3px; border-radius: 50%; background: currentColor; }
nav.navbar [${MARK}-label] { font-size: 13px; font-weight: 500; opacity: 0; transition: opacity .12s ease; }
html[${MARK}-pinned] nav.navbar [${MARK}-label] { opacity: 1; }

/* The submenu of a module opens beside the rail. Fixed rather than absolute:
   the rail scrolls, and an absolute submenu would be cut off at its edge. The
   code sets top and left as the pointer arrives. */
nav.navbar .dropdown-menu.submenu {
  display: none !important;
  position: fixed !important; left: var(--ct-rail) !important; margin: 0 !important;
  border: 1px solid rgba(15, 23, 42, .1); border-radius: 8px;
  box-shadow: 0 8px 24px rgba(15, 23, 42, .12);
}
html[${MARK}-pinned] nav.navbar .dropdown-menu.submenu { left: var(--ct-rail-open) !important; }
/* Opened by the three dots alone, never by the row: clicking a module should
   go to the module, not unfold a menu under the pointer. */
nav.navbar .dropdown-menu.submenu[${MARK}-open] { display: block !important; }

/* SuiteCRM keeps a folded copy of the menu for the modules that do not fit
   its horizontal bar. The limit is lifted (config/services), so every module
   is a full entry with its submenu and that copy only repeats them. */
nav.navbar scrm-menu-items-list { display: none !important; }

/* What the rail has no room for. */
/* The bar across the top carries what is not a module and what the rail is
   too narrow for: the search, the quick actions, the user's menu. */
[${MARK}-bar] {
  position: fixed; top: 0; left: 0; right: 0; height: var(--ct-bar);
  display: flex; align-items: center; gap: 8px;
  /* Indented by the closed rail, so what the bar carries lines up with the
     content rather than sitting over the column of modules. The indent does
     not follow the rail when it opens: a search field that slides away as
     the menu unfolds is worse than one that stays put. */
  padding: 0 14px 0 calc(var(--ct-rail) + 14px);
  background: linear-gradient(to right top, var(--st-header-from, #0369a0), var(--st-header-to, #3abff8));
  color: #fff; z-index: 1050;
}
[${MARK}-bar] [${MARK}-gap] { flex: 1 1 auto; }

/* The controls keep their behaviour; only their colours change, so they can
   be read on the gradient. */
[${MARK}-bar] a, [${MARK}-bar] button, [${MARK}-bar] .action-link { color: #fff !important; }
[${MARK}-bar] svg, [${MARK}-bar] svg * { fill: currentColor; }
[${MARK}-bar] .dropdown-menu, [${MARK}-bar] .dropdown-menu a { color: #1d283a !important; }
[${MARK}-bar] .dropdown-menu svg, [${MARK}-bar] .dropdown-menu svg * { fill: #64748b; }
/* The search comes from a bar of its own, two lines tall and carrying
   SuiteCRM's grey. Here it is a single pill, the height of the bandeau's
   row: field and button side by side, nothing wrapping. */
[${MARK}-bar] scrm-search-bar { display: flex !important; align-items: center; height: 36px; }
[${MARK}-bar] form.search-bar { display: flex; align-items: center; height: 36px; margin: 0; }
[${MARK}-bar] .input-group {
  flex-wrap: nowrap !important; width: auto !important; height: 36px !important;
  background: rgba(255, 255, 255, .16) !important; border-radius: 8px; overflow: hidden;
}
[${MARK}-bar] .search-bar-term {
  background: none !important; border: 0 !important; box-shadow: none !important;
  color: #fff; width: 220px !important; min-width: 0 !important; height: 36px !important;
  padding: 0 12px;
}
[${MARK}-bar] .search-bar-term::placeholder { color: rgba(255, 255, 255, .75); }
[${MARK}-bar] .input-group-append { height: 36px; }
[${MARK}-bar] .search-button {
  height: 36px !important; border: 0 !important; background: none !important;
  display: flex; align-items: center; padding: 0 10px;
}

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
 * What the bar across the top carries, in the order it shows them. These are
 * SuiteCRM's own elements: they are moved, never copied, so they keep their
 * behaviour and their bindings.
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
 * when any of them has strayed, all are set down again, in order.
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
 * Opens a module's submenu beside its entry, and only from the three dots:
 * clicking a module should go to the module, not unfold a menu under the
 * pointer. The rail scrolls, so the submenu is fixed rather than absolute,
 * and only the code knows how far down the entry has ended up.
 *
 * One listener on the document rather than one per entry: Angular replaces
 * the entries as the menu changes, and a listener set on an entry goes with
 * it.
 */
const followThePointer = (rail: HTMLElement): void => {
    if (rail.hasAttribute(`${MARK}-follows`)) {
        return;
    }
    rail.setAttribute(`${MARK}-follows`, '');

    let closing = 0;
    const closeAll = (): void => {
        for (const open of Array.from(document.querySelectorAll(`[${MARK}-open]`))) {
            open.removeAttribute(`${MARK}-open`);
        }
    };

    document.addEventListener('mouseover', event => {
        const over = event.target;
        if (!(over instanceof Element)) {
            return;
        }

        // Walking onto the submenu keeps it open: the pointer has to cross
        // the gap between the dots and it.
        if (over.closest('.dropdown-menu.submenu')) {
            window.clearTimeout(closing);

            return;
        }

        const dots = over.closest(`[${MARK}-more]`);
        if (!dots) {
            closing = window.setTimeout(closeAll, 250);

            return;
        }

        window.clearTimeout(closing);
        const entry = dots.closest('li.top-nav');
        const link = entry?.querySelector<HTMLElement>(`[${MARK}-item]`);
        const submenu = entry?.querySelector<HTMLElement>('.dropdown-menu.submenu');
        if (!link || !submenu) {
            return;
        }

        closeAll();
        submenu.setAttribute(`${MARK}-open`, '');
        const box = link.getBoundingClientRect();
        // Against the bottom of the window the submenu rises rather than
        // running off the screen.
        const top = Math.min(box.top, Math.max(0, window.innerHeight - submenu.offsetHeight - 8));
        submenu.style.top = `${top}px`;
    });
};

/** The three dots that say an entry opens a submenu. */
const moreDots = (): HTMLElement => {
    const dots = document.createElement('span');
    dots.setAttribute(`${MARK}-more`, '');
    dots.setAttribute('aria-hidden', 'true');
    for (let drawn = 0; drawn < 3; drawn++) {
        dots.append(document.createElement('i'));
    }

    return dots;
};

/**
 * Waits for SuiteCRM to have drawn its menu, and says whether it did.
 *
 * The login screen has no menu at all, so it keeps SuiteCRM's own layout
 * rather than an empty rail.
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
 * Whether the rail is held open. Kept per browser, because it is a habit of
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

/** The button at the top of the rail: three lines, a cross once open. */
const pinButton = (): HTMLElement => {
    const button = document.createElement('button');
    button.setAttribute(`${MARK}-pin`, '');
    button.type = 'button';

    const lines = document.createElement('span');
    for (let drawn = 0; drawn < 3; drawn++) {
        lines.append(document.createElement('i'));
    }
    button.append(lines);

    const says = (pinned: boolean): void => {
        const words = pinned ? 'Replier le menu' : 'Ouvrir le menu';
        button.title = words;
        button.setAttribute('aria-label', words);
    };
    says(document.documentElement.hasAttribute(`${MARK}-pinned`));

    button.addEventListener('click', () => {
        const pinned = !document.documentElement.hasAttribute(`${MARK}-pinned`);
        applyPin(pinned);
        writePin(pinned);
        says(pinned);
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
        fillBar();

        const bar = document.querySelector<HTMLElement>('nav.navbar');
        if (bar) {
            followThePointer(bar);
        }
        if (bar && !bar.querySelector(`[${MARK}-pin]`)) {
            bar.prepend(pinButton());
        }

        const entries = document.querySelectorAll(
            'nav.navbar li.top-nav a.top-nav-link, nav.navbar a.home-nav-link'
        );
        for (const link of Array.from(entries) as HTMLElement[]) {
            if (link.hasAttribute(`${MARK}-item`)) {
                continue;
            }
            // Home carries an icon and no words at all, so it needs its
            // name given to it rather than read from the page.
            const home = link.matches('a.home-nav-link');
            const label = home ? 'Accueil' : (link.textContent ?? '').trim();
            if (!label) {
                continue;
            }

            const module = home ? 'Home' : moduleOf(link, accents);
            const accent = (module !== null && accents[module] !== undefined) ? accents[module] : '#64748b';
            link.setAttribute(`${MARK}-item`, '');
            link.style.setProperty('--ct-accent', accent);

            const text = document.createElement('span');
            text.setAttribute(`${MARK}-label`, '');
            text.textContent = label;

            // Home keeps the icon SuiteCRM already draws inside the link;
            // the stylesheet gives it the entry's colour.
            const svg = (module !== null && !home) ? await iconFor(module) : null;
            let mark: HTMLElement = text;
            if (home) {
                mark = text;
            } else if (svg) {
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

            if (home) {
                link.append(text);
            } else {
                link.textContent = '';
                link.append(mark, text);
            }
            if (link.closest('li.top-nav')?.querySelector('.dropdown-menu.submenu')) {
                link.append(moreDots());
            }
        }
    }
}
