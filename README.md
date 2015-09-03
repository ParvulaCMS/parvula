# ![PARVULA](http://i.imgur.com/R2niLC2.png)

#### A quick website from markdown files (or the syntax you want)

![example](http://i.imgur.com/dvi75Mq.png)
*Example with the fullpage template*

### Requirements

* PHP 5.4+
* URL Rewriting (recommended)
* [Composer](http://getcomposer.org/) is recommended

### Installation with Composer

1. Download and extract the zip
2. Run `composer install`
3. That's it !

Without composer, download [php-markdown](http://michelf.ca/projects/php-markdown/) and put Michelf directory in root.

## Usage

### Edit pages

All pages are in `data/pages/`. You can create some sub directories if you want to organise your pages.

The basic configuration file is `data/site.conf.php`. There is 2 templates bundle by default: "simple" and "fullpage".

![website](http://i.imgur.com/P3Fp24p.png)
*Example with the default template*


### Pages

* Pages must have a title to be listed
* Pages beginning with `_` are *hidden* pages, not listed
* You can order pages with the *index* field


### Administration

![parvula administration](http://i.imgur.com/WtDfVXu.png)

You can edit pages online at **yoursite.com/admin/** (admin url can be edited in `plugins/Admin/config.php`).

Don't forget to *chmod 755* `data/pages` and *change the default password* in `plugins/Admin/config.php` !
