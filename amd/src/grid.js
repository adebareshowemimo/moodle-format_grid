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
 * Grid format JavaScript module.
 *
 * @module     format_grid/grid
 * @copyright  2026 Adebare Showemimo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {BaseComponent} from 'core/reactive';
import {getCurrentCourseEditor} from 'core_courseformat/courseeditor';
import log from 'core/log';

/**
 * Grid format component.
 */
export default class Component extends BaseComponent {

    /**
     * Constructor hook.
     */
    create() {
        this.name = 'format_grid';
        this.selectors = {
            GRID_CONTAINER: '[data-region="format-grid"]',
            SECTION_CARD: '[data-region="section-card"]',
        };
        this.classes = {
            DRAGGING: 'dragging',
            DRAG_OVER: 'drag-over',
        };
    }

    /**
     * Static method to create a component instance.
     *
     * @param {string} target the DOM main element or its ID
     * @param {object} selectors optional css selector overrides
     * @return {Component|null}
     */
    static init(target, selectors) {
        const selector = target || '[data-region="format-grid"]';
        const element = document.querySelector(selector);
        if (!element) {
            log.debug('Grid format: main element not found');
            return null;
        }
        return new Component({
            element: element,
            reactive: getCurrentCourseEditor(),
            selectors,
        });
    }

    /**
     * Initial state ready method.
     */
    stateReady() {
        this._initializeCards();
        this._setupEventListeners();
    }

    /**
     * Initialize card behaviors.
     * @private
     */
    _initializeCards() {
        const cards = this.getElements(this.selectors.SECTION_CARD);
        cards.forEach(card => {
            this._setupCardAccessibility(card);
        });
    }

    /**
     * Setup keyboard accessibility for cards.
     * @param {Element} card The card element
     * @private
     */
    _setupCardAccessibility(card) {
        // Ensure cards are keyboard navigable.
        if (!card.hasAttribute('tabindex')) {
            card.setAttribute('tabindex', '0');
        }

        card.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' || e.key === ' ') {
                const link = card.querySelector('a');
                if (link) {
                    e.preventDefault();
                    link.click();
                }
            }
        });
    }

    /**
     * Setup event listeners.
     * @private
     */
    _setupEventListeners() {
        // Listen for state changes.
        this.addEventListener(
            this.element,
            'click',
            this._handleCardClick.bind(this)
        );
    }

    /**
     * Handle click on cards.
     * @param {Event} event Click event
     * @private
     */
    _handleCardClick(event) {
        const card = event.target.closest(this.selectors.SECTION_CARD);
        if (!card) {
            return;
        }

        // Log interaction for debugging.
        const sectionId = card.dataset.sectionId;
        log.debug(`Grid card clicked: section ${sectionId}`);
    }

    /**
     * Called when the reactive state changes.
     *
     * @param {Object} state the new state
     */
    stateUpdated(state) {
        // Re-render cards if sections changed.
        if (state.section) {
            this._updateSectionCards(state.section);
        }
    }

    /**
     * Update section cards based on state changes.
     * @param {Object} sections Section state data
     * @private
     */
    _updateSectionCards(sections) {
        const cards = this.getElements(this.selectors.SECTION_CARD);
        cards.forEach(card => {
            const sectionId = parseInt(card.dataset.sectionId);
            const section = sections.get(sectionId);
            if (section) {
                this._updateCardVisibility(card, section);
            }
        });
    }

    /**
     * Update card visibility based on section state.
     * @param {Element} card The card element
     * @param {Object} section Section data
     * @private
     */
    _updateCardVisibility(card, section) {
        card.classList.toggle('format-grid-card--hidden', !section.visible);
    }

    /**
     * Destroy the component.
     */
    destroy() {
        // Cleanup if needed.
    }
}
