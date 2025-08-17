# Laravel Memo Package

A configurable Laravel package for managing memos, designed to be integrated into existing Laravel applications.

## Table of Contents

- [Installation](#installation)
- [Configuration](#configuration)
- [API Endpoints](#api-endpoints)
- [Events](#events)

## Installation

Install the package using Composer:

```bash
composer require saidabdulsalam/laravel-memo
```

Publish the configuration file:

```bash
php artisan vendor:publish --tag=memo-config
```

Run the database migrations:

```bash
php artisan migrate
```

## Configuration

The configuration file (`config/memo.php`) allows you to customize the package to fit your application's needs.

| Option                        | Type      | Default                     | Description                                                                                             |
| ----------------------------- | --------- | --------------------------- | ------------------------------------------------------------------------------------------------------- |
| `pagination_length`           | `Integer` | `15`                        | The number of memos to display per page.                                                                |
| `members_models`              | `Array`   | `[App\User::class]`         | An array of models to be used for users/members.                                                        |
| `office_model`                | `Array`   | `[App\Office::class]`       | The model to be used for offices.                                                                       |
| `name`                        | `Array`   | `['full_name']`             | The column name(s) to be used for the user's full name.                                                 |
| `department_model`            | `String`  | `App\Models\Department::class` | The model to be used for departments.                                                                   |
| `role_column_name`            | `String`  | `'role_id'`                 | The column name on the user model that stores the user's role.                                          |
| `user_department_id_column`   | `String`  | `'department_id'`           | The column name on the user model that stores the user's department ID.                                 |
| `user_office_id_column`       | `String`  | `'office_id'`               | The column name on the user model that stores the user's office ID.                                     |
| `members_models_filters`      | `Array`   | `null`                      | An array of filters to apply when fetching members (e.g., `[['type' => 'staff']]`).                     |
| `load_routes`                 | `Boolean` | `true`                      | Whether to load the package's routes automatically.                                                     |

## Load Routes with Helper

To load the routes automatically, you can use the `memoRoutes()` helper function:

```php
memoRoutes();
```

## API Endpoints

All endpoints are prefixed with `/memo`.

| Method   | URI                  | Action               | Description                                |
| -------- | -------------------- | -------------------- | ------------------------------------------ |
| `GET`    | `/boot`              | `boot`               | Fetches initial data for the frontend.     |
| `GET`    | `/all`               | `index`              | Fetches a paginated list of memos.         |
| `POST`   | `/`                  | `createOrUpdateMemo` | Creates or updates a memo.                 |
| `GET`    | `/statuses`          | `memoStatus`         | Fetches the available memo statuses.       |
| `GET`    | `/types`             | `memoTypes`          | Fetches the available memo types.          |
| `GET`    | `/members`           | `members`            | Fetches the list of members/users.         |
| `POST`   | `/reject`            | `rejectMemo`         | Rejects a memo.                            |
| `POST`   | `/approve`           | `approveMemo`        | Approves a memo.                           |
| `POST`   | `/make_comment`      | `saveComment`        | Adds a comment to a memo.                  |
| `PUT`    | `/comment/{id}`      | `updateComment`      | Updates a comment.                         |
| `DELETE` | `/comment/{id}`      | `deleteComment`      | Deletes a comment.                         |
| `GET`    | `/departments`       | `departments`        | Fetches the list of departments.           |
| `DELETE` | `/{id}`              | `deleteMemo`         | Deletes a memo.                            |

## Events

The package fires the following events:

- `MemoApproved`
- `MemoComment`
- `MemoCreated`
- `MemoRejected`
- `MemoUpdated`
