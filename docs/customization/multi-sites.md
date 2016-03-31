---
layout: doc
title: Multi-sites
section: customization
since: 0.7.0
---

Since the version 0.7.0, Parvula can handle multi-sites.

The idea is to share Parvula engine between sites. Each site will just need the "personal" folders (data and static).

# Create a multi-site configuration

Create a new folder per site and copy past from Parvula the `data` and `static` folders and the `.htaccess` and `robots.txt` files.

You must now create `index.php` in this new site folder to use Parvula:

```php
<?php
define('_ROOT_',        '../parvula/'); // Path to Parvula (absolute or relative)
require_once _ROOT_ . 'index.php';
```

That's it !

The structure of your sites is then:

```_
├── myCompagnySite
│   ├── .htaccess
│   ├── data
│   ├── index.php
│   ├── robots.txt
│   └── static
├── myBlog
│   ├── .htaccess
│   ├── data
│   ├── index.php
│   ├── robots.txt
│   └── static
...
```
