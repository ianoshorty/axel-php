# AXEL PHP - Axel Accelerated Download Functionality For PHP

[![Codeship Status for ianoshorty/axel-php](https://codeship.com/projects/a0f58ef0-e7b1-0132-651b-4e340869c11f/status?branch=master)](https://codeship.com/projects/82613)
[![Code Climate](https://codeclimate.com/github/ianoshorty/axel-php/badges/gpa.svg)](https://codeclimate.com/github/ianoshorty/axel-php)

## General

The Axel PHP library wraps around the C based [Axel] library. [Axel] performs accelerated downloads from the command line, similar to `wget`.

Axel PHP offers Async Downloads, Sync Downloads and a download Queue. See below for usage.

** _PLEASE NOTE_: This library is under active development and is subject to change at any time. **

## Example Usage

### Sync Download With Complete Callback

```php
$axel = new AxelDownload();
$axel->start($download_address, null, null, function($axel, $status, $success, $error) use ($download_address) {
    echo 'File Downloaded';
    print_r($status);
});
```

### Start Async Download

```php
$axel = new AxelDownload();
$axel->startAsync('http://ipv4.download.thinkbroadband.com/1GB.zip', 'test.zip', '~/');
```

### Get Download Status

```php
$status = $axel->updateStatus();
```

### Cleanup

```php
$axel->clearCompleted()
```

### Axel Managed Download Queue
// TODO

### Version
0.0.2

---

## Installation

### Prerequesites

In order to install RPVR you will need:

 - [LAMP] - A full Lamp stack
 - [Axel] - A linux server with Axel installed

### Install Instructions

**INSTALL VIA DOWNLOAD ONLY - PACKAGIST SUBMISSION COMING SOON**

#### Manual Install
To manually install the package:

  1. `$ sudo apt-get install axel`
  2. Clone or download the repo
  3. // Add instructions

#### Packagist
**INSTALL VIA DOWNLOAD ONLY - PACKAGIST SUBMISSION COMING SOON**

  1. `$ sudo apt-get install axel`
  2. $ composer install axel-php
  3. // Add instructions

---
## Development

Want to contribute? Great! Feel free to get in touch with me and we can collaborate, or fork / pull as you like.

### TODO

-- Full implement callback closure for any time a progress update is made
-- Manage downloads in a queue (Axel)
-- Add to packagist
-- Document

---
## License
The MIT License (MIT)

Copyright (c) 2015 Ian Outterside ([Ian Builds Apps]).

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.

[LAMP]:http://laravel.com/docs/5.0/homestead
[Axel]:http://axel.alioth.debian.org
[Ian Builds Apps]:http://www.ianbuildsapps.com