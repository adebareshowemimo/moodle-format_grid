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
 * Section image upload page for Grid format.
 *
 * @package    format_grid
 * @copyright  2026 Adebare Showemimo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/formslib.php');
require_once($CFG->libdir . '/filelib.php');

$sectionid = required_param('id', PARAM_INT);
$courseid = required_param('courseid', PARAM_INT);

$course = $DB->get_record('course', ['id' => $courseid], '*', MUST_EXIST);
$section = $DB->get_record('course_sections', ['id' => $sectionid, 'course' => $courseid], '*', MUST_EXIST);

require_login($course);
$context = context_course::instance($course->id);
require_capability('moodle/course:update', $context);

$format = course_get_format($course);
$sectionname = $format->get_section_name($section);

$PAGE->set_url('/course/format/grid/sectionimage.php', ['id' => $sectionid, 'courseid' => $courseid]);
$PAGE->set_context($context);
$PAGE->set_pagelayout('admin');
$PAGE->set_title(get_string('sectionimage', 'format_grid') . ': ' . $sectionname);
$PAGE->set_heading($course->fullname);

$PAGE->navbar->add(get_string('sectionimage', 'format_grid'));

/**
 * Section image form.
 */
class format_grid_sectionimage_form extends moodleform {
    /**
     * Form definition.
     */
    public function definition() {
        $mform = $this->_form;
        $data = $this->_customdata;

        $mform->addElement('hidden', 'id', $data['sectionid']);
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'courseid', $data['courseid']);
        $mform->setType('courseid', PARAM_INT);

        // File manager for section image.
        $mform->addElement(
            'filemanager',
            'sectionimage',
            get_string('sectionimage', 'format_grid'),
            null,
            [
                'subdirs' => 0,
                'maxfiles' => 1,
                'accepted_types' => ['web_image'],
            ]
        );
        $mform->addHelpButton('sectionimage', 'sectionimage', 'format_grid');

        $this->add_action_buttons(true, get_string('savechanges'));
    }
}

// Process form.
$returnurl = new moodle_url('/course/view.php', ['id' => $courseid]);

$formdata = [
    'sectionid' => $sectionid,
    'courseid' => $courseid,
];

$mform = new format_grid_sectionimage_form(null, $formdata);

// Get existing file.
$draftitemid = file_get_submitted_draft_itemid('sectionimage');
file_prepare_draft_area(
    $draftitemid,
    $context->id,
    'format_grid',
    'sectionimage',
    $sectionid,
    ['subdirs' => 0, 'maxfiles' => 1]
);

$mform->set_data(['sectionimage' => $draftitemid]);

if ($mform->is_cancelled()) {
    redirect($returnurl);
} else if ($data = $mform->get_data()) {
    // Save the file.
    file_save_draft_area_files(
        $data->sectionimage,
        $context->id,
        'format_grid',
        'sectionimage',
        $sectionid,
        ['subdirs' => 0, 'maxfiles' => 1]
    );

    // Update the format_grid_images table.
    $record = $DB->get_record('format_grid_images', [
        'courseid' => $courseid,
        'sectionid' => $sectionid,
    ]);

    $fs = get_file_storage();
    $files = $fs->get_area_files($context->id, 'format_grid', 'sectionimage', $sectionid, 'sortorder', false);
    $file = reset($files);

    if ($file) {
        if ($record) {
            $record->image = $file->get_id();
            $record->timemodified = time();
            $DB->update_record('format_grid_images', $record);
        } else {
            $record = new stdClass();
            $record->courseid = $courseid;
            $record->sectionid = $sectionid;
            $record->image = $file->get_id();
            $record->timecreated = time();
            $record->timemodified = time();
            $DB->insert_record('format_grid_images', $record);
        }
    } else {
        // No file uploaded, delete record if exists.
        if ($record) {
            $DB->delete_records('format_grid_images', ['id' => $record->id]);
        }
    }

    // Purge course cache.
    rebuild_course_cache($courseid, true);

    redirect($returnurl, get_string('changessaved'), null, \core\output\notification::NOTIFY_SUCCESS);
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('sectionimage', 'format_grid') . ': ' . $sectionname);

$mform->display();

echo $OUTPUT->footer();
