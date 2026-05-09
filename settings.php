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
 * Site-level admin settings for the Grid course format.
 *
 * Lets administrators control the colours used for the section
 * completion badge and progress bar shown on grid cards.
 *
 * @package    format_grid
 * @copyright  2026 Adebare Showemimo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {
    $settings->add(new admin_setting_heading(
        'format_grid/completionheading',
        get_string('completioncolours', 'format_grid'),
        get_string('completioncolours_desc', 'format_grid')
    ));

    $settings->add(new admin_setting_configcolourpicker(
        'format_grid/completebadgebgcolor',
        get_string('completebadgebgcolor', 'format_grid'),
        get_string('completebadgebgcolor_desc', 'format_grid'),
        '#28a745'
    ));

    $settings->add(new admin_setting_configcolourpicker(
        'format_grid/completebadgetextcolor',
        get_string('completebadgetextcolor', 'format_grid'),
        get_string('completebadgetextcolor_desc', 'format_grid'),
        '#ffffff'
    ));

    $settings->add(new admin_setting_configcolourpicker(
        'format_grid/progressbarcolor',
        get_string('progressbarcolor', 'format_grid'),
        get_string('progressbarcolor_desc', 'format_grid'),
        '#28a745'
    ));

    $settings->add(new admin_setting_heading(
        'format_grid/statecolourheading',
        get_string('statecolours', 'format_grid'),
        get_string('statecolours_desc', 'format_grid')
    ));

    $settings->add(new admin_setting_configcolourpicker(
        'format_grid/notstartedcolor',
        get_string('notstartedcolor', 'format_grid'),
        get_string('notstartedcolor_desc', 'format_grid'),
        '#9ca3af'
    ));

    $settings->add(new admin_setting_configcolourpicker(
        'format_grid/inprogresscolor',
        get_string('inprogresscolor', 'format_grid'),
        get_string('inprogresscolor_desc', 'format_grid'),
        '#f59e0b'
    ));

    $settings->add(new admin_setting_configcolourpicker(
        'format_grid/completecolor',
        get_string('completecolor', 'format_grid'),
        get_string('completecolor_desc', 'format_grid'),
        '#22c55e'
    ));
}
