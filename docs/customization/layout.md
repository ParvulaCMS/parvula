---
layout: doc
title: Layout
section: customization
---

{:.alert .alert-warning}
This page is being written

# Introduction

Parvula themes are completely PHP based, you don't need to learn a new syntax. It uses [Plates](http://platesphp.com/) to render themes.

> Plates is a native PHP template system that’s fast, easy to use and easy to extend. It’s inspired by the excellent Twig template engine and strives to bring modern template language functionality to native PHP templates.


Page [variables](./variables) can be accessed via


## Global variables

{:.table .table-striped}
| Variable     | Description |
| --------     | -------- |
| `$baseURL`   | Get the relative path from the CMS root folder |
| `$themeUrl`  | Get the relative path from the current theme folder |
| `$baseURL`   | Get the relative path from the root folder |
| `$site` | Returns the site information (object). Useful to get information like the site title or the site description |
| `$page` | Returns the current page (Page) |
| `$config` | User config file (user.yaml) |
| `$content` | Alias for `$page->content` |


### Functions

{:.table .table-striped}
| Variable     | Description |
| --------     | -------- |
| `$pages($listHidden=false)`     | Function to get an array of all pages, a boolean can be passed to list hidden pages |
| `$plugin($name)`   | Function to get a specific plugin, the plugin name must be passed |
| `$__time__()` | Function to get the elapse time passed since the CMS bootstrap, useful to benchmark |


## Functions

Theme functions in Plates are accessed using the $this pseudo-variable.

Variables can be escaped via `$this->e($var)`.

Variables can be escaped via `$this->e($var, 'strip_tags|strtoupper|escape')`.

<!-- // Image structure -->

## Layout

Date: DateTime

<!-- DateTime::RFC850 -->

https://secure.php.net/manual/fr/datetime.formats.compound.php

https://secure.php.net/manual/fr/class.datetime.php
