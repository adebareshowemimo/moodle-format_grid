/**
 * This file is part of Moodle - http://moodle.org/
 *
 * Moodle is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Moodle is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Moodle.  If not, see <http://www.gnu.org/licenses/>.
 *
 * Course index drawer collapse module for Grid format.
 *
 * Collapses the course index drawer on page load without
 * persisting the state to user preferences, so users can
 * still toggle it open during their session.
 *
 * @module     format_grid/courseindex
 * @copyright  2026 Adebare Showemimo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

const SELECTOR = '[data-preference="drawer-open-index"]';

/**
 * Initialise: collapse the course index drawer if it is open.
 */
export const init = () => {
    const drawer = document.querySelector(SELECTOR);
    if (!drawer || !drawer.classList.contains('show')) {
        return;
    }

    // Use the Boost Drawers API if available, otherwise fall back to DOM manipulation.
    import('theme_boost/drawers').then(({default: Drawers}) => {
        const instance = Drawers.getDrawerInstanceForNode(drawer);
        if (instance) {
            instance.closeDrawer({focusOnOpenButton: false, updatePreferences: false});
        } else {
            collapseDrawerDOM(drawer);
        }
    }).catch(() => {
        collapseDrawerDOM(drawer);
    });
};

/**
 * Fallback: collapse the drawer via direct DOM manipulation.
 *
 * @param {HTMLElement} drawer The drawer element.
 */
const collapseDrawerDOM = (drawer) => {
    drawer.classList.remove('show');
    drawer.setAttribute('aria-expanded', 'false');
    const stateClass = drawer.dataset.state;
    if (stateClass) {
        // The state class lives on #page, not document.body.
        const page = document.getElementById('page');
        if (page) {
            page.classList.remove(stateClass);
        }
    }
};
