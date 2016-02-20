---
index: 0
layout: doc
title: Installation
section: getting-started
---

# Requirements

Parvula requires you to have:

 - PHP 5.4+
 - Apache
 - [Composer](http://getcomposer.org/)

# Installing Parvula

## Via Composer *create-project* (recommended)

You can simply run the following command. It will download the last version of Parvula and install its dependencies.

```bash
composer create-project bafs/parvula cms
```

Replace `cms` with the folder name you would like to install it in.

## Manually

Parvula is a *flat files* CMS thus you only need to copy the files and then install the dependencies with composer.

 1. Simply download the [source from github](https://github.com/BafS/parvula/releases) and extract it to your preferred location
 2. Open a terminal, go to the freshly created folder and install the dependencies through `composer install`

# Permissions

This step is required if you want to allow third party plugins or the API functionality to write data.
If you plan to only edit and upload files *by hand*, this step is optional.

The two main folders (`data` and `static`) and all the children should be writable.
(check the [structure of Parvula](/docs/content/structure) for the detailed hierarchy)

```bash
chmod -r 755 data
chmod -r 755 static
```
