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
 * Section control menu for the Grid course format.
 *
 * @package    format_moderngrid
 * @copyright  2026 Adebare Showemimo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace format_moderngrid\output\courseformat\content\section;

use core_courseformat\output\local\content\section\controlmenu as controlmenu_base;
use core\output\action_menu\link;
use core\output\action_menu\link_secondary;
use core\output\pix_icon;
use core\url;

/**
 * Section control menu class for the Grid format.
 *
 * Adds a "Section image" option to the section control menu.
 *
 * @package    format_moderngrid
 * @copyright  2026 Adebare Showemimo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class controlmenu extends controlmenu_base {
    /**
     * Generate the edit control items of a section.
     *
     * @return array of edit control items
     */
    public function section_control_items() {
        $controls = parent::section_control_items();

        // Add section image control after edit.
        $imagecontrol = $this->get_section_image_item();
        if ($imagecontrol) {
            // Insert after 'edit' item.
            $newcontrols = [];
            foreach ($controls as $key => $control) {
                $newcontrols[$key] = $control;
                if ($key === 'edit') {
                    $newcontrols['sectionimage'] = $imagecontrol;
                }
            }
            $controls = $newcontrols;
        }

        return $controls;
    }

    /**
     * Retrieves the section image item for the control menu.
     *
     * @return link|null The menu item if applicable, otherwise null.
     */
    protected function get_section_image_item(): ?link {
        if (!has_capability('moodle/course:update', $this->coursecontext)) {
            return null;
        }

        // Don't show for section 0.
        if ($this->section->sectionnum == 0) {
            return null;
        }

        $url = new url(
            '/course/format/moderngrid/sectionimage.php',
            [
                'id' => $this->section->id,
                'courseid' => $this->format->get_courseid(),
            ]
        );

        return new link_secondary(
            url: $url,
            icon: new pix_icon('e/insert_edit_image', ''),
            text: get_string('sectionimage', 'format_moderngrid'),
            attributes: ['class' => 'sectionimage'],
        );
    }
}
