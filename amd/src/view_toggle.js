// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Student view toggle for mod_uemsinfotutoria.
 *
 * Animates height between "Meu polo" and "Lista completa" without reserving
 * the maximum height (which would create dead whitespace on the shorter view).
 *
 * @module     mod_uemsinfotutoria/view_toggle
 * @copyright  2026 UEMS Virtual
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

const SELECTORS = {
    SHELL:   '[data-region="uemsinfotutoria-switcher"]',
    STAGE:   '#uit-stage',
    BUTTON:  '[data-view]',
    PANEL:   '[data-panel]',
    ACTIVE:  'uit-view--active',
    LEAVING: 'uit-view--leaving',
    ANIM:    'uit-stage--animating',
};

/**
 * Measure the scrollHeight of a hidden panel without flickering.
 * Temporarily renders it off-screen via the measure class.
 *
 * @param {HTMLElement} panel
 * @returns {number}
 */
function measureHeight(panel) {
    panel.classList.add('uit-view--measure');
    const h = panel.scrollHeight;
    panel.classList.remove('uit-view--measure');
    return h;
}

/**
 * Animate the stage between two views.
 *
 * @param {HTMLElement} stage
 * @param {HTMLElement} current   Currently visible panel.
 * @param {HTMLElement} next      Panel to show.
 * @param {NodeList}    buttons   All toggle buttons.
 * @param {string}      nextName  data-view value of next view.
 */
function showView(stage, current, next, buttons, nextName) {
    if (!next || next === current || stage.classList.contains(SELECTORS.ANIM)) {
        return;
    }

    // Update button states.
    buttons.forEach(btn => {
        btn.setAttribute('aria-selected', String(btn.dataset.view === nextName));
    });

    const startH = current.scrollHeight;
    const endH   = measureHeight(next);

    // Lock current height so the browser has a start value to animate from.
    stage.style.height = startH + 'px';
    // Force layout so the browser registers the explicit height before we change it.
    // eslint-disable-next-line no-unused-expressions
    stage.offsetHeight;

    stage.classList.add(SELECTORS.ANIM);
    current.classList.add(SELECTORS.LEAVING);
    next.classList.add(SELECTORS.ACTIVE);

    requestAnimationFrame(() => {
        stage.style.height = endH + 'px';
    });

    const onEnd = (e) => {
        if (e.propertyName !== 'height') {
            return;
        }
        stage.removeEventListener('transitionend', onEnd);
        current.classList.remove(SELECTORS.ACTIVE, SELECTORS.LEAVING);
        stage.classList.remove(SELECTORS.ANIM);
        stage.style.height = 'auto';
    };

    stage.addEventListener('transitionend', onEnd);
}

/**
 * Initialise all switchers on the page.
 */
export const init = () => {
    document.querySelectorAll(SELECTORS.SHELL).forEach(shell => {
        const stage   = shell.querySelector(SELECTORS.STAGE);
        const buttons = shell.querySelectorAll(SELECTORS.BUTTON);
        const panels  = shell.querySelectorAll(SELECTORS.PANEL);

        if (!stage || !buttons.length || !panels.length) {
            return;
        }

        buttons.forEach(btn => {
            btn.addEventListener('click', () => {
                const name    = btn.dataset.view;
                const current = shell.querySelector('.' + SELECTORS.ACTIVE);
                const next    = [...panels].find(p => p.dataset.panel === name);
                showView(stage, current, next, buttons, name);
            });
        });
    });
};
