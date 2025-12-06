# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2024-01-01

### Added
- Initial release of CI4 Settings library
- Database-backed settings storage with `settings` table
- `settings()` helper function for easy access to settings
- `feature()` helper function for feature flags management
- Support for multiple data types (string, integer, boolean, json)
- Context-based settings for organizing settings by scope
- Built-in caching for improved performance
- SettingsService class for programmatic access
- SettingModel for database operations
- Database migration for settings table creation
- Comprehensive documentation and examples
- Example files demonstrating common use cases
- PHPUnit configuration for testing

### Features
- Store and retrieve key/value settings
- Feature flag support for gradual rollouts
- Type-safe value storage and retrieval
- Context support for multi-tenant or user-specific settings
- Simple API with helper functions
- Compatible with CodeIgniter 4.x

[1.0.0]: https://github.com/olajideolamide/ci4-settings/releases/tag/v1.0.0
