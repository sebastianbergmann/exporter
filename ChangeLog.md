# ChangeLog

All notable changes are documented in this file using the [Keep a CHANGELOG](https://keepachangelog.com/) principles.

## [7.0.0] - 2025-02-07

### Removed

* This component is no longer supported on PHP 8.2

## [6.3.0] - 2024-12-05

### Added

* Optional constructor argument to control maximum string length

### Deprecated

* Optional argument for `shortenedRecursiveExport()` and `shortenedExport()` to control maximum string length

## [6.2.0] - 2024-12-05

### Added

* [#67](https://github.com/sebastianbergmann/exporter/issues/67): Optional argument for `shortenedRecursiveExport()` and `shortenedExport()` to control maximum string length

### Changed

* [#69](https://github.com/sebastianbergmann/exporter/pull/69): Do not initialize lazy objects during export

## [6.1.3] - 2024-07-03

### Changed

* [#66](https://github.com/sebastianbergmann/exporter/pull/66): Avoid using the Reflection API for some classes
* This project now uses PHPStan instead of Psalm for static analysis

## [6.1.2] - 2024-06-18

### Changed

* [#64](https://github.com/sebastianbergmann/exporter/pull/64): Improve performance of `Exporter::exportString()`
* [#65](https://github.com/sebastianbergmann/exporter/pull/65): Prevent unnecessary calls to `str_repeat()`

### Fixed

* [#62](https://github.com/sebastianbergmann/exporter/issues/62): Do not limit export of arrays by default (to restore BC with versions prior to 6.1.0)

## [6.1.1] - 2024-06-18

### Fixed

* [#61](https://github.com/sebastianbergmann/exporter/issues/61): `count(): Recursion detected` warning triggered

## [6.1.0] - 2024-06-18

### Added

* [#59](https://github.com/sebastianbergmann/exporter/pull/59): Option to limit export of (large) arrays (to speed up PHPUnit)

### Changed

* [#60](https://github.com/sebastianbergmann/exporter/pull/60): Take shortcut when exporting a string

## [6.0.3] - 2024-06-17

### Fixed

* Fixed code coverage metadata

## [6.0.2] - 2024-06-17 [YANKED]

### Changed

* [#58](https://github.com/sebastianbergmann/exporter/pull/58): Remove unnecessary `sprintf()` in hot path

## [6.0.1] - 2024-03-02

### Changed

* Do not use implicitly nullable parameters

## [6.0.0] - 2024-02-02

### Removed

* This component is no longer supported on PHP 8.1

## [5.1.2] - 2024-03-02

### Changed

* Do not use implicitly nullable parameters

## [5.1.1] - 2023-09-24

### Changed

* [#52](https://github.com/sebastianbergmann/exporter/pull/52): Optimize export of large arrays and object graphs

## [5.1.0] - 2023-09-18

### Changed

* [#51](https://github.com/sebastianbergmann/exporter/pull/51): Export arrays using short array syntax

## [5.0.1] - 2023-09-08

### Fixed

* [#49](https://github.com/sebastianbergmann/exporter/issues/49): `Exporter::toArray()` changes `SplObjectStorage` index

## [5.0.0] - 2023-02-03

### Changed

* [#42](https://github.com/sebastianbergmann/exporter/pull/42): Improve export of enumerations

### Removed

* This component is no longer supported on PHP 7.3, PHP 7.4 and PHP 8.0

## [4.0.5] - 2022-09-14

### Fixed

* [#47](https://github.com/sebastianbergmann/exporter/pull/47): Fix `float` export precision

## [4.0.4] - 2021-11-11

### Changed

* [#37](https://github.com/sebastianbergmann/exporter/pull/37): Improve export of closed resources

## [4.0.3] - 2020-09-28

### Changed

* Changed PHP version constraint in `composer.json` from `^7.3 || ^8.0` to `>=7.3`

## [4.0.2] - 2020-06-26

### Added

* This component is now supported on PHP 8

## [4.0.1] - 2020-06-15

### Changed

* Tests etc. are now ignored for archive exports

## [4.0.0] - 2020-02-07

### Removed

* This component is no longer supported on PHP 7.0, PHP 7.1, and PHP 7.2

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

[7.0.0]: https://github.com/sebastianbergmann/exporter/compare/6.3...7.0.0
[6.3.0]: https://github.com/sebastianbergmann/exporter/compare/6.2.0...6.3.0
[6.2.0]: https://github.com/sebastianbergmann/exporter/compare/6.1.3...6.2.0
[6.1.3]: https://github.com/sebastianbergmann/exporter/compare/6.1.2...6.1.3
[6.1.2]: https://github.com/sebastianbergmann/exporter/compare/6.1.1...6.1.2
[6.1.1]: https://github.com/sebastianbergmann/exporter/compare/6.1.0...6.1.1
[6.1.0]: https://github.com/sebastianbergmann/exporter/compare/6.0.3...6.1.0
[6.0.3]: https://github.com/sebastianbergmann/exporter/compare/fe0dca49a60d34440e2f086951952dd13aa9a5d2...6.0.3
[6.0.2]: https://github.com/sebastianbergmann/exporter/compare/6.0.1...fe0dca49a60d34440e2f086951952dd13aa9a5d2
[6.0.1]: https://github.com/sebastianbergmann/exporter/compare/6.0.0...6.0.1
[6.0.0]: https://github.com/sebastianbergmann/exporter/compare/5.1...6.0.0
[5.1.2]: https://github.com/sebastianbergmann/exporter/compare/5.1.1...5.1.2
[5.1.1]: https://github.com/sebastianbergmann/exporter/compare/5.1.0...5.1.1
[5.1.0]: https://github.com/sebastianbergmann/exporter/compare/5.0.1...5.1.0
[5.0.1]: https://github.com/sebastianbergmann/exporter/compare/5.0.0...5.0.1
[5.0.0]: https://github.com/sebastianbergmann/exporter/compare/4.0.5...5.0.0
[4.0.5]: https://github.com/sebastianbergmann/exporter/compare/4.0.4...4.0.5
[4.0.4]: https://github.com/sebastianbergmann/exporter/compare/4.0.3...4.0.4
[4.0.3]: https://github.com/sebastianbergmann/exporter/compare/4.0.2...4.0.3
[4.0.2]: https://github.com/sebastianbergmann/exporter/compare/4.0.1...4.0.2
[4.0.1]: https://github.com/sebastianbergmann/exporter/compare/4.0.0...4.0.1
[4.0.0]: https://github.com/sebastianbergmann/exporter/compare/3.1.2...4.0.0
[3.1.5]: https://github.com/sebastianbergmann/exporter/compare/3.1.4...3.1.5
[3.1.4]: https://github.com/sebastianbergmann/exporter/compare/3.1.3...3.1.4
[3.1.3]: https://github.com/sebastianbergmann/exporter/compare/3.1.2...3.1.3
[3.1.2]: https://github.com/sebastianbergmann/exporter/compare/3.1.1...3.1.2
