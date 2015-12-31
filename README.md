# ![PARVULA](http://i.imgur.com/o8RirUr.png)

> An extremely simple & flexible CMS

#### A quick website from markdown files (or the syntax you want)

![example](http://i.imgur.com/yC4upE7.png)
*Example with the univers template*

### Requirements

* PHP 5.4+
* [Composer](http://getcomposer.org/)
* URL Rewriting (recommended)

### Installation with Composer

1. Download and extract the zip
2. Run `composer install`
3. That's it !

## Usage

### Edit pages

All pages are in `data/pages/`. You can create some sub directories if you want to organise your pages.

The basic configuration file is `data/config/site.yaml`. There is 2 templates bundle by default: "simple" and "univers".

![website](http://i.imgur.com/LgG54UW.png)
*Example with the default template*

### Pages

* Pages must have a title to be listed
* Pages beginning with `_` are *hidden* pages, not listed
* You can order pages with the *index* field

### Administration

![parvula administration](http://i.imgur.com/WtDfVXu.png)

You can edit pages online at **yoursite.com/admin** (admin url can be edited in `plugins/Admin/config.php`).

Don't forget to *chmod 755* `data/pages` and *change the default password* in `plugins/Admin/config.php` !
