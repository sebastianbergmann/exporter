# ChangeLog

All notable changes are documented in this file using the [Keep a CHANGELOG](https://keepachangelog.com/) principles.

## [8.1.0] - 2026-05-21

### Changed

* [#57](https://github.com/sebastianbergmann/exporter/issues/57): Preserve `SplObjectStorage` iterator position instead of resetting to first element
* [#91](https://github.com/sebastianbergmann/exporter/pull/91): Make binary string output readable for mostly-printable values

## [8.0.3] - 2026-05-20

### Fixed

* [#90](https://github.com/sebastianbergmann/exporter/issues/90): `Exporter::toArray()` silently drops a private property that is redeclared in a derived class

## [8.0.2] - 2026-04-15

* [#74](https://github.com/sebastianbergmann/exporter/pull/74): Int cast warning when exporting large floats

## [8.0.1] - 2026-04-10

### Changed

* Explicitly handle `NAN`, `INF`, and `-INF` in `exportFloat()`

## [8.0.0] - 2026-02-06

### Removed

* This component is no longer supported on PHP 8.3

## [7.0.3] - 2026-05-20

### Fixed

* [#90](https://github.com/sebastianbergmann/exporter/issues/90): `Exporter::toArray()` silently drops a private property that is redeclared in a derived class

## [7.0.2] - 2025-09-24

### Changed

* Suppress `unexpected NAN value was coerced to string` warning triggered on PHP 8.5

## [7.0.1] - 2025-09-22

### Changed

* Suppress `not representable as an int, cast occurred` warning triggered on PHP 8.5

## [7.0.0] - 2025-02-07

### Removed

* This component is no longer supported on PHP 8.2

[8.1.0]: https://github.com/sebastianbergmann/exporter/compare/8.0.3...8.1.0
[8.0.3]: https://github.com/sebastianbergmann/exporter/compare/8.0.2...8.0.3
[8.0.2]: https://github.com/sebastianbergmann/exporter/compare/8.0.1...8.0.2
[8.0.1]: https://github.com/sebastianbergmann/exporter/compare/8.0.0...8.0.1
[8.0.0]: https://github.com/sebastianbergmann/exporter/compare/7.0...8.0.0
[7.0.3]: https://github.com/sebastianbergmann/exporter/compare/7.0.2...7.0
[7.0.3]: https://github.com/sebastianbergmann/exporter/compare/7.0.2...7.0.3
[7.0.2]: https://github.com/sebastianbergmann/exporter/compare/7.0.1...7.0.2
[7.0.1]: https://github.com/sebastianbergmann/exporter/compare/7.0.0...7.0.1
[7.0.0]: https://github.com/sebastianbergmann/exporter/compare/6.3...7.0.0
