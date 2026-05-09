<?php
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
 * Renderer for the Grid course format.
 *
 * @package    format_grid
 * @copyright  2026 Adebare Showemimo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace format_grid\output;

use core_courseformat\output\section_renderer;
use plugin_renderer_base;

/**
 * Renderer class for the Grid course format.
 *
 * @package    format_grid
 * @copyright  2026 Adebare Showemimo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class renderer extends section_renderer {
    /**
     * Generate the section title, wraps it in a link to the section page if page is to be displayed on a separate page.
     *
     * @param \section_info|\stdClass $section The section info
     * @param \stdClass $course The course object
     * @param bool $onsectionpage true if being printed on a section page
     * @param int $sectionreturn The section to return to after an action
     * @return string HTML fragment
     */
    public function section_title($section, $course, $onsectionpage = false, $sectionreturn = null) {
        return get_section_name($course, $section);
    }

    /**
     * Generate the section title to be displayed on the section page.
     *
     * @param \section_info|\stdClass $section The section info
     * @param \stdClass $course The course object
     * @return string HTML fragment
     */
    public function section_title_without_link($section, $course) {
        return get_section_name($course, $section);
    }
}
