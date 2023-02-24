# zenstruck/temp-file

[![CI Status](https://github.com/zenstruck/temp-file/workflows/CI/badge.svg)](https://github.com/zenstruck/temp-file/actions?query=workflow%3ACI)
[![codecov](https://codecov.io/gh/zenstruck/temp-file/branch/1.x/graph/badge.svg?token=X5OYYQKR7B)](https://codecov.io/gh/zenstruck/temp-file)

Temporary file wrapper. Created files are deleted at the end of the script.

## Installation

```bash
composer require zenstruck/temp-file
```

## API

### Create `TempFile`'s

```php
use Zenstruck\TempFile;

// create empty file with random name (in /tmp)
$file = TempFile::new();

// create empty file with random filename, with extension (in /tmp)
$file = TempFile::withExtension('txt');

// create file with specific filename (in /tmp)
$file = TempFile::withName('my-file.txt'); // creates empty file
$file = TempFile::withName('my-file.txt', 'some content'); // creates file with string content
$file = TempFile::withName('my-file.txt', \fopen('some/file.txt', 'r')); // creates file with resource as content
$file = TempFile::withName('my-file.txt', new \SplFileInfo('some/file.txt')); // creates file from existing file (existing file is copied)

// create for existing file
$file = TempFile::new('some/file.txt'); // note: will be deleted at the end of the script

// create with string content
$file = TempFile::for('some contents');

// create with resource
$file = TempFile::for(\fopen('some/file.txt', 'r'));

// create from another file (existing file is copied)
$file = TempFile::for(new \SplFileInfo('some/file.txt'));

// create image
$image = TempFile::image(); // temporary 10x10 image with 'jpg' as the extension
$image = TempFile::image(100, 50); // customize the dimensions
$image = TempFile::image(type: 'gif'); // customize the image type
$image = TempFile::image(name: 'my-image.png'); // customize the file name
```

### Using `TempFile`'s

```php
/** @var \Zenstruck\TempFile $file */

$file->contents(); // string - the file's contents
$file->refresh(); // self - clearstatcache() on the file (refreshes metadata)
$file->delete(); // self - delete the file

// is instance of \SplFileInfo
$file->getMTime(); // int - last modified timestamp
$file->getExtension(); // string - file extension
```

### Long-Running Processes

Created `TempFile`'s are automatically deleted at the end of the script by keeping
track of created files and purging with `register_shutdown_function`. If using a
long-running PHP process (like a worker or Swoole/RoadRunner runtime) the files
will not be purged until the process is stopped. This creates a memory leak as the
tracked created files grows in memory. To combat this, you'll need to hook into
some kind of event in your process that enables you to clear these type of leaks
and call `TempFile::purge()` manually.

## Symfony Integration

A simple service is provided to purge `TempFile`'s at the end of each request
and, if using symfony/messenger, after a job is processed.

To use, register the service:

```yaml
# config/packages/zenstruck_temp_file.yaml

services:
    Zenstruck\TempFile\Bridge\Symfony\PurgeTempFiles:
        autoconfigure: true
```
