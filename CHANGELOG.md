# Craft Ajaxinate Changelog

## 3.0.0 - 2022-09-19

### Added
- Fixed Composer 2 compatibility
- Supported upto Craft version `4.x`

## 2.0.1 - 2019-10-11

### Added
- Added an option to make your data dump class dynamic using `containerClass` option within `render()`.
- Added an option to update the success/error message using `messageClass` option within `render() `.
- Added a new functionality to load data on scroll.

## 2.0.0 - 2019-08-10

> {note} Please adjust your CP settings as majority of the configuration has been migrated to the template and substantial amount of new options has been introduced.

### Added [#5]
- Support for more advanced query options. Now the plugin has an option for default `query` while calling `render()` method.
- Support for all the [Entry Queries](https://docs.craftcms.com/v3/dev/element-queries/entry-queries.html#example) that CRAFT offers.

### Added
- Added `template` option, pass the **Rendering Template** path
- Added `limit` option, pass the limit to load entries on each `loadmore`
- Added `offset` option, entries to skip and load the entries on the page load if `initLoad` is active.
- Added `initLoad` option, to show entries on page load
- Added `resetBtnState` option, to show reset button
- Added `extraFilters` option, pass `fieldHandle` name for showing filters options to the user.
- Added `sortingFilters` option, to show sorting options ( date and price handle only ), options will be shown in the dropdown.
- Added `section` option, pass the sections name from which the entries will be loaded.
- Added `catGroupLimit` option, the number of categories child to show. The default value is 10.
- Added `tagGroup` option, pass an array of tag group handle name.
- Added `noMoreDataMsg` option, change the default message for each configuration.


### Added [#7]
- Display X number of entries on page load. The default value is 10.

### Added [#4]
- Support for `multiple configurations`. The plugin now allows the user to add multiple load more configuration on different templates.

## 1.0.2 - 2019-07-22
### Added [#5]
- Now user have option to show the future entries only.User can enable this in plugin settings page.

## 1.0.1 - 2019-07-19
### Fixed
- `catGroup` hierarchy issue on the frontend template has been  resolved.

### Added
- Front End error message has been made more flexible: Now the user has the functionality to update the default message shown when no data is found.

### Added
- Users can set the limit of child categories that is rendered on the frontend while calling the filter in the template. The default is 10.


## 1.0.0 - 2019-07-07
### Added
- Initial release