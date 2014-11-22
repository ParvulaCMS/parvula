Parvula CMS
===========

#### A quick website from markdown files

![website](http://i.imgur.com/jO3KwHI.png)
*Example with the fullpage template*

Requirements
------------
* PHP 5.3+
* URL Rewriting (recommended)
* [Composer](http://getcomposer.org/) is recommended

Installation with Composer
--------------------------
1. Download and extract the zip
2. Run `composer install`
3. That's it !

Without composer, download [php-markdown](http://michelf.ca/projects/php-markdown/) and put Michelf directory in root.


Edit pages
----------
All pages are in `data/pages/`. You can create some sub directories if you want to organise your pages.

The basic configuration file is in `data/site.conf.md`. There is 2 templates bundle by default: "simple" and "fullpage".

![website](http://i.imgur.com/P3Fp24p.png)
*Default template*


Pages
-----
* Pages must have a title to be listed
* Page beginning with `_` are "secret" pages, not listed
* You can order pages with the *index* field


Administration
--------------

![parvula administration](http://i.imgur.com/WtDfVXu.png)

You can edit pages online at **yoursite.com/admin/** (admin url can be edited in Parvula/config.php).

Don't forget to chmod 755 `data/pages` and *change the default password* in `data/admin.conf.php`.
