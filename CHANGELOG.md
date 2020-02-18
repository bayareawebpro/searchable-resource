# Changelog

All notable changes to `searchable-resource` will be documented in this file.

## 1.0.0 - 2020-02-16

- Initial Release

## 1.1.0 - 2020-02-16

- Bug Fixes and Add `labeled` method to auto-format query options.

## 1.1.1 - 2020-02-17

- `Request` removed from `AbstractQuery` method parameters as all static methods have been 
removed from queries with the exception of `make` so queries can be applied using 
`tap` in other usages.

- `SearchableResourceBuilder` class will instantiate classes that extend 
`AbstractQuery` with the `app()` helper instead of static make method.

- `Validatable` interface removed from `AbstractQuery` so queries can be 
applied without interacting with input. Implement the interface `ValidatableQuery` to regain 
the functionality. 
