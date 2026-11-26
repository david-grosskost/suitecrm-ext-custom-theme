/**
 * The dark mode, on the browser's side.
 *
 * SuiteCRM writes its colours in its stylesheets, some four hundred greys of
 * them: none is a variable. Each one is therefore handed over to a variable of
 * its own, named after its part (a background, a text, a border) and its
 * colour, with the original as fallback. Light, nothing defines those
 * variables and SuiteCRM is drawn exactly as before; dark, a rule worked out
 * here gives each one its dark shade.
 *
 * The part matters because one colour plays several: SuiteCRM's white is the
 * background of its pages and the text of its coloured buttons. The
 * background turns dark, the text on a button stays white.
 *
 * Only the greys are worked out. The colours that mean something (red for an
 * error, green for a success, the client's own) keep their value, and the
 * ones the theme already hands over to the client's palette are left to it.
 */

/** Where the choice is remembered in the browser, for the very first paint. */
const SCHEME_KEY = 'custom-theme.scheme';

export const SCHEME_URL = 'legacy/index.php?entryPoint=customThemeScheme';

/** The attribute that says, on the root of a document, which mode is shown. */
export const SCHEME_ATTRIBUTE = 'data-ct-scheme';

export type Scheme = 'light' | 'dark' | 'auto';

const SCHEMES: Scheme[] = ['light', 'dark', 'auto'];

/** The part a colour plays in a declaration. */
type Part = 'bg' | 'text' | 'border';

/** Below this saturation a colour is a grey, and the dark mode takes it. */
const GREY = 0.2;

interface Hsl {
    h: number;
    s: number;
    l: number;
}

/** What the dark greys are tinted with: the hue of the client's dark page. */
export interface Tint {
    h: number;
    s: number;
}

/* ---------------------------------------------------------- colours ---- */

/** Red, green, blue (0-255) and alpha (0-1) of a CSS colour, or null. */
export const parse = (colour: string): [number, number, number, number] | null => {
    const lowered = colour.trim().toLowerCase();
    if (lowered === 'white') {
        return [255, 255, 255, 1];
    }
    if (lowered === 'black') {
        return [0, 0, 0, 1];
    }

    const hex = /^#([0-9a-f]{3,8})$/.exec(lowered);
    if (hex !== null) {
        let digits = hex[1];
        if (digits.length === 3 || digits.length === 4) {
            digits = digits.split('').map(digit => digit + digit).join('');
        }
        if (digits.length !== 6 && digits.length !== 8) {
            return null;
        }
        const alpha = digits.length === 8 ? parseInt(digits.slice(6, 8), 16) / 255 : 1;

        return [parseInt(digits.slice(0, 2), 16), parseInt(digits.slice(2, 4), 16), parseInt(digits.slice(4, 6), 16), alpha];
    }

    const functional = /^rgba?\(([^)]*)\)$/.exec(lowered);
    if (functional !== null) {
        const parts = functional[1].split(/[\s,/]+/).filter(part => part !== '');
        if (parts.length < 3 || parts.some(part => part.endsWith('%'))) {
            return null;
        }
        const numbers = parts.map(Number);
        if (numbers.some(Number.isNaN)) {
            return null;
        }

        return [numbers[0], numbers[1], numbers[2], numbers[3] ?? 1];
    }

    return null;
};

export const toHsl = (red: number, green: number, blue: number): Hsl => {
    const [r, g, b] = [red / 255, green / 255, blue / 255];
    const max = Math.max(r, g, b);
    const min = Math.min(r, g, b);
    const l = (max + min) / 2;
    if (max === min) {
        return {h: 0, s: 0, l};
    }
    const delta = max - min;
    const s = delta / (1 - Math.abs(2 * l - 1));
    let h: number;
    if (max === r) {
        h = ((g - b) / delta + 6) % 6;
    } else if (max === g) {
        h = (b - r) / delta + 2;
    } else {
        h = (r - g) / delta + 4;
    }

    return {h: h * 60, s: Math.min(1, s), l};
};

export const hex = ({h, s, l}: Hsl): string => {
    const chroma = (1 - Math.abs(2 * l - 1)) * s;
    const sector = (h % 360) / 60;
    const second = chroma * (1 - Math.abs((sector % 2) - 1));
    const match = l - chroma / 2;
    const [r, g, b] = sector < 1 ? [chroma, second, 0]
        : sector < 2 ? [second, chroma, 0]
        : sector < 3 ? [0, chroma, second]
        : sector < 4 ? [0, second, chroma]
        : sector < 5 ? [second, 0, chroma]
        : [chroma, 0, second];

    return '#' + [r, g, b]
        .map(channel => Math.round(Math.min(1, Math.max(0, channel + match)) * 255).toString(16).padStart(2, '0'))
        .join('');
};

