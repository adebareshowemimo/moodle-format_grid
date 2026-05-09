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

namespace format_grid;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/course/lib.php');

/**
 * Grid course format related unit tests.
 *
 * @package    format_grid
 * @copyright  2026 Adebare Showemimo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \format_grid
 */
final class format_grid_test extends \advanced_testcase {
    /**
     * Tests for format_grid::get_section_name method with default section names.
     *
     * @return void
     */
    public function test_get_section_name(): void {
        global $DB;
        $this->resetAfterTest(true);

        // Generate a course with 5 sections.
        $generator = $this->getDataGenerator();
        $numsections = 5;
        $course = $generator->create_course(
            ['numsections' => $numsections, 'format' => 'grid'],
            ['createsections' => true]
        );

        // Get section names for course.
        $coursesections = $DB->get_records('course_sections', ['course' => $course->id]);

        // Test get_section_name with default section names.
        $courseformat = course_get_format($course);
        foreach ($coursesections as $section) {
            // Assert that with unmodified section names, get_section_name returns the same result as get_default_section_name.
            $this->assertEquals($courseformat->get_default_section_name($section), $courseformat->get_section_name($section));
        }
    }

    /**
     * Tests for format_grid::get_section_name method with modified section names.
     *
     * @return void
     */
    public function test_get_section_name_customised(): void {
        global $DB;
        $this->resetAfterTest(true);

        // Generate a course with 5 sections.
        $generator = $this->getDataGenerator();
        $numsections = 5;
        $course = $generator->create_course(
            ['numsections' => $numsections, 'format' => 'grid'],
            ['createsections' => true]
        );

        // Get section names for course.
        $coursesections = $DB->get_records('course_sections', ['course' => $course->id]);

        // Modify section names.
        $customname = "Custom Section";
        foreach ($coursesections as $section) {
            $section->name = "$customname $section->section";
            $DB->update_record('course_sections', $section);
        }

        // Requery updated section names then test get_section_name.
        $coursesections = $DB->get_records('course_sections', ['course' => $course->id]);
        $courseformat = course_get_format($course);
        foreach ($coursesections as $section) {
            // Assert that with modified section names, get_section_name returns the modified section name.
            $this->assertEquals($section->name, $courseformat->get_section_name($section));
        }
    }

    /**
     * Tests for format_grid::get_default_section_name.
     *
     * @return void
     */
    public function test_get_default_section_name(): void {
        global $DB;
        $this->resetAfterTest(true);

        // Generate a course with 5 sections.
        $generator = $this->getDataGenerator();
        $numsections = 5;
        $course = $generator->create_course(
            ['numsections' => $numsections, 'format' => 'grid'],
            ['createsections' => true]
        );

        // Get section names for course.
        $coursesections = $DB->get_records('course_sections', ['course' => $course->id]);

        // Test get_default_section_name with default section names.
        $courseformat = course_get_format($course);
        foreach ($coursesections as $section) {
            if ($section->section == 0) {
                $sectionname = get_string('section0name', 'format_grid');
                $this->assertEquals($sectionname, $courseformat->get_default_section_name($section));
            } else {
                $sectionname = get_string('sectionname', 'format_grid') . ' ' . $section->section;
                $this->assertEquals($sectionname, $courseformat->get_default_section_name($section));
            }
        }
    }

    /**
     * Tests for format_grid::course_format_options.
     *
     * @return void
     */
    public function test_course_format_options(): void {
        $this->resetAfterTest(true);

        $generator = $this->getDataGenerator();
        $course = $generator->create_course(['format' => 'grid']);

        $courseformat = course_get_format($course);
        $options = $courseformat->get_format_options();

        // Test that all expected options exist with correct defaults.
        $this->assertArrayHasKey('gridcolumns', $options);
        $this->assertArrayHasKey('showsectiontitles', $options);
        $this->assertArrayHasKey('showsectionsummary', $options);
        $this->assertArrayHasKey('showactivitiescount', $options);
        $this->assertArrayHasKey('sectioncardstyle', $options);
        $this->assertArrayHasKey('imageaspectratio', $options);

        // Test default values.
        $this->assertEquals(3, $options['gridcolumns']);
        $this->assertEquals(1, $options['showsectiontitles']);
        $this->assertEquals(1, $options['showsectionsummary']);
        $this->assertEquals(1, $options['showactivitiescount']);
        $this->assertEquals('card', $options['sectioncardstyle']);
        $this->assertEquals('16:9', $options['imageaspectratio']);
    }

    /**
     * Tests for format_grid::course_format_options with custom values.
     *
     * @return void
     */
    public function test_course_format_options_custom(): void {
        $this->resetAfterTest(true);

        $generator = $this->getDataGenerator();
        $course = $generator->create_course([
            'format' => 'grid',
            'gridcolumns' => 4,
            'sectioncardstyle' => 'overlay',
            'imageaspectratio' => '4:3',
        ]);

        $courseformat = course_get_format($course);
        $options = $courseformat->get_format_options();

        // Test that custom values are stored correctly.
        $this->assertEquals(4, $options['gridcolumns']);
        $this->assertEquals('overlay', $options['sectioncardstyle']);
        $this->assertEquals('4:3', $options['imageaspectratio']);
    }

