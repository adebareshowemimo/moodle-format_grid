# Grid format

Grid format is a visual Moodle course format for courses that are easier to browse as a set of section cards instead of a long topic list. It keeps Moodle's standard section editing and single-section activity pages, while replacing the course landing page with a responsive grid of cards that can show images, summaries, activity counts, and learner progress.

The format is useful for course home pages, self-paced learning paths, media-heavy courses, and courses where learners need a clear overview of each section before opening it.

## Main Features

- Responsive section card grid with 2, 3, 4, 5, or 6 desktop columns.
- Mobile-friendly layout that stacks cards automatically on smaller screens.
- Three card styles: standard card, text overlay, and minimal.
- Configurable image aspect ratios: 1:1, 4:3, 16:9, and 21:9.
- Section cover images uploaded from the section edit form.
- Automatic image fallback from images already embedded in the section summary.
- Generated pattern image fallback when no section image is available.
- Optional section title, summary excerpt, activity count, progress bar, and completion summary row.
- Section completion indicators using Moodle activity completion data.
- Completed badge when all tracked activities in a section are complete.
- Site-level colour settings for completed badges, progress bars, and completion states.
- Configurable General section display: panel above the grid, grid card, or hidden.
- Course index support with optional default collapsed state.
- Course-index completion checkmarks for completed sections.
- Optional hiding of Moodle's secondary course navigation for learners.
- Previous and Next activity navigation injected into activity pages for Grid-format courses.
- Completion-aware Next button locking on activity pages when the current activity is not complete.
- Editing support for section controls, drag handles, add section, bulk edit tools, and Moodle course-format AJAX behavior.

## Course Settings

Teachers can configure the format from the course settings page after choosing **Grid format**.

Available course-level options include:

- **Grid columns**: choose how many columns to use on large screens.
- **Show section titles**: display or hide card titles.
- **Show section summary**: display or hide a short plain-text summary excerpt.
- **Show activities count**: display the number of visible activities in each section.
- **Show progress bar**: display section completion progress when completion is enabled.
- **Show completion summary**: display the status icon, "X of Y activities completed" text, and completion fraction.
- **Card style**: choose standard card, text overlay, or minimal card display.
- **Image aspect ratio**: choose the cover image shape used by the grid.
- **General section display**: show section 0 as a panel, as a card, or hide it.
- **Course index default**: open or collapse the course index drawer by default.
- **Hide secondary navigation**: hide the secondary course navigation from learners while keeping it visible for teachers and admins.

## Section Images

Each section can have its own cover image. Course editors can upload a section image from the section edit form. The plugin stores section image references in its own `format_grid_images` table and serves the files through Moodle's plugin file API.

Image selection works in this order:

1. A custom image uploaded for the section.
2. A valid image found in the section summary or description.
3. A generated pattern image based on the course and section.

Recommended image size: `800x450` pixels for the default 16:9 layout.

## Completion And Progress

Grid format uses Moodle completion data to show learner progress at section level. Each card can show:

- a percentage progress bar;
- a completion summary row;
- a completed badge when every tracked activity is complete.

The completion calculation skips structural wrapper modules such as subsections and excludes End of Section activities from the section activity count. When subsection wrappers are used, the format recursively includes the nested activities so the card progress matches the learner's section flow.

Administrators can configure the colours used for:

- completed badges;
- completed badge text;
- progress bars;
- not started state;
- in progress state;
- completed state.

## Activity Navigation

On activity view pages inside a Grid-format course, the plugin can add Previous and Next navigation at the bottom of the page. Navigation respects Moodle visibility and skips module types where automatic next/previous movement can be disruptive, including quiz, assignment, subsection, and end-of-section activities.

When the current activity has completion tracking and is not complete, the Next action can be presented as locked. Learners see a completion reminder instead of being moved forward before finishing the current activity.

## Requirements

- Moodle 4.5 or later.
- PHP version supported by the target Moodle release.
- Moodle activity completion must be enabled if you want progress bars, completion rows, and completion locking to show learner progress.

## Installation

1. Copy this plugin to `course/format/grid`.
2. Visit **Site administration** to complete the Moodle plugin installation.
3. Go to a course settings page.
4. Set **Course format** to **Grid format**.
5. Configure the grid display options for that course.

## Repository

The Moodle Plugins Directory recommends the repository naming pattern `moodle-{plugintype}_{pluginname}`. This plugin is published as:

https://github.com/adebareshowemimo/moodle-format_grid

The Moodle component name remains:

```text
format_grid
```

## License

GNU GPL v3 or later.