/** The tint of the dark greys, from the client's dark page. */
export const tintOf = (surface: string | undefined): Tint => {
    const channels = surface === undefined ? null : parse(surface);
    if (channels === null) {
        return {h: 210, s: 0.1};
    }
    const {h, s} = toHsl(channels[0], channels[1], channels[2]);

    return {h, s: Math.min(s, 0.15)};
};

/**
 * The dark shade of one of SuiteCRM's greys in the given part, or null when
 * the colour is not one the dark mode takes (a colour that means something,
 * a text already light, a shadow).
 *
 * - A light background goes dark, the lightest the darkest: white becomes
 *   the page, the light greys the panels and the fields above it. A dark
 *   background (a tooltip) is left: it already is.
 * - A light border follows the backgrounds, a step lighter, so it is still
 *   seen. A translucent black one turns translucent white.
 * - A dark text goes light, the darkest the lightest: the body text near
 *   white, the muted greys a little less. A light text is on something
 *   coloured, and stays.
 */
export const darkShade = (part: Part, colour: string, tint: Tint): string | null => {
    const channels = parse(colour);
    if (channels === null) {
        return null;
    }
    const [red, green, blue, alpha] = channels;
    const {s, l} = toHsl(red, green, blue);
    if (s >= GREY) {
        return null;
    }

    const translucentBlack = alpha < 1 && l < 0.1;
    if (translucentBlack) {
        // Over a dark page, a black veil disappears: borders and texts turn
        // to a white one. A background veil (a backdrop) keeps darkening.
        return part === 'bg' ? null : `rgba(255, 255, 255, ${Math.min(1, alpha * 1.2).toFixed(3)})`;
    }

    const shade = (lightness: number): string => {
        const solid = hex({h: tint.h, s: tint.s, l: lightness});

        return alpha < 1 ? `${solid}${Math.round(alpha * 255).toString(16).padStart(2, '0')}` : solid;
    };

    switch (part) {
        case 'bg':
            return l > 0.5 ? shade(0.11 + (1 - l) * 1.1) : null;
        case 'border':
            return l > 0.5 ? shade(0.22 + (1 - l) * 0.9) : null;
        case 'text':
            return l < 0.6 ? shade(0.9 - l * 0.45) : null;
    }
};

/* --------------------------------------------------------- the rules ---- */

/** The part a CSS property gives its colours, or null for one left alone. */
export const partOf = (property: string): Part | null => {
    const name = property.trim().toLowerCase();
    if (name.startsWith('background')) {
        return 'bg';
    }
    if (name === 'color' || name === 'fill' || name === 'caret-color') {
        return 'text';
    }
    if (name.startsWith('border') || name.startsWith('outline')) {
        return 'border';
    }

    return null;
};

/** The colours a value can be written in, and nothing inside a url(). */
const COLOUR = /url\([^)]*\)|#[0-9a-f]{3,8}\b|rgba?\([^)]*\)|\b(?:white|black)\b/gi;

/** The variable a grey becomes, in its part. */
export const variableOf = (part: Part, colour: string): string => {
    const channels = parse(colour);
    const [red, green, blue, alpha] = channels ?? [0, 0, 0, 1];
    const code = [red, green, blue].map(channel => Math.round(channel).toString(16).padStart(2, '0')).join('');

    return `--ct-dk-${part}-${code}${alpha < 1 ? `-${Math.round(alpha * 1000)}` : ''}`;
};

/**
 * A declaration's value with its greys handed over to their dark variables,
 * and the variables found, with their dark value. Null when nothing changed.
 */
export const handOverGreys = (
    property: string,
    value: string,
    tint: Tint,
    found: Map<string, string>,
): string | null => {
    const part = partOf(property);
    if (part === null || value.includes('var(--ct-')) {
        return null;
    }

    let changed = false;
    const handed = value.replace(COLOUR, written => {
        if (written.toLowerCase().startsWith('url(')) {
            return written;
        }
        const dark = darkShade(part, written, tint);
        if (dark === null) {
            return written;
        }
        const variable = variableOf(part, written);
        found.set(variable, dark);
        changed = true;

        return `var(${variable}, ${written})`;
    });

    return changed ? handed : null;
};

/**
 * The declarations of a block, split at the semicolons that end them and not
 * at those inside parentheses (a data: URL carries some).
 */
export const declarations = (body: string): string[] => {
    const found: string[] = [];
    let depth = 0;
    let start = 0;
    for (let at = 0; at < body.length; at++) {
        const character = body[at];
        if (character === '(') {
            depth++;
        } else if (character === ')') {
            depth = Math.max(0, depth - 1);
        } else if (character === ';' && depth === 0) {
            found.push(body.slice(start, at));
            start = at + 1;
        }
    }
    found.push(body.slice(start));

    return found.filter(declaration => declaration.trim() !== '');
};

