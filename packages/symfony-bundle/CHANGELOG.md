Changelog
======

# 2.2.1

## Fixed

- Fix directory processing when `singleFileMode: true` is enabled

## Improved

- Configuration now accepts both YAML map and list syntax for `interfaces` and `directories`
  - Map syntax: `'path/to/file': { output: 'out/' }`
  - List syntax: `- { path: 'path/to/file', output: 'out/' }`
  - Both syntaxes produce identical results

# 2.2.0

## Added

- **useType option**: Generate TypeScript `type` aliases instead of `interface` declarations. Outputs `.ts` files instead of `.d.ts`.
- **export option**: Add the `export` keyword before type/interface declarations for ES module compatibility.
- **useEnumUnionType option**: Output PHP enums as TypeScript string literal union types (e.g., `type Status = 'active' | 'inactive';`).
- **Enum support**: Automatically process PHP 8.1+ enums annotated with `#[TypeScript]` attribute.
- **CLI options**: Added `--use-type`, `--export`, and `--enum-union-type` flags to both commands.
- **PHPUnit test suite**: Added basic test infrastructure with configuration tests.
- **Single file mode**: Generate all TypeScript types into a single file instead of individual files per class/enum. Configure with `singleFileMode: true` and `singleFileOutput: 'types.ts'` or use `--single-file[=filename]` CLI option.
- **Import resolution**: When using `export: true`, the generator now automatically adds `import` statements for referenced types. This enables proper TypeScript module resolution between generated files.

# 2.0.0

- Update dependency `paneon/php-to-typescript` to `^2.0`
- Remove usage of the deprecated Doctrine `AnnotationReader` (no longer needed)

# 1.0.0

- Publish first version using the Parser in version 1.0.0
- Add configuration option to add directories instead of only singular files
- Add configuration option to require or not require the @TypeScriptInterface annotation
