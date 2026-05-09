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
 * Grid course format main class.
 *
 * @package    format_grid
 * @copyright  2026 Adebare Showemimo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot . '/course/format/lib.php');

use core\output\inplace_editable;

/**
 * Main class for the Grid course format.
 *
 * @package    format_grid
 * @copyright  2026 Adebare Showemimo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class format_grid extends core_courseformat\base {
    /**
     * Returns true if this course format uses sections.
     *
     * @return bool
     */
    public function uses_sections() {
        return true;
    }

    /**
     * Returns true if this course format uses the course index.
     *
     * @return bool
     */
    public function uses_course_index() {
        return true;
    }

    /**
     * Returns true if indentation is allowed.
     *
     * @return bool
     */
    public function uses_indentation(): bool {
        return false;
    }

    /**
     * Whether this format allows sections to be deleted.
     *
     * @param section_info|stdClass|int $section
     * @return bool
     */
    public function can_delete_section($section) {
        return true;
    }

    /**
     * Returns the display name of the given section.
     *
     * @param int|stdClass $section Section object from database or just field section.section
     * @return string Display name that the course format prefers
     */
    public function get_section_name($section) {
        $section = $this->get_section($section);
        if ((string)$section->name !== '') {
            return format_string(
                $section->name,
                true,
                ['context' => context_course::instance($this->courseid)]
            );
        } else {
            return $this->get_default_section_name($section);
        }
    }

    /**
     * Returns the default section name.
     *
     * @param int|stdClass $section Section object from database or just field course_sections section
     * @return string The default value for the section name.
     */
    public function get_default_section_name($section) {
        $section = $this->get_section($section);
        if ($section->sectionnum == 0) {
            return get_string('section0name', 'format_grid');
        }
        return get_string('sectionname', 'format_grid') . ' ' . $section->sectionnum;
    }

    /**
     * Generate the title for this section page.
     *
     * @return string the page title
     */
    public function page_title(): string {
        return get_string('sectionoutline');
    }

    /**
     * The URL to use for the specified course (with section).
     *
     * @param int|stdClass $section Section object from database or just field course_sections.section
     * @param array $options options for view URL
     * @return moodle_url
     */
    public function get_view_url($section, $options = []) {
        $course = $this->get_course();
        if (array_key_exists('sr', $options) && !is_null($options['sr'])) {
            $sectionno = $options['sr'];
        } else if (is_object($section)) {
            $sectionno = $section->section;
        } else {
            $sectionno = $section;
        }
        if ((!empty($options['navigation']) || array_key_exists('sr', $options)) && $sectionno !== null) {
            $sectioninfo = $this->get_section($sectionno);
            return new moodle_url('/course/section.php', ['id' => $sectioninfo->id]);
        }
        return new moodle_url('/course/view.php', ['id' => $course->id]);
    }

    /**
     * Returns the information about the ajax support.
     *
     * @return stdClass
     */
    public function supports_ajax() {
        $ajaxsupport = new stdClass();
        $ajaxsupport->capable = true;
        return $ajaxsupport;
    }

    /**
     * Returns true if the format supports components.
     *
     * @return bool
     */
    public function supports_components() {
        return true;
    }

    /**
     * Definitions of the additional options that this course format uses for section.
     *
     * @param bool $foreditform
     * @return array
     */
    public function section_format_options($foreditform = false) {
        // Section images are handled through create_edit_form_elements
        // and through the custom sectionimage.php page.
        return [];
    }

    /**
     * Creates the elements for the edit form.
     *
     * Adds a filemanager for section images in the section edit form.
     *
     * @param MoodleQuickForm $mform form to be modified
     * @param bool $forsection true if this is a section edit form
     * @return array of elements
     */
    public function create_edit_form_elements(&$mform, $forsection = false) {
        global $PAGE;

        $elements = parent::create_edit_form_elements($mform, $forsection);

        if ($forsection) {
            // Add section image filemanager.
            $sectionid = optional_param('id', 0, PARAM_INT);
            if ($sectionid) {
                $context = context_course::instance($this->courseid);

                $mform->addElement('header', 'gridimagehdr', get_string('sectionimage', 'format_grid'));

                // Prepare draft area.
                $draftitemid = file_get_submitted_draft_itemid('sectionimage');
                file_prepare_draft_area(
                    $draftitemid,
                    $context->id,
                    'format_grid',
                    'sectionimage',
                    $sectionid,
                    ['subdirs' => 0, 'maxfiles' => 1]
                );

                $mform->addElement(
                    'filemanager',
                    'sectionimage',
                    get_string('uploadsectionimage', 'format_grid'),
                    null,
                    [
                        'subdirs' => 0,
                        'maxfiles' => 1,
                        'accepted_types' => ['web_image'],
                    ]
                );
                $mform->addHelpButton('sectionimage', 'sectionimage', 'format_grid');
                $mform->setDefault('sectionimage', $draftitemid);
            }
        }

        return $elements;
    }

    /**
     * Updates format options for a section.
     *
     * Handles section image uploads.
     *
     * @param stdClass|array $data form data or array
     * @return bool whether there were any changes
     */
    public function update_section_format_options($data) {
        global $DB;

        $data = (array)$data;

        // Handle section image upload.
        if (!empty($data['sectionimage']) && !empty($data['id'])) {
            $context = context_course::instance($this->courseid);
            $sectionid = $data['id'];

            // Save the file.
            file_save_draft_area_files(
                $data['sectionimage'],
                $context->id,
                'format_grid',
                'sectionimage',
                $sectionid,
                ['subdirs' => 0, 'maxfiles' => 1]
            );

            // Update the format_grid_images table.
            $record = $DB->get_record('format_grid_images', [
                'courseid' => $this->courseid,
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
                    $record->courseid = $this->courseid;
                    $record->sectionid = $sectionid;
                    $record->image = $file->get_id();
                    $record->timecreated = time();
                    $record->timemodified = time();
                    $DB->insert_record('format_grid_images', $record);
                }
            } else {
                // No file, delete record if exists.
                if ($record) {
                    $DB->delete_records('format_grid_images', ['id' => $record->id]);
                }
            }
        }

        return parent::update_section_format_options($data);
    }

    /**
     * Definitions of the additional options that this course format uses for course.
     *
     * @param bool $foreditform
     * @return array of options
     */
    public function course_format_options($foreditform = false) {
        static $courseformatoptions = false;

        if ($courseformatoptions === false) {
            $courseformatoptions = [
                'gridcolumns' => [
                    'default' => 3,
                    'type' => PARAM_INT,
                ],
                'showsectiontitles' => [
                    'default' => 1,
                    'type' => PARAM_INT,
                ],
                'showsectionsummary' => [
                    'default' => 1,
                    'type' => PARAM_INT,
                ],
                'showactivitiescount' => [
                    'default' => 1,
                    'type' => PARAM_INT,
                ],
                'showprogressbar' => [
                    'default' => 1,
                    'type' => PARAM_INT,
                ],
                'showcompletionrow' => [
                    'default' => 1,
                    'type' => PARAM_INT,
                ],
                'sectioncardstyle' => [
                    'default' => 'card',
                    'type' => PARAM_ALPHA,
                ],
                'imageaspectratio' => [
                    'default' => '16:9',
                    'type' => PARAM_TEXT,
                ],
                'section0display' => [
                    'default' => 'default',
                    'type' => PARAM_ALPHA,
                ],
                'courseindexdefault' => [
                    'default' => 1,
                    'type' => PARAM_INT,
                ],
                'hidesecondarynavigation' => [
                    'default' => 0,
                    'type' => PARAM_INT,
                ],
            ];
        }

        if ($foreditform && !isset($courseformatoptions['gridcolumns']['label'])) {
            $courseformatoptionsedit = [
                'gridcolumns' => [
                    'label' => new lang_string('gridcolumns', 'format_grid'),
                    'help' => 'gridcolumns',
                    'help_component' => 'format_grid',
                    'element_type' => 'select',
                    'element_attributes' => [
                        [
                            2 => '2 ' . get_string('columns', 'format_grid'),
                            3 => '3 ' . get_string('columns', 'format_grid'),
                            4 => '4 ' . get_string('columns', 'format_grid'),
                            5 => '5 ' . get_string('columns', 'format_grid'),
                            6 => '6 ' . get_string('columns', 'format_grid'),
                        ],
                    ],
                ],
                'showsectiontitles' => [
                    'label' => new lang_string('showsectiontitles', 'format_grid'),
                    'help' => 'showsectiontitles',
                    'help_component' => 'format_grid',
                    'element_type' => 'select',
                    'element_attributes' => [
                        [
                            0 => get_string('no'),
                            1 => get_string('yes'),
                        ],
                    ],
                ],
                'showsectionsummary' => [
                    'label' => new lang_string('showsectionsummary', 'format_grid'),
                    'help' => 'showsectionsummary',
                    'help_component' => 'format_grid',
                    'element_type' => 'select',
                    'element_attributes' => [
                        [
                            0 => get_string('no'),
                            1 => get_string('yes'),
                        ],
                    ],
                ],
                'showactivitiescount' => [
                    'label' => new lang_string('showactivitiescount', 'format_grid'),
                    'help' => 'showactivitiescount',
                    'help_component' => 'format_grid',
                    'element_type' => 'select',
                    'element_attributes' => [
                        [
                            0 => get_string('no'),
                            1 => get_string('yes'),
                        ],
                    ],
                ],
                'showprogressbar' => [
                    'label' => new lang_string('showprogressbar', 'format_grid'),
                    'help' => 'showprogressbar',
                    'help_component' => 'format_grid',
                    'element_type' => 'select',
                    'element_attributes' => [
                        [
                            0 => get_string('no'),
                            1 => get_string('yes'),
                        ],
                    ],
                ],
                'showcompletionrow' => [
                    'label' => new lang_string('showcompletionrow', 'format_grid'),
                    'help' => 'showcompletionrow',
                    'help_component' => 'format_grid',
                    'element_type' => 'select',
                    'element_attributes' => [
                        [
                            0 => get_string('no'),
                            1 => get_string('yes'),
                        ],
                    ],
                ],
                'sectioncardstyle' => [
                    'label' => new lang_string('sectioncardstyle', 'format_grid'),
                    'help' => 'sectioncardstyle',
                    'help_component' => 'format_grid',
                    'element_type' => 'select',
                    'element_attributes' => [
                        [
                            'card' => get_string('cardstyle_card', 'format_grid'),
                            'overlay' => get_string('cardstyle_overlay', 'format_grid'),
                            'minimal' => get_string('cardstyle_minimal', 'format_grid'),
                        ],
                    ],
                ],
                'imageaspectratio' => [
                    'label' => new lang_string('imageaspectratio', 'format_grid'),
                    'help' => 'imageaspectratio',
                    'help_component' => 'format_grid',
                    'element_type' => 'select',
                    'element_attributes' => [
                        [
                            '1:1' => get_string('aspectratio_1_1', 'format_grid'),
                            '4:3' => get_string('aspectratio_4_3', 'format_grid'),
                            '16:9' => get_string('aspectratio_16_9', 'format_grid'),
                            '21:9' => get_string('aspectratio_21_9', 'format_grid'),
                        ],
                    ],
                ],
                'section0display' => [
                    'label' => new lang_string('section0display', 'format_grid'),
                    'help' => 'section0display',
                    'help_component' => 'format_grid',
                    'element_type' => 'select',
                    'element_attributes' => [
                        [
                            'default' => get_string('section0display_default', 'format_grid'),
                            'card' => get_string('section0display_card', 'format_grid'),
                            'hidden' => get_string('section0display_hidden', 'format_grid'),
                        ],
                    ],
                ],
                'courseindexdefault' => [
                    'label' => new lang_string('courseindexdefault', 'format_grid'),
                    'help' => 'courseindexdefault',
                    'help_component' => 'format_grid',
                    'element_type' => 'select',
                    'element_attributes' => [
                        [
                            1 => get_string('courseindexdefault_open', 'format_grid'),
                            0 => get_string('courseindexdefault_collapsed', 'format_grid'),
                        ],
                    ],
                ],
                'hidesecondarynavigation' => [
                    'label' => new lang_string('hidesecondarynavigation', 'format_grid'),
                    'help' => 'hidesecondarynavigation',
                    'help_component' => 'format_grid',
                    'element_type' => 'select',
                    'element_attributes' => [
                        [
                            0 => get_string('no'),
                            1 => get_string('yes'),
                        ],
                    ],
                ],
            ];
            $courseformatoptions = array_merge_recursive($courseformatoptions, $courseformatoptionsedit);
        }

        return $courseformatoptions;
    }

    /**
     * Updates format options for a course.
     *
     * @param stdClass|array $data
     * @param stdClass $oldcourse
     * @return bool
     */
    public function update_course_format_options($data, $oldcourse = null) {
        return $this->update_format_options($data);
    }

    /**
     * Get the section image URL.
     *
     * First checks for a custom uploaded section image.
     * Falls back to images from section summary/description.
     *
     * @param int $sectionid Section ID
     * @return string|null Image URL or null
     */
    public function get_section_image_url($sectionid) {
        global $DB;

        $context = context_course::instance($this->courseid);

        // First, check for a custom format_grid section image.
        $record = $DB->get_record('format_grid_images', [
            'courseid' => $this->courseid,
            'sectionid' => $sectionid,
        ]);

        if ($record && !empty($record->image)) {
            $fs = get_file_storage();
            $files = $fs->get_area_files(
                $context->id,
                'format_grid',
                'sectionimage',
                $sectionid,
                'sortorder DESC, id ASC',
                false
            );

            $file = reset($files);
            if ($file && $file->is_valid_image()) {
                return moodle_url::make_pluginfile_url(
                    $file->get_contextid(),
                    $file->get_component(),
                    $file->get_filearea(),
                    $file->get_itemid(),
                    $file->get_filepath(),
                    $file->get_filename()
                )->out(false);
            }
        }

        // Fallback: check for images in section summary/description.
        return $this->get_section_summary_image_url($sectionid);
    }

    /**
     * Get an image from the section summary/description.
     *
     * @param int $sectionid Section ID
     * @param int $minwidth Minimum image width in pixels
     * @param int $minheight Minimum image height in pixels
     * @return string|null Image URL or null
     */
    protected function get_section_summary_image_url($sectionid, $minwidth = 200, $minheight = 150) {
        $context = context_course::instance($this->courseid);
        $fs = get_file_storage();

        // Get files from section summary (component: course, filearea: section).
        $files = $fs->get_area_files(
            $context->id,
            'course',
            'section',
            $sectionid,
            'sortorder DESC, id ASC',
            false
        );

        foreach ($files as $file) {
            if (!$file->is_valid_image()) {
                continue;
            }

            // Check image dimensions.
            $imageinfo = $file->get_imageinfo();
            if ($imageinfo && $imageinfo['width'] >= $minwidth && $imageinfo['height'] >= $minheight) {
                return moodle_url::make_pluginfile_url(
                    $file->get_contextid(),
                    $file->get_component(),
                    $file->get_filearea(),
                    $file->get_itemid(),
                    $file->get_filepath(),
                    $file->get_filename()
                )->out(false);
            }
        }

        return null;
    }

    /**
     * Generate a default pattern image URL for a section.
     *
     * @param int $sectionid Section ID
     * @return string Pattern image data URL
     */
    public function get_section_default_image($sectionid) {
        $pattern = new \core_geopattern();
        $pattern->patternbyid($this->courseid . '-' . $sectionid);
        return $pattern->datauri();
    }

    /**
     * Allows course format to execute code on moodle_page::set_course().
     *
     * @param moodle_page $page
     */
    public function page_set_course(moodle_page $page) {
        $page->add_body_class('format-grid');
    }
}

/**
 * Serve the files from the format_grid file areas.
 *
 * @param stdClass $course
 * @param stdClass $cm
 * @param context $context
 * @param string $filearea
 * @param array $args
 * @param bool $forcedownload
 * @param array $options
 * @return bool
 */
function format_grid_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = []): bool {
    if ($context->contextlevel != CONTEXT_COURSE) {
        return false;
    }

    require_login($course);

    if ($filearea !== 'sectionimage') {
        return false;
    }

    $sectionid = (int)array_shift($args);
    $fs = get_file_storage();
    $filename = array_pop($args);
    $filepath = $args ? '/' . implode('/', $args) . '/' : '/';

    $file = $fs->get_file($context->id, 'format_grid', $filearea, $sectionid, $filepath, $filename);
    if (!$file || $file->is_directory()) {
        return false;
    }

    send_stored_file($file, 0, 0, $forcedownload, $options);
    return true;
}
