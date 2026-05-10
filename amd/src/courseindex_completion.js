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
 * Course-index section completion indicator for Grid format.
 *
 * Reads section completion state from the reactive course state and injects
 * a checkmark next to the name of any section whose tracked activities are
 * all complete. Re-evaluates whenever a section's state changes (e.g. when
 * an activity is marked complete).
 *
 * @module     format_moderngrid/courseindex_completion
 * @copyright  2026 Adebare Showemimo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {BaseComponent} from 'core/reactive';
import {getCurrentCourseEditor} from 'core_courseformat/courseeditor';
import {getString} from 'core/str';
import Templates from 'core/templates';
import log from 'core/log';

const INDICATOR_CLASS = 'format-grid-section-complete';

let labelPromise = null;
let iconPromise = null;

const getLabel = () => {
    if (!labelPromise) {
        labelPromise = getString('sectioncomplete', 'format_moderngrid');
    }
    return labelPromise;
};

const getIconHtml = () => {
    if (!iconPromise) {
        iconPromise = Templates.renderPix('i/completion-auto-y', 'core', '');
    }
    return iconPromise;
};

export default class Component extends BaseComponent {

    create() {
        this.name = 'format_moderngrid_courseindex_completion';
        this.selectors = {
            SECTION: '[data-for="section"]',
            SECTION_TITLE: '.courseindex-section-title',
            INDICATOR: `.${INDICATOR_CLASS}`,
        };
    }

    static init(target) {
        const element = document.querySelector(target || '#course-index');
        if (!element) {
            log.debug('format_moderngrid/courseindex_completion: course index not found');
            return null;
        }
        return new this({
            element,
            reactive: getCurrentCourseEditor(),
        });
    }

    stateReady(state) {
        this._refreshAll(state);
    }

    getWatchers() {
        return [
            {watch: `section:created`, handler: this._refreshFromArgs},
            {watch: `section:updated`, handler: this._refreshFromArgs},
            {watch: `section:deleted`, handler: this._refreshFromArgs},
            {watch: `cm:updated`, handler: this._refreshFromState},
        ];
    }

    _refreshFromArgs({element}) {
        if (!element) {
            return;
        }
        this._applyToSection(element);
    }

    _refreshFromState() {
        if (this.reactive && this.reactive.state) {
            this._refreshAll(this.reactive.state);
        }
    }

    _refreshAll(state) {
        if (!state || !state.section) {
            return;
        }
        state.section.forEach((section) => {
            this._applyToSection(section);
        });
    }

    _applyToSection(section) {
        if (!section || section.iscomplete === undefined) {
            return;
        }
        const sectionEl = this.element.querySelector(
            `${this.selectors.SECTION}[data-id="${section.id}"]`
        );
        if (!sectionEl) {
            return;
        }
        const titleRow = sectionEl.querySelector(this.selectors.SECTION_TITLE);
        if (!titleRow) {
            return;
        }
        const existing = titleRow.querySelector(this.selectors.INDICATOR);

        if (!section.iscomplete) {
            if (existing) {
                existing.remove();
            }
            return;
        }
        if (existing) {
            return;
        }
        Promise.all([getIconHtml(), getLabel()]).then(([iconHtml, label]) => {
            // Re-check in case of a race with another update.
            if (titleRow.querySelector(this.selectors.INDICATOR)) {
                return;
            }
            const span = document.createElement('span');
            span.className = `${INDICATOR_CLASS} ms-1`;
            span.title = label;
            span.setAttribute('aria-label', label);
            span.innerHTML = iconHtml;
            titleRow.appendChild(span);
            return;
        }).catch((err) => log.debug(err));
    }
}
