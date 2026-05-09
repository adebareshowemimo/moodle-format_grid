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
 * Content output class for the Grid course format.
 *
 * @package    format_grid
 * @copyright  2026 Adebare Showemimo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace format_grid\output\courseformat;

use core_courseformat\output\local\content as content_base;
use renderer_base;
use stdClass;

/**
 * Content output class for the Grid format.
 *
 * @package    format_grid
 * @copyright  2026 Adebare Showemimo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class content extends content_base {
    /** @var bool Grid format has add section. */
    protected $hasaddsection = true;

    /**
     * Get the template name for this renderable.
     *
     * @param \renderer_base $renderer
     * @return string
     */
    public function get_template_name(\renderer_base $renderer): string {
        return 'format_grid/local/content';
    }

    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @param renderer_base $output typically, the renderer that's calling this function
     * @return stdClass data context for a mustache template
     */
    public function export_for_template(renderer_base $output): stdClass {
        global $PAGE;

        $format = $this->format;
        $course = $format->get_course();

        // Get format options.
        $gridcolumns = $format->get_format_options()['gridcolumns'] ?? 3;
        $showsectiontitles = $format->get_format_options()['showsectiontitles'] ?? 1;
        $showsectionsummary = $format->get_format_options()['showsectionsummary'] ?? 1;
        $cardstyle = $format->get_format_options()['sectioncardstyle'] ?? 'card';
        $aspectratio = $format->get_format_options()['imageaspectratio'] ?? '16:9';
        $section0display = $format->get_format_options()['section0display'] ?? 'default';

        // Add CSS custom properties.
        $aspectclass = 'aspect-' . str_replace(':', '-', $aspectratio);

        $data = parent::export_for_template($output);

        // Add grid-specific data.
        $data->gridcolumns = $gridcolumns;
        $data->showsectiontitles = (bool)$showsectiontitles;
        $data->showsectionsummary = (bool)$showsectionsummary;
        $data->cardstyle = $cardstyle;
        $data->aspectclass = $aspectclass;
        $data->isgridformat = true;

        // Section 0 display options.
        $data->section0display = $section0display;
        $data->section0asdefault = ($section0display === 'default');
        $data->section0ascard = ($section0display === 'card');
        $data->section0hidden = ($section0display === 'hidden');

        // Hide the initialsection if section0 is hidden or shown as card.
        if ($section0display !== 'default') {
            $data->initialsection = null;
        }

        // Bootstrap row-cols mapping. Cards stack 1-per-row on phones, scale up at each breakpoint.
        $columnclasses = [
            2 => 'row-cols-1 row-cols-sm-2',
            3 => 'row-cols-1 row-cols-sm-2 row-cols-lg-3',
            4 => 'row-cols-1 row-cols-sm-2 row-cols-lg-3 row-cols-xl-4',
            5 => 'row-cols-1 row-cols-sm-2 row-cols-lg-3 row-cols-xl-4 row-cols-xxl-5',
            6 => 'row-cols-1 row-cols-sm-2 row-cols-lg-3 row-cols-xl-4 row-cols-xxl-6',
        ];
        $data->gridclass = $columnclasses[$gridcolumns] ?? $columnclasses[3];

        // Admin-configurable colours for completion indicators.
        $badgebg = get_config('format_grid', 'completebadgebgcolor');
        $badgetext = get_config('format_grid', 'completebadgetextcolor');
        $progresscolor = get_config('format_grid', 'progressbarcolor');
        $notstartedcolor = get_config('format_grid', 'notstartedcolor');
        $inprogresscolor = get_config('format_grid', 'inprogresscolor');
        $completecolor = get_config('format_grid', 'completecolor');
        $data->completebadgebg = !empty($badgebg) ? $badgebg : '#28a745';
        $data->completebadgetext = !empty($badgetext) ? $badgetext : '#ffffff';
        $data->progressbarcolor = !empty($progresscolor) ? $progresscolor : '#28a745';
        $data->notstartedcolor = !empty($notstartedcolor) ? $notstartedcolor : '#9ca3af';
        $data->inprogresscolor = !empty($inprogresscolor) ? $inprogresscolor : '#f59e0b';
        $data->completecolor = !empty($completecolor) ? $completecolor : '#22c55e';

        // Add AMD module for grid functionality.
        $PAGE->requires->js_call_amd('format_grid/grid', 'init', ['[data-region="format-grid"]']);

        // Inject section-complete checkmarks into the course index drawer.
        $PAGE->requires->js_call_amd('format_grid/courseindex_completion', 'init');

        // Collapse course index drawer by default if configured.
        $courseindexdefault = $format->get_format_options()['courseindexdefault'] ?? 1;
        if (!$courseindexdefault) {
            $PAGE->requires->js_call_amd('format_grid/courseindex', 'init');
        }

        return $data;
    }
}
