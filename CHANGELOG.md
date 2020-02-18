# Changelog

All notable changes to `searchable-resource` will be documented in this file.

## 1.0.0 - 2020-02-16

- Initial Release

## 1.0.1 - 2020-02-16

- Bug Fixes and Add `labeled` method to auto-format query options.

## 1.0.2 - 2020-02-17

- `Request` removed from `AbstractQuery` method parameters as all static methods have been 
removed from queries with the exception of `make` so queries can be applied using 
`tap` in other usages.

- `SearchableBuilder` class will instantiate classes that extend 
`AbstractQuery` with the `app()` helper instead of static make method.

- `Validatable` interface removed from `AbstractQuery` so queries can be 
applied without interacting with input. Implement the interface `ValidatableQuery` to regain 
the functionality. 

## 1.0.3 - 2020-02-17

- Added `ProvidesOptions` interface to allow queries to append their options to the response. 

## 1.0.4 - 2020-02-17

- Refactored `getOptions` method signature to `options` and added `with` method.

## 1.0.5 - 2020-02-18

- Added `when` conditional callback method.

## 1.0.6 - 2020-02-18

- Update Query.stub to `getOptions` method signature to `options`

## 1.0.6 - 2020-02-18

- Add `FormatsOptions` contract and `OptionsFormatter` class.

## 1.0.7 - 2020-02-18

- Refactor `OptionsFormatter` class.

## 1.0.8 - 2020-02-18

- Add `tap` alias of (when true) method and insure invokable classes can be used with `when`.
- Rename SearchableResourceBuilder to SearchableBuilder (name too long).
