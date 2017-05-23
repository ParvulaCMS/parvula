<p align="center">
  <a href="https://parvulacms.github.io" target="_blank"><img width="630" src="http://i.imgur.com/igAQPza.png" alt="Parvula"></a>
</p>

<p align="center">
  <img src="https://img.shields.io/badge/PHP-5.6+-brightgreen.svg?style=flat-square" alt="PHP 5.6+">

  <a href="https://packagist.org/packages/bafs/parvula">
    <img src="https://img.shields.io/packagist/v/bafs/parvula.svg?style=flat-square" alt="version">
  </a>

  <a href="https://travis-ci.org/ParvulaCMS/parvula">
    <img src="https://img.shields.io/travis/ParvulaCMS/parvula.svg?style=flat-square" alt="Build Status">
  </a>

  <a href="https://www.versioneye.com/user/projects/56fcfa82905db1003b29956e">
    <img src="https://www.versioneye.com/user/projects/56fcfa82905db1003b29956e/badge.svg?style=flat-square" alt="Dependencies">
  </a>

  <img src="https://img.shields.io/packagist/l/BafS/parvula.svg?style=flat-square" alt="license">
</p>

> **An extremely simple & flexible CMS** generated from flat files with a complete API

![screenshot](http://i.imgur.com/gsbzwgl.png)
> Parvula with the default galaxy theme

### Requirements

* PHP 5.6+
* [Composer](http://getcomposer.org/)
* URL Rewriting (recommended)

### Installation with Composer

Parvula can be install with this single line :

```bash
composer create-project bafs/parvula cms
```

where `cms` is the destination folder. Please check the [docs](https://parvulacms.github.io/docs/getting-started/installation.html) if you need more details.

## Quick start

**Please read the [documentation](https://parvulacms.github.io) for more information**

### Edit pages

All pages are located in `data/pages/`. You can also create sub directories to better organize your pages.

The basic configuration file is `data/config/site.yml`.
There are 2 bundled themes by default: "simple" and "galaxy".

### Pages

* Pages must have a [title](https://parvulacms.github.io/docs/content/pages.html#front-matter-metadata) to be listed (in the menu)
* Pages beginning with `_` are *hidden* pages, not listed but accessible
* You can order pages with the *index* field
* Each page can have [multiple sections](https://parvulacms.github.io/docs/content/pages.html#sections)

### Administration

![Parvula administration](http://i.imgur.com/WtDfVXu.png)
> Administration plugin

You can edit pages online at **yoursite.com/admin** (admin url can be edited in `plugins/Admin/config.php`).

Don't forget to *chmod 755* `data/pages` and *change the default password* in `data/users/users.php` !