/**
 * A declaration block with its greys handed over, or null when it has none.
 * Applied after the client's colours, on a block they may have changed.
 */
export const darkBlock = (body: string, tint: Tint, found: Map<string, string>): string | null => {
    let changed = false;
    const handed = declarations(body).map(declaration => {
        const colon = declaration.indexOf(':');
        if (colon === -1) {
            return declaration;
        }
        const property = declaration.slice(0, colon);
        const value = declaration.slice(colon + 1);
        const dark = handOverGreys(property, value, tint, found);
        if (dark === null) {
            return declaration;
        }
        changed = true;

        return `${property}:${dark}`;
    });

    return changed ? handed.join(';') : null;
};

/** The rule that gives the dark variables their value, dark mode only. */
export const darkVariables = (found: Map<string, string>, extra: Record<string, string>): string => {
    const declared = [...Object.entries(extra), ...found.entries()]
        .map(([name, value]) => `${name}:${value};`)
        .join('');

    return `:root[${SCHEME_ATTRIBUTE}="dark"]{color-scheme:dark;${declared}}`;
};

/* -------------------------------------------------------- the choice ---- */

export const readScheme = (): Scheme => {
    try {
        const stored = window.localStorage.getItem(SCHEME_KEY);

        return SCHEMES.includes(stored as Scheme) ? stored as Scheme : 'auto';
    } catch {
        return 'auto';
    }
};

const remember = (scheme: Scheme): void => {
    try {
        window.localStorage.setItem(SCHEME_KEY, scheme);
    } catch {
        // Forgotten between visits, still kept in the user's preferences.
    }
};

const prefersDark = (): MediaQueryList => window.matchMedia('(prefers-color-scheme: dark)');

/** Whether the choice shows the dark mode, on this computer, now. */
export const isDark = (scheme: Scheme): boolean => scheme === 'dark' || (scheme === 'auto' && prefersDark().matches);

/** Marks a document, the page or one in a frame, with the mode shown. */
export const markDocument = (paper: Document, scheme: Scheme): void => {
    paper.documentElement.setAttribute(SCHEME_ATTRIBUTE, isDark(scheme) ? 'dark' : 'light');
};

/**
 * The choice kept in the user's preferences, or null on the login screen,
 * where nobody is known yet.
 */
export const fetchScheme = async (): Promise<Scheme | null> => {
    try {
        const answer = await fetch(SCHEME_URL, {credentials: 'same-origin', headers: {Accept: 'application/json'}});
        if (!answer.ok || !(answer.headers.get('Content-Type') ?? '').includes('json')) {
            return null;
        }
        const {scheme} = await answer.json() as {scheme?: string};

        return SCHEMES.includes(scheme as Scheme) ? scheme as Scheme : null;
    } catch {
        return null;
    }
};

export const saveScheme = async (scheme: Scheme): Promise<void> => {
    remember(scheme);
    try {
        await fetch(SCHEME_URL, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: new URLSearchParams({scheme}).toString(),
        });
    } catch {
        // Remembered in this browser at least.
    }
};

/** The next choice, as the button goes round: light, dark, as the computer. */
export const nextScheme = (scheme: Scheme): Scheme => SCHEMES[(SCHEMES.indexOf(scheme) + 1) % SCHEMES.length];

/** What the button says of a choice. */
export const SCHEME_WORDS: Record<Scheme, string> = {
    light: 'Mode clair (cliquer : mode sombre)',
    dark: 'Mode sombre (cliquer : comme l’ordinateur)',
    auto: 'Comme l’ordinateur (cliquer : mode clair)',
};

/** Its icon: a sun, a moon, half of each. */
export const SCHEME_ICONS: Record<Scheme, string> = {
    light: '<svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true"><circle cx="12" cy="12" r="4.5" fill="currentColor"/><g stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M12 2v2.5M12 19.5V22M2 12h2.5M19.5 12H22M4.9 4.9l1.8 1.8M17.3 17.3l1.8 1.8M4.9 19.1l1.8-1.8M17.3 6.7l1.8-1.8"/></g></svg>',
    dark: '<svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true"><path fill="currentColor" d="M20 14.5A8.5 8.5 0 0 1 9.5 4a8.5 8.5 0 1 0 10.5 10.5z"/></svg>',
    auto: '<svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true"><circle cx="12" cy="12" r="8.5" fill="none" stroke="currentColor" stroke-width="2"/><path fill="currentColor" d="M12 3.5a8.5 8.5 0 0 1 0 17z"/></svg>',
};

/** Calls back whenever the computer switches between light and dark. */
export const onSystemChange = (callback: () => void): void => {
    prefersDark().addEventListener('change', callback);
};
