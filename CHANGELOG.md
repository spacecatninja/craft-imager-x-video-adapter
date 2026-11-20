# Imager X Video Adapter Changelog

## 1.1.2 - Unreleased

### Fixed
- Fixed the video frame being extracted again on every call to `getPath()` and `getTransformPath()`. Imager X calls both for each transform, so with `cacheEnabled` off, each frame was extracted twice.
- Fixed `getDuration()` throwing an exception for a missing or unreadable file. It now logs the error and returns `0`.

## 1.1.1 - 2026-05-23

### Added
- Added support for Imager X 6.0

## 1.1.0 - 2024-02-09

### Added
- Added support for Craft 5

## 1.0.0 - 2022-06-17

### Added
- Initial public release
