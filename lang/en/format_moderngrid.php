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
 * Language strings for the Grid course format.
 *
 * @package    format_moderngrid
 * @copyright  2026 Adebare Showemimo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();


$string['activitiescomplete'] = '{$a->complete} of {$a->total} activities completed';
$string['activitynav_aria'] = 'Activity navigation';
$string['activitynav_locked_message'] = 'You haven\'t finished the current activity yet. Please complete it before moving on:';
$string['activitynav_locked_title'] = 'Finish this activity to continue';
$string['activitynav_next'] = 'Next';
$string['activitynav_prev'] = 'Previous';
$string['aspectratio_16_9'] = 'Widescreen (16:9)';
$string['aspectratio_1_1'] = 'Square (1:1)';
$string['aspectratio_21_9'] = 'Ultrawide (21:9)';
$string['aspectratio_4_3'] = 'Standard (4:3)';
$string['cardstyle_card'] = 'Standard card';
$string['cardstyle_minimal'] = 'Minimal';
$string['cardstyle_overlay'] = 'Text overlay';
$string['changesectionimage'] = 'Change image';
$string['closesection'] = 'Close section';
$string['collapseall'] = 'Collapse all sections';
$string['columns'] = 'columns';
$string['completebadgebgcolor'] = 'Completion badge background colour';
$string['completebadgebgcolor_desc'] = 'Background colour of the "Section complete" badge shown on a card when every tracked activity is done.';
$string['completebadgetextcolor'] = 'Completion badge text colour';
$string['completebadgetextcolor_desc'] = 'Text and icon colour for the "Section complete" badge.';
$string['completecolor'] = 'Completed colour';
$string['completecolor_desc'] = 'Used when every tracked activity in the section is complete (green by default).';
$string['completioncolours'] = 'Completion indicator colours';
$string['completioncolours_desc'] = 'Control the colours used for the section completion badge and progress bar shown on grid cards.';
$string['courseindexdefault'] = 'Course index default';
$string['courseindexdefault_collapsed'] = 'Collapsed';
$string['courseindexdefault_help'] = 'Controls whether the course index sidebar starts open or collapsed when students visit this course. When collapsed, students can still open it by clicking the toggle button.';
$string['courseindexdefault_open'] = 'Open';
$string['currentsection'] = 'Current section';
$string['deletesectionimage'] = 'Delete image';
$string['errorinvalidimage'] = 'Invalid image file';
$string['errorsectionnotfound'] = 'Section not found';
$string['expandall'] = 'Expand all sections';
$string['gridcolumns'] = 'Grid columns';
$string['gridcolumns_help'] = 'Number of columns to display in the grid layout on desktop screens.';
$string['hidefromothers'] = 'Hide';
$string['hidesecondarynavigation'] = 'Hide secondary navigation';
$string['hidesecondarynavigation_help'] = 'When enabled, the secondary navigation bar (the tab strip below the page header) is hidden on the course page.';
$string['imageaspectratio'] = 'Image aspect ratio';
$string['imageaspectratio_help'] = 'The aspect ratio for section cover images.';
$string['inprogresscolor'] = 'In progress colour';
$string['inprogresscolor_desc'] = 'Used when at least one — but not all — tracked activities are complete (amber by default).';
$string['newsection'] = 'New section';
$string['nosectionimage'] = 'No image uploaded';
$string['notrackedactivities'] = 'No completion tracked';
$string['notstartedcolor'] = 'Not started colour';
$string['notstartedcolor_desc'] = 'Used when a section has tracked activities but none completed yet (gray by default).';
$string['opensection'] = 'Open section';
$string['page-course-view-moderngrid'] = 'Any course main page in Grid - Modern Course Format';
$string['page-course-view-moderngrid-x'] = 'Any course page in Grid - Modern Course Format';
$string['plugin_description'] = 'A modern, responsive grid layout displaying course sections as beautiful cards with cover images.';
$string['pluginname'] = 'Grid - Modern Course Format';
$string['privacy:metadata'] = 'The Grid format plugin stores section images uploaded by course editors.';
$string['privacy:metadata:format_moderngrid_images'] = 'Stores references to section images.';
$string['privacy:metadata:format_moderngrid_images:courseid'] = 'The course ID.';
$string['privacy:metadata:format_moderngrid_images:image'] = 'The image file reference.';
$string['privacy:metadata:format_moderngrid_images:sectionid'] = 'The section ID.';
$string['progressbarcolor'] = 'Progress bar colour';
$string['progressbarcolor_desc'] = 'Fill colour of the section completion progress bar at the bottom of each card.';
$string['section0display'] = 'General section display';
$string['section0display_card'] = 'Show as card';
$string['section0display_default'] = 'Show as panel (default)';
$string['section0display_help'] = 'Choose how to display the General section (section 0). Default shows it as a panel above the grid. Card shows it as a grid card. Hidden removes it from view.';
$string['section0display_hidden'] = 'Hidden';
$string['section0name'] = 'General';
$string['sectionactivities'] = '{$a} activities';
$string['sectionactivities_singular'] = '{$a} activity';
$string['sectioncardstyle'] = 'Card style';
$string['sectioncardstyle_help'] = 'Choose the visual style for section cards.';
$string['sectioncomplete'] = 'Completed';
$string['sectionimage'] = 'Section image';
$string['sectionimage_help'] = 'Upload a cover image for this section. Recommended size: 800x450 pixels.';
$string['sectionname'] = 'Section';
$string['sectionprogress'] = '{$a}% complete';
$string['showactivitiescount'] = 'Show activities count';
$string['showactivitiescount_help'] = 'Display the number of activities as a badge on each section card.';
$string['showcompletionrow'] = 'Show completion summary';
$string['showcompletionrow_help'] = 'Display the completion summary row on each section card — the small status icon, the "X of Y activities completed" text, and the fraction pill (e.g. 3/5).';
$string['showfromothers'] = 'Show';
$string['showprogressbar'] = 'Show progress bar';
$string['showprogressbar_help'] = 'Display a progress bar on each section card showing how much of the section is complete. The completion summary (icon and fraction count) is always shown when course completion is enabled.';
$string['showsectionsummary'] = 'Show section summary';
$string['showsectionsummary_help'] = 'Display a brief summary excerpt on each card.';
$string['showsectiontitles'] = 'Show section titles';
$string['showsectiontitles_help'] = 'Display the section title on each card.';
$string['statecolours'] = 'Completion state colours';
$string['statecolours_desc'] = 'Colours applied to the completion summary row — the small icon and the fraction pill (e.g. 3/5) on each card. The same colour drives the icon background, the pill text, and is mixed with white for the pill border and background.';
$string['uploadsectionimage'] = 'Upload section image';
$string['viewsection'] = 'View section: {$a}';
