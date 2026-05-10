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
 * Hook callbacks for format_moderngrid.
 *
 * @package    format_moderngrid
 * @copyright  2026 Adebare Showemimo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace format_moderngrid;

use core\hook\output\after_standard_main_region_html_generation;
use core_course\hook\before_course_viewed;
use format_moderngrid\local\activity_nav;

/**
 * Static callbacks registered via db/hooks.php.
 */
class hook_callbacks {
    /**
     * Hide the secondary navigation bar on the course view page when the
     * hidesecondarynavigation format option is enabled.
     *
     * Must fire before $OUTPUT->header() is called, which is why this uses
     * the before_course_viewed hook (line 110 of course/view.php) rather than
     * anything inside format.php (which is included after the header at line 394).
     *
     * @param before_course_viewed $hook
     */
    public static function maybe_hide_secondary_nav(before_course_viewed $hook): void {
        global $PAGE;
        $course = $hook->course;
        if (empty($course->format) || $course->format !== 'moderngrid') {
            return;
        }
        $options = course_get_format($course)->get_format_options();
        if (!empty($options['hidesecondarynavigation'])) {
            // Only hide for students; teachers and admins keep the navigation.
            if (!has_capability('moodle/course:update', \context_course::instance($course->id))) {
                $PAGE->set_secondary_navigation(false);
            }
        }
    }

    /**
     * Inject Previous / Next activity navigation at the bottom of every
     * `mod_*_view` page in a Grid-format course.
     *
     * The callback fires on every page request, so the early returns are
     * intentionally cheap and ordered from broadest to most specific.
     *
     * @param after_standard_main_region_html_generation $hook
     */
    public static function inject_activity_nav(after_standard_main_region_html_generation $hook): void {
        global $PAGE, $USER, $OUTPUT;

        if (empty($PAGE->course) || empty($PAGE->course->id) || $PAGE->course->id == SITEID) {
            return;
        }

        // Only mod-*-view pagetypes (e.g. mod-page-view, mod-url-view).
        if (empty($PAGE->pagetype) || !preg_match('/^mod-.+-view$/', $PAGE->pagetype)) {
            return;
        }

        if (empty($PAGE->cm) || empty($PAGE->cm->modname)) {
            return;
        }

        // Excluded module types are off-limits regardless of format.
        if (in_array($PAGE->cm->modname, activity_nav::EXCLUDED_MODNAMES, true)) {
            return;
        }

        // Grid format only.
        $format = course_get_format($PAGE->course);
        if (!$format || $format->get_format() !== 'moderngrid') {
            return;
        }

        $cm = \cm_info::create($PAGE->cm);
        $nav = new activity_nav($PAGE->course, $cm, $USER);
        $previous = $nav->get_previous();
        $next = $nav->get_next();

        if (!$previous && !$next) {
            // Nothing on either side of this activity — render nothing.
            return;
        }

        $lock = $nav->is_lockable() && $next !== null;
        $incomplete = $lock ? $nav->get_incomplete_required() : [];

        $data = [
            'showicons' => true,
            'showname' => true,
            'islocked' => $lock,
            'prev' => $previous ? [
                'url' => $previous->url ? $previous->url->out(false) : '',
                'name' => format_string($previous->name),
            ] : null,
            'next' => $next ? [
                'url' => $next->url ? $next->url->out(false) : '',
                'name' => format_string($next->name),
            ] : null,
        ];

        try {
            $html = $OUTPUT->render_from_template('format_moderngrid/activity_nav', $data);
        } catch (\Throwable $e) {
            // Don't let a render error break the activity page.
            return;
        }

        if ($lock) {
            $PAGE->requires->js_call_amd('format_moderngrid/activity_nav', 'init', [[
                'incomplete' => array_values($incomplete),
            ]]);
        }

        $hook->add_html($html);
    }
}