    /**
     * Tests for format_grid::get_section_image_url returns null when no image.
     *
     * @return void
     */
    public function test_get_section_image_url_no_image(): void {
        global $DB;
        $this->resetAfterTest(true);

        $generator = $this->getDataGenerator();
        $course = $generator->create_course(
            ['numsections' => 3, 'format' => 'grid'],
            ['createsections' => true]
        );

        $section = $DB->get_record('course_sections', ['course' => $course->id, 'section' => 1]);
        /** @var \format_grid $courseformat */
        $courseformat = course_get_format($course);

        // No image uploaded - should return null (then fallback will handle it).
        $imageurl = $courseformat->get_section_image_url($section->id);
        $this->assertNull($imageurl);
    }

    /**
     * Tests for format_grid::get_section_default_image returns data URI.
     *
     * @return void
     */
    public function test_get_section_default_image(): void {
        global $DB;
        $this->resetAfterTest(true);

        $generator = $this->getDataGenerator();
        $course = $generator->create_course(
            ['numsections' => 3, 'format' => 'grid'],
            ['createsections' => true]
        );

        $section = $DB->get_record('course_sections', ['course' => $course->id, 'section' => 1]);
        /** @var \format_grid $courseformat */
        $courseformat = course_get_format($course);

        // Default image should return a data URI (GeoPattern).
        $imageurl = $courseformat->get_section_default_image($section->id);
        $this->assertNotEmpty($imageurl);
        $this->assertStringStartsWith('data:image/svg+xml;base64,', $imageurl);
    }

    /**
     * Tests for format_grid::get_section_default_image returns unique images.
     *
     * @return void
     */
    public function test_get_section_default_image_unique(): void {
        global $DB;
        $this->resetAfterTest(true);

        $generator = $this->getDataGenerator();
        $course = $generator->create_course(
            ['numsections' => 3, 'format' => 'grid'],
            ['createsections' => true]
        );

        $section1 = $DB->get_record('course_sections', ['course' => $course->id, 'section' => 1]);
        $section2 = $DB->get_record('course_sections', ['course' => $course->id, 'section' => 2]);
        /** @var \format_grid $courseformat */
        $courseformat = course_get_format($course);

        // Each section should get a unique pattern.
        $image1 = $courseformat->get_section_default_image($section1->id);
        $image2 = $courseformat->get_section_default_image($section2->id);

        $this->assertNotEquals($image1, $image2);
    }

    /**
     * Tests for format_grid uses correct renderer.
     *
     * @return void
     */
    public function test_uses_correct_renderer(): void {
        $this->resetAfterTest(true);

        $generator = $this->getDataGenerator();
        $course = $generator->create_course(['format' => 'grid']);

        $courseformat = course_get_format($course);

        // Format should support components (modern renderer).
        $this->assertTrue($courseformat->supports_components());
    }

    /**
     * Tests for format_grid supports ajax.
     *
     * @return void
     */
    public function test_supports_ajax(): void {
        $this->resetAfterTest(true);

        $generator = $this->getDataGenerator();
        $course = $generator->create_course(['format' => 'grid']);

        $courseformat = course_get_format($course);
        $ajaxsupport = $courseformat->supports_ajax();

        $this->assertTrue($ajaxsupport->capable);
    }

    /**
     * Tests for format_grid does not use indentation.
     *
     * @return void
     */
    public function test_uses_indentation(): void {
        $this->resetAfterTest(true);

        $generator = $this->getDataGenerator();
        $course = $generator->create_course(['format' => 'grid']);

        $courseformat = course_get_format($course);

        // Grid format should not use indentation.
        $this->assertFalse($courseformat->uses_indentation());
    }

    /**
     * Tests for format_grid::get_view_url.
     *
     * @return void
     */
    public function test_get_view_url(): void {
        global $DB;
        $this->resetAfterTest(true);

        $generator = $this->getDataGenerator();
        $course = $generator->create_course(
            ['numsections' => 3, 'format' => 'grid'],
            ['createsections' => true]
        );

        $section = $DB->get_record('course_sections', ['course' => $course->id, 'section' => 2]);
        $courseformat = course_get_format($course);

        // Test course view URL.
        $url = $courseformat->get_view_url(null);
        $this->assertStringContainsString('/course/view.php', $url->out());
        $this->assertStringContainsString('id=' . $course->id, $url->out());

        // Test section URL with navigation option.
        $url = $courseformat->get_view_url($section, ['navigation' => true]);
        $this->assertStringContainsString('/course/section.php', $url->out());
    }

    /**
     * Tests that format_grid can update format options.
     *
     * @return void
     */
    public function test_update_course_format_options(): void {
        $this->resetAfterTest(true);

        $generator = $this->getDataGenerator();
        $course = $generator->create_course(['format' => 'grid']);

        $courseformat = course_get_format($course);

        // Update format options.
        $data = [
            'gridcolumns' => 5,
            'sectioncardstyle' => 'minimal',
        ];
        $courseformat->update_course_format_options((object)$data);

        // Reload and verify.
        $courseformat = course_get_format($course);
        $options = $courseformat->get_format_options();

        $this->assertEquals(5, $options['gridcolumns']);
        $this->assertEquals('minimal', $options['sectioncardstyle']);
    }

    /**
     * Tests for format_grid page body class.
     *
     * @return void
     */
    public function test_page_body_class(): void {
        $this->resetAfterTest(true);

        $generator = $this->getDataGenerator();
        $course = $generator->create_course(['format' => 'grid']);

        // The format class name indicates the format (format-grid is added by Moodle core).
        $courseformat = course_get_format($course);
        $this->assertEquals('grid', $courseformat->get_format());
    }
}
