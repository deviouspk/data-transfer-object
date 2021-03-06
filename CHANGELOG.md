# Changelog

All notable changes to `data-transfer-object` will be documented in this file

## 3.1.2 - 2019-05-01

- Fixed a bug that would cause non optional properties that have a default value to be required. (bug was caused by caching & the property reset mechanism)

## 3.1.1 - 2019-05-01

- Fixed a bug that could cause mutability of a property on an immutable dto

## 3.1.0 - 2019-05-01

- Constraint Inheritance. Cleaned up code.

## 3.0.0 - 2019-04-23

- Huge rewrite. Completed validation with symfony\validation through annotations. Improved caching.

## 2.3.0 - 2019-04-23

- Nested Validation

## 2.2.0 - 2019-04-19

- Annotations for optional & immutable properties

## 2.1.0 - 2019-04-18

- Added support to override or add attributes on non immutable dto's

## 2.0.1 - 2019-04-15

- Improved error messages

## 2.0.0 - 2019-04-15

- new package release (namespace changed). Optional properties support + major immutability improvements

## 1.8.0 - 2019-03-18

- Support immutability

## 1.7.1 - 2019-02-11

- Fixes #47, allowing empty dto's to be cast to using an empty array.

## 1.7.0 - 2019-02-04

- Nested array DTO casting supported.

## 1.6.6 - 2018-12-04

- Properly support `float`.

## 1.6.5 - 2018-11-20

- Fix uninitialised error with default value.

## 1.6.4 - 2018-11-15

- Don't use `allValues` anymore.

## 1.6.3 - 2018-11-14

- Support nested collections in collections
- Cleanup code

## 1.6.2 - 2018-11-14

- Remove too much magic in nested array casting

## 1.6.1 - 2018-11-14

- Support nested `toArray` in collections.

## 1.6.0 - 2018-11-14

- Support nested `toArray`.

## 1.5.1 - 2018-11-07

- Add strict type declarations

## 1.5.0 - 2018-11-07

- Add auto casting of nested DTOs

## 1.4.0 - 2018-11-05

- Rename to data-transfer-object

## 1.2.0 - 2018-10-30

- Add uninitialized errors.

## 1.1.1 - 2018-10-25

- Support instanceof on interfaces when type checking

## 1.1.0 - 2018-10-24

- proper support for collections of value objects

## 1.0.0 - 2018-10-24

- initial release
