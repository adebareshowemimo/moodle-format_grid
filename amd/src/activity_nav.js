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
 * Click-intercept for the locked Next button in the Grid-format activity nav.
 *
 * @module     format_grid/activity_nav
 * @copyright  2026 Adebare Showemimo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import Notification from 'core/notification';
import {get_string as getString} from 'core/str';

const LOCKED_SELECTOR = '[data-fg-nav-locked="1"]';

/**
 * Build the popup body: an intro line followed by a bullet list of the
 * blocking activity name(s).
 *
 * @param {Array} incomplete  array of activity name strings
 * @returns {Promise<string>}
 */
const buildBody = async (incomplete) => {
    const intro = await getString('activitynav_locked_message', 'format_grid');
    const items = incomplete.map((name) => `<li>${name}</li>`).join('');
    return `<p>${intro}</p><ul class="mb-0">${items}</ul>`;
};

/**
 * Initialise the locked-click handler.
 *
 * @param {object} config
 * @param {Array} config.incomplete  list of blocking activity names
 */
export const init = (config = {}) => {
    const incomplete = Array.isArray(config.incomplete) ? config.incomplete : [];
    if (incomplete.length === 0) {
        return;
    }
    const buttons = document.querySelectorAll(LOCKED_SELECTOR);
    buttons.forEach((button) => {
        if (button.dataset.fgNavBound === '1') {
            return;
        }
        button.dataset.fgNavBound = '1';
        button.addEventListener('click', async (e) => {
            e.preventDefault();
            e.stopPropagation();
            const [title, body] = await Promise.all([
                getString('activitynav_locked_title', 'format_grid'),
                buildBody(incomplete),
            ]);
            Notification.alert(title, body);
        });
    });
};
