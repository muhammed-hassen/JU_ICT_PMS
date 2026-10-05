// Removes !important from the Bootstrap 4 utilities that share a name with a
// Tailwind utility (spacing, sizing, border, rounded). Bootstrap 4 hardcodes
// !important on them, which would beat every Tailwind class of the same name in
// the rewritten pages. See resources/sass/bootstrap-legacy.scss.
import { readFileSync, writeFileSync } from 'node:fs';

const file = process.argv[2];
const shared = /^\.(?:[mp][trblxy]?(?:-(?:sm|md|lg|xl))?-(?:n?\d|auto)|[wh]-(?:\d+|auto)|border(?:-[a-z0-9]+)*|rounded(?:-[a-z0-9]+)*)$/;

let softened = 0;
const css = readFileSync(file, 'utf8').replace(/([^{}]+)\{([^{}]*)\}/g, (rule, selectors, body) => {
    const list = selectors.split(',').map((s) => s.trim());
    if (!list.every((s) => shared.test(s)) || !body.includes('!important')) {
        return rule;
    }
    softened++;
    return `${selectors}{${body.replaceAll(' !important', '')}}`;
});

writeFileSync(file, css);
console.log(`softened ${softened} Bootstrap utility rules`);
