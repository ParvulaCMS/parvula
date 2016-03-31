---
layout: doc
title: Scripting
section: advanced
since: 0.7.0
---

Scripting is possible since Parvula 0.7.0. Scripts are located in `./Parvula/bin/`.

Scripts can be useful to debug or to seed a database.

To run a script the syntax is (in your parvula root folder): `php index.php <script name> [args...]`

# Available scripts

### Install
Allow you to install remote themes or plugins from the web.

Usage: `php index.php install <plugin or theme> <URL or Path (*.zip)>`

Example: `php index.php install plugin https://github.com/bafs/SuperPlugin/archive/master.zip`

### Doctor
Doctor is a script to check your configuration and create a basic report to see if everything is all right.

Run `php index.php doctor` to get the report.

### Query
A basic script to create query to Parvula without the web interface.

`query [method] <path>`

Example: `php index.php query GET /api/0/pages/home`


# Add an easy way to run scripts

We will create an alias for `php index.php`. So we will be able to use `./parv`.

You can create a new file in your root directory (eg. `parv`) and add

```php
#!/usr/bin/env php
<?php require 'index.php';
```

If your are on Linux or OS X, make it executable
```
chmod +x parv
```

You can now run scripts with parv like this: `./parv install theme http://site.com/theme.zip`.


# Create your own script

Simple create a new php file in `./Parvula/bin/` and it will be accessible with `php index <name of your script> [args...]`.

You can use `$argv` (array) to get the arguments.
