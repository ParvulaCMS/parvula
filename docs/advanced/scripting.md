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

### Doctor
Doctor is a script to check your configuration and create a basic report to see if everything is all right.

Run `php index.php doctor` to get the report.

### Query
A basic script to create query to Parvula without the web interface.

`query [method] <path>`

Example: `php index.php query GET /api/0/pages/home`

# Create your own script

Simple create a new php file in `./Parvula/bin/` and it will be accessible with `php index <name of your script> [args...]`.

You can use `$argv` (array) to get the arguments.
