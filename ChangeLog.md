# ChangeLog

All notable changes are documented in this file using the [Keep a CHANGELOG](https://keepachangelog.com/) principles.

## [3.1.7] - 2025-09-22

### Changed

* Suppress `not representable as an int, cast occurred` warning triggered on PHP 8.5

## [3.1.6] - 2024-03-02

### Changed

* Do not use implicitly nullable parameters

### Removed

* This component is no longer supported on PHP 7.0 and PHP 7.1

## [3.1.5] - 2022-09-14

### Fixed

* [#47](https://github.com/sebastianbergmann/exporter/pull/47): Fix `float` export precision

## [3.1.4] - 2021-11-11

### Changed

* [#38](https://github.com/sebastianbergmann/exporter/pull/38): Improve export of closed resources

## [3.1.3] - 2020-11-30

### Changed

* Changed PHP version constraint in `composer.json` from `^7.0` to `>=7.0`

## [3.1.2] - 2019-09-14

### Fixed

* [#29](https://github.com/sebastianbergmann/exporter/pull/29): Second parameter for `str_repeat()` must be an integer

### Removed

* Remove HHVM-specific code that is no longer needed

[3.1.7]: https://github.com/sebastianbergmann/exporter/compare/3.1.6...3.1.7
[3.1.6]: https://github.com/sebastianbergmann/exporter/compare/3.1.5...3.1.6
[3.1.5]: https://github.com/sebastianbergmann/exporter/compare/3.1.4...3.1.5
[3.1.4]: https://github.com/sebastianbergmann/exporter/compare/3.1.3...3.1.4
[3.1.3]: https://github.com/sebastianbergmann/exporter/compare/3.1.2...3.1.3
[3.1.2]: https://github.com/sebastianbergmann/exporter/compare/3.1.1...3.1.2
