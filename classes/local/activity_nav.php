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
 * Activity-page navigation helper for the Grid course format.
 *
 * @package    format_grid
 * @copyright  2026 Adebare Showemimo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace format_grid\local;

use cm_info;
use completion_info;
use stdClass;

/**
 * Computes the previous and next activities relative to a given course module
 * inside a Grid-format course, plus the list of incomplete completion items
 * for the current activity (used to lock the Next button).
 */
class activity_nav {
    /**
     * Modules whose view pages should never display the prev/next nav.
     * Quiz / assignment because nudging mid-attempt or mid-submission is
     * destructive; subsection because it's a structural wrapper; endofsection
     * because that activity owns its own Continue CTA.
     */
    public const EXCLUDED_MODNAMES = ['quiz', 'assign', 'subsection', 'endofsection'];

    /** @var stdClass */
    protected $course;

    /** @var cm_info */
    protected $cm;

    /** @var stdClass */
    protected $user;

    /** @var \course_modinfo */
    protected $modinfo;

    /** @var completion_info */
    protected $completion;

    /**
     * Create the activity navigation helper.
     *
     * @param stdClass $course
     * @param cm_info $cm the activity the learner is currently viewing
     * @param stdClass $user
     */
    public function __construct(stdClass $course, cm_info $cm, stdClass $user) {
        global $CFG;
        require_once($CFG->libdir . '/completionlib.php');

        $this->course = $course;
        $this->cm = $cm;
        $this->user = $user;
        $this->modinfo = get_fast_modinfo($course, $user->id);
        $this->completion = new completion_info($course);
    }

    /**
     * Previous visible non-excluded activity in reading order, or null.
     *
     * @return cm_info|null
     */
    public function get_previous(): ?cm_info {
        return $this->walk(-1);
    }

    /**
     * Next visible non-excluded activity in reading order, or null.
     *
     * @return cm_info|null
     */
    public function get_next(): ?cm_info {
        return $this->walk(1);
    }

    /**
     * The current activity has completion configured and the user has not
     * met it. The Next button is locked when this is true AND the course
     * format option `navlockincomplete` is on.
     *
     * @return bool
     */
    public function is_lockable(): bool {
        if ((int)$this->cm->completion === COMPLETION_TRACKING_NONE) {
            return false;
        }
        $data = $this->completion->get_data($this->cm, false, $this->user->id);
        return !$this->is_completionstate_done((int)$data->completionstate);
    }

    /**
     * Description sentences for the unmet completion criteria of the current
     * activity. Used as the body of the locked-button popup.
     *
     * @return string[]
     */
    public function get_incomplete_required(): array {
        if (!$this->is_lockable()) {
            return [];
        }
        $items = [format_string($this->cm->name)];
        return $items;
    }

    /**
     * Walk forward (+1) or backward (-1) through the course's activities
     * starting from the current cm. Skips hidden/restricted activities and
     * any module name in EXCLUDED_MODNAMES, and skips the current cm itself.
     *
     * @param int $direction +1 for next, -1 for previous
     * @return cm_info|null
     */
    protected function walk(int $direction): ?cm_info {
        $sectionnum = (int)$this->cm->sectionnum;
        $cmid = (int)$this->cm->id;

        // Walk inside the current section first.
        $sectioncms = $this->modinfo->sections[$sectionnum] ?? [];
        $idx = array_search($cmid, $sectioncms, true);
        if ($idx !== false) {
            $candidates = $direction > 0
                ? array_slice($sectioncms, $idx + 1)
                : array_reverse(array_slice($sectioncms, 0, $idx));
            foreach ($candidates as $candidatecmid) {
                $found = $this->candidate($candidatecmid);
                if ($found) {
                    return $found;
                }
            }
        }

        // Walk into adjacent sections, in order, until we find one with a
        // qualifying cm or run off the ends of the course.
        $lastnum = (int)course_get_format($this->course)->get_last_section_number();
        $step = $direction > 0 ? 1 : -1;
        $next = $sectionnum + $step;
        while ($next >= 0 && $next <= $lastnum) {
            $section = $this->modinfo->get_section_info($next);
            if ($section && $section->uservisible && !empty($this->modinfo->sections[$next])) {
                $list = $this->modinfo->sections[$next];
                if ($direction < 0) {
                    $list = array_reverse($list);
                }
                foreach ($list as $candidatecmid) {
                    $found = $this->candidate($candidatecmid);
                    if ($found) {
                        return $found;
                    }
                }
            }
            $next += $step;
        }

        return null;
    }

    /**
     * Return the cm_info if it's a valid prev/next target, else null.
     *
     * @param int $cmid
     * @return cm_info|null
     */
    protected function candidate(int $cmid): ?cm_info {
        if (!isset($this->modinfo->cms[$cmid])) {
            return null;
        }
        $cm = $this->modinfo->cms[$cmid];
        if ((int)$cm->id === (int)$this->cm->id) {
            return null;
        }
        if (in_array($cm->modname, self::EXCLUDED_MODNAMES, true)) {
            return null;
        }
        if (!$cm->uservisible || !$cm->is_visible_on_course_page()) {
            return null;
        }
        return $cm;
    }

    /**
     * Check whether a completion state is complete.
     *
     * @param int $state
     * @return bool
     */
    protected function is_completionstate_done(int $state): bool {
        return $state === COMPLETION_COMPLETE || $state === COMPLETION_COMPLETE_PASS;
    }
}
