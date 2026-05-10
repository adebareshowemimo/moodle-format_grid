# Grid - Modern Course Format v1.0.0

## Release notes

- Renamed the Moodle frankenstyle component from `format_grid` to `format_moderngrid` for Moodle Plugins Directory submission.
- Renamed the user-facing plugin name to "Grid - Modern Course Format".
- Fixed AJAX section title updates by adding the missing `format_moderngrid_inplace_editable()` callback.
- Rendered section titles through Moodle's standard inplace editable output.
- Added PHPUnit regression coverage for inplace section title updates.

# Grid format v1.5.3

## Release notes

- Expanded the README with a detailed review of the plugin's actual features, course settings, section image behavior, completion support, and activity navigation.
- Kept Moodle 4.5+ as the minimum supported Moodle version.

# Grid format v1.5.2

## Release notes

- Prepared the Grid course format for Moodle Plugins Directory submission.
- Confirmed the component remains `format_moderngrid` and the repository follows the recommended `moodle-format_moderngrid` naming pattern.
- Added release documentation for installation, requirements, repository naming, and license.
- Added full Moodle GPL boilerplate headers to CSS and AMD JavaScript source files.
- Fixed Moodle codechecker formatting issues and language-string ordering.
- Kept Moodle 4.5+ as the minimum supported Moodle version.

## Short description

Grid format displays Moodle course sections as responsive image cards with progress indicators and clear activity navigation.
