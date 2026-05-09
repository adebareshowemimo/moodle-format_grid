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
 * Section output class for the Grid course format.
 *
 * @package    format_grid
 * @copyright  2026 Adebare Showemimo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace format_grid\output\courseformat\content;

use core_courseformat\output\local\content\section as section_base;
use format_grid\output\courseformat\section_completion_trait;
use stdClass;
use renderer_base;
use completion_info;

/**
 * Section output class for the Grid format.
 *
 * @package    format_grid
 * @copyright  2026 Adebare Showemimo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class section extends section_base {
    use section_completion_trait;

    /**
     * Get the template name for this renderable.
     *
     * In single section view, use the core template to show activities.
     * In grid view, use the card template.
     *
     * @param \renderer_base $renderer
     * @return string
     */
    public function get_template_name(\renderer_base $renderer): string {
        // If viewing a single section (section page), use the core template to show activities.
        if ($this->format->get_sectionid()) {
            return 'core_courseformat/local/content/section';
        }
        // In grid view, use the card template.
        return 'format_grid/local/content/section';
    }

    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @param renderer_base $output typically, the renderer that's calling this function
     * @return stdClass data context for a mustache template
     */
    public function export_for_template(renderer_base $output): stdClass {
        global $CFG;

        $format = $this->format;
        $course = $format->get_course();
        $section = $this->section;

        // Get base data from parent.
        $data = parent::export_for_template($output);

        // In single section view, just use the parent data (for core template with activities).
        if ($format->get_sectionid()) {
            return $data;
        }

        // Get section 0 display option.
        $section0display = $format->get_format_options()['section0display'] ?? 'default';
        $section0ascard = ($section0display === 'card');

        // Pass section0ascard to template for conditional rendering.
        $data->section0ascard = $section0ascard;

        // Handle section 0 - either skip (default) or render as card.
        if ($section->sectionnum == 0) {
            $data->issection0 = true;

            // If section 0 should show as card, populate all card data.
            // Otherwise, return early with basic data.
            if (!$section0ascard) {
                return $data;
            }
        }

        // Section image.
        $imageurl = $format->get_section_image_url($section->id);
        if (empty($imageurl)) {
            // Use generated pattern as default.
            $imageurl = $format->get_section_default_image($section->id);
            $data->hasdefaultimage = true;
        } else {
            $data->hasdefaultimage = false;
        }
        $data->sectionimage = $imageurl;
        $data->hasimage = !empty($imageurl);

        // Section URL.
        $data->sectionurl = (new \moodle_url('/course/section.php', ['id' => $section->id]))->out(false);

        // Section name.
        $data->sectiontitle = $format->get_section_name($section);

        // Section summary (truncated).
        $summary = $section->summary ?? '';
        // Strip HTML tags and decode entities for plain text display.
        $summary = strip_tags($summary);
        $summary = html_entity_decode($summary, ENT_QUOTES, 'UTF-8');
        $summary = trim($summary);
        if (strlen($summary) > 120) {
            $summary = substr($summary, 0, 117) . '...';
        }
        $data->sectionsummary = $summary;
        $data->hassummary = !empty(trim($summary));

        // Activity totals and completion math come from the same recursive
        // walk used by mod_endofsection's "Section progress" panel, so the
        // numbers on the grid card and the end-of-section page agree.
        $modinfo = $format->get_modinfo();
        $completiondata = $this->compute_section_completion($course, $section, $modinfo);
        $activitycount = $completiondata['activitycount'];

        $data->activitycount = $activitycount;
        $data->hasactivities = $activitycount > 0;
        $data->activitylabel = ($activitycount == 1)
            ? get_string('sectionactivities_singular', 'format_grid', $activitycount)
            : get_string('sectionactivities', 'format_grid', $activitycount);

        // Completion progress.
        require_once($CFG->libdir . '/completionlib.php');
        $completion = new completion_info($course);
        if ($completion->is_enabled()) {
            $data->completionenabled = true;
            $data->completionprogress = $completiondata['progress'];
            $data->completioncomplete = $completiondata['complete'];
            $data->completiontotal = $completiondata['total'];
            $data->hasprogress = $completiondata['total'] > 0;
            $data->iscomplete = $completiondata['iscomplete'];
            $data->isinprogress = ($completiondata['complete'] > 0 && !$completiondata['iscomplete']);
            $data->isnotstarted = !$data->iscomplete && !$data->isinprogress;

            // Match EOS: numerator and denominator are tracked-completion
            // activities. No fallback to the raw activity count.
            $data->rowcomplete = $completiondata['complete'];
            $data->rowtotal = $completiondata['total'];
            $data->hasrowdata = $activitycount > 0;
        } else {
            $data->completionenabled = false;
            $data->hasprogress = false;
            $data->iscomplete = false;
            $data->isnotstarted = false;
            $data->isinprogress = false;
            $data->rowcomplete = 0;
            $data->rowtotal = 0;
            $data->hasrowdata = $activitycount > 0;
        }

        // Card styles from format options.
        $cardstyle = $format->get_format_options()['sectioncardstyle'] ?? 'card';
        $data->cardstyle = $cardstyle;
        $data->isoverlaystyle = ($cardstyle === 'overlay');
        $data->isminimalstyle = ($cardstyle === 'minimal');
        $data->iscardstyle = ($cardstyle === 'card');

        // Visibility.
        $data->ishidden = !$section->visible;
        $data->uservisible = $section->uservisible;

        // Section number.
        $data->sectionnumber = $section->sectionnum;

        // Format options.
        $showsectiontitles = $format->get_format_options()['showsectiontitles'] ?? 1;
        $showsectionsummary = $format->get_format_options()['showsectionsummary'] ?? 1;
        $showactivitiescount = $format->get_format_options()['showactivitiescount'] ?? 1;
        $showprogressbar = $format->get_format_options()['showprogressbar'] ?? 1;
        $showcompletionrow = $format->get_format_options()['showcompletionrow'] ?? 1;
        $data->showsectiontitles = (bool)$showsectiontitles;
        $data->showsectionsummary = (bool)$showsectionsummary;
        $data->showactivitiescount = (bool)$showactivitiescount;
        $data->showprogressbar = (bool)$showprogressbar;
        $data->showcompletionrow = (bool)$showcompletionrow;

        return $data;
    }
}
