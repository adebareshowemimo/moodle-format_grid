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
 * @package    format_moderngrid
 * @copyright  2026 Adebare Showemimo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {
    $settings->add(new admin_setting_heading(
        'format_moderngrid/completionheading',
        get_string('completioncolours', 'format_moderngrid'),
        get_string('completioncolours_desc', 'format_moderngrid')
    ));

    $settings->add(new admin_setting_configcolourpicker(
        'format_moderngrid/completebadgebgcolor',
        get_string('completebadgebgcolor', 'format_moderngrid'),
        get_string('completebadgebgcolor_desc', 'format_moderngrid'),
        '#28a745'
    ));

    $settings->add(new admin_setting_configcolourpicker(
        'format_moderngrid/completebadgetextcolor',
        get_string('completebadgetextcolor', 'format_moderngrid'),
        get_string('completebadgetextcolor_desc', 'format_moderngrid'),
        '#ffffff'
    ));

    $settings->add(new admin_setting_configcolourpicker(
        'format_moderngrid/progressbarcolor',
        get_string('progressbarcolor', 'format_moderngrid'),
        get_string('progressbarcolor_desc', 'format_moderngrid'),
        '#28a745'
    ));

    $settings->add(new admin_setting_heading(
        'format_moderngrid/statecolourheading',
        get_string('statecolours', 'format_moderngrid'),
        get_string('statecolours_desc', 'format_moderngrid')
    ));

    $settings->add(new admin_setting_configcolourpicker(
        'format_moderngrid/notstartedcolor',
        get_string('notstartedcolor', 'format_moderngrid'),
        get_string('notstartedcolor_desc', 'format_moderngrid'),
        '#9ca3af'
    ));

    $settings->add(new admin_setting_configcolourpicker(
        'format_moderngrid/inprogresscolor',
        get_string('inprogresscolor', 'format_moderngrid'),
        get_string('inprogresscolor_desc', 'format_moderngrid'),
        '#f59e0b'
    ));

    $settings->add(new admin_setting_configcolourpicker(
        'format_moderngrid/completecolor',
        get_string('completecolor', 'format_moderngrid'),
        get_string('completecolor_desc', 'format_moderngrid'),
        '#22c55e'
    ));
}
