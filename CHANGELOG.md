# Changelog

## [Unreleased]

### Added
- CsvToTable and DbBulkInsert can now have `->dummy()` runs.
- ChunkExtractor `->getFiles()` now takes an optional parameter which
  adds support for ZIP files containing multiple directory levels.

### Fixed
- CsvToTable now allows CSV files to be empty or have malformed
  header. File is then skipped.

## [0.1.0] – 2024-04-18
### Added
- API for sinks to implement. CRUD for stage 1 data import.
- API for mass insertion of DB records for really huge dumps.
- API for mapping CSV files to DB columns.
- API for creating and extracting archives for chunks.
- API for listing destination tables. Used to get an overview of its
  schemas.
- API call for getting chunk ID based on file name.
- Console command for creating skeleton sinks.
