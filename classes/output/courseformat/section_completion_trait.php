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
 * Shared section completion calculation for the Grid course format.
 *
 * @package    format_grid
 * @copyright  2026 Adebare Showemimo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace format_grid\output\courseformat;

use cm_info;
use completion_info;
use course_modinfo;
use section_info;
use stdClass;

/**
 * Trait providing the section-level completion calculation.
 *
 * Mirrors mod_endofsection's sectionflow_manager so the grid card progress
 * matches the "Section progress" panel learners see at the end of a section.
 */
trait section_completion_trait {
    /**
     * Flat list of activities owned by this section, descending recursively
     * into any mod_subsection wrappers. Skips wrappers themselves, the End of
     * Section module, and items hidden from the user on every front.
     *
     * Mirrors mod_endofsection\local\sectionflow_manager::collect_section_cms_recursive.
     *
     * @param section_info|stdClass $section section to inspect
     * @param course_modinfo $modinfo course module information
     * @param array $visited keyed by section id to prevent loops
     * @return cm_info[] keyed by cm id
     */
    protected function collect_section_cms($section, course_modinfo $modinfo, array &$visited = []): array {
        $sectionid = (int)$section->id;
        if (isset($visited[$sectionid])) {
            return [];
        }
        $visited[$sectionid] = true;

        $sectionnum = (int)($section->section ?? $section->sectionnum ?? 0);
        $result = [];
        if (empty($modinfo->sections[$sectionnum])) {
            return $result;
        }

        foreach ($modinfo->sections[$sectionnum] as $cmid) {
            $cm = $modinfo->cms[$cmid];

            // Recurse into subsection wrappers in place of counting the wrapper.
            $childsection = $this->find_delegated_section($cm, $modinfo);
            if ($childsection) {
                foreach ($this->collect_section_cms($childsection, $modinfo, $visited) as $nested) {
                    if (!isset($result[$nested->id])) {
                        $result[$nested->id] = $nested;
                    }
                }
                continue;
            }

            if (in_array($cm->modname, ['endofsection', 'subsection'], true)) {
                continue;
            }
            // Lenient visibility: include access-restricted items so locked
            // activities still surface in the count, matching EOS.
            if (!$cm->uservisible && !$cm->is_visible_on_course_page()) {
                continue;
            }
            $result[$cm->id] = $cm;
        }

        return $result;
    }

    /**
     * Return the delegated section a cm wraps (e.g. mod_subsection), or null.
     *
     * @param cm_info $cm
     * @param course_modinfo $modinfo
     * @return section_info|null
     */
    protected function find_delegated_section(cm_info $cm, course_modinfo $modinfo): ?section_info {
        $delegated = $cm->get_delegated_section_info();
        if ($delegated) {
            return $delegated;
        }
        foreach ($modinfo->get_section_info_all() as $section) {
            if (!$section->is_delegated() || $section->component !== 'mod_' . $cm->modname) {
                continue;
            }
            if ((int)$section->itemid === (int)$cm->instance) {
                return $section;
            }
        }
        return null;
    }

    /**
     * Calculate section completion progress.
     *
     * @param stdClass $course course record
     * @param stdClass|section_info $section section to inspect
     * @param course_modinfo $modinfo course module information
     * @return array completion data
     */
    protected function compute_section_completion($course, $section, $modinfo): array {
        global $CFG;
        require_once($CFG->libdir . '/completionlib.php');

        $completion = new completion_info($course);
        $cms = $this->collect_section_cms($section, $modinfo);
        $activitycount = count($cms);

        $complete = 0;
        $total = 0;

        if ($completion->is_enabled()) {
            foreach ($cms as $cm) {
                if ($cm->completion == COMPLETION_TRACKING_NONE) {
                    continue;
                }
                $total++;
                $data = $completion->get_data($cm);
                if (
                    $data->completionstate == COMPLETION_COMPLETE
                    || $data->completionstate == COMPLETION_COMPLETE_PASS
                ) {
                    $complete++;
                }
            }
        }

        $progress = $total > 0 ? (int)round(($complete / $total) * 100) : 0;

        return [
            'complete' => $complete,
            'total' => $total,
            'progress' => $progress,
            'iscomplete' => ($total > 0 && $complete === $total),
            'activitycount' => $activitycount,
        ];
    }
}
