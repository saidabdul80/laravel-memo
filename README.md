# Laravel Memo

A simple Laravel package for managing memos with support for approvers and customizable configurations.

## Table of Contents

- [Installation](#installation)
- [Configuration](#configuration)
- [Usage](#usage)
- [Routes](#routes)
- [Contributing](#contributing)
- [License](#license)

## Installation

To install the package, run the following command:

```bash
composer require saidabdulsalam/laravel-memo
```
After installation, register the service provider in your config/app.php (if you're not using package auto-discovery):

```
'providers' => [
    // Other service providers...
    Saidabdulsalam\LaravelMemo\MemoServiceProvider::class,
],
```

### Configuration
```php artisan vendor:publish --tag=laravel-memo-config```


## Memo Functionality Documentation

### Usage

#### Creating and Updating Memos
To create or update a memo, utilize the `createOrUpdateMemo` method in the `MemoController`. This method validates the input using `MemoRequest`.

#### Fetching Memos
Retrieve a list of memos using the `index` method in the `MemoController`. This method supports filtering and pagination.

#### Memo Status and Types
Obtain available memo statuses and types via the following endpoints:

* **Memo Statuses:** `GET /memo/statuses`
* **Memo Types:** `GET /memo/types`

### Routes

The following routes are available for memo functionality:

#### Memo Management
* `GET /memos`: List all memos
* `POST /memo`: Create or update a memo

#### Memo Reference Data
* `GET /memo/statuses`: Get all memo statuses
* `GET /memo/types`: Get all memo types

### Contributing

Contributions are welcome! Please:

* Open an issue for suggestions or improvements
* Submit a pull request for code changes

### License

This package is licensed under the MIT License. See the LICENSE file for details.

**Additional Notes**

* Ensure content fits your package's specific functionalities and configuration options.
* Add additional features or setup instructions as necessary.
* Consider creating a LICENSE file in your package if licensing is mentioned in the README.