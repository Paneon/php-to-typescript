Changelog
======

# 2.2.0

- Add PHP 8.1+ enum support with new `#[TypeScript]` attribute
  - String-backed enums → TypeScript enums with string values
  - Int-backed enums → TypeScript enums with numeric values
  - Unit enums → TypeScript enums without values
  - Optional `asUnion: true` parameter to output string literal union types instead
- Add `ParserService::getContent()` method that auto-detects classes and enums
- Add `ParserService::getEnumContent()` method for parsing enum files
- Add `ParserService::setUseEnumUnionType()` for global union type default
- Add import statement generation for referenced types
  - `SourceFileCollection` to register files and calculate relative import paths
  - `setSingleFileMode(true)` to disable imports when all types are in one file
  - `setSourceFiles()` and `setCurrentTargetDirectory()` for multi-file generation
- Deprecate `#[TypeScriptInterface]` in favor of unified `#[TypeScript]` attribute (backward compatible)

# 2.1.0

- Add optional type syntax and export keyword support

# 2.0.0

- **BC BREAK**: Remove docblock/Doctrine-style `@TypeScriptInterface` annotation capabilities (use PHP 8 attributes instead).
- **BC BREAK**: Remove/trim dependencies as part of the rewrite/cleanup.

# 1.1.0

- Add support for psalm array syntax as well: `@var array<int>`

# 1.0.0

- Initial Release of the Parser
