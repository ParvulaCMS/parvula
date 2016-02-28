---
layout: doc
title: Page
section: content
---

{:.alert .alert-warning}
This page is incomplete

Each page is divided into two.

- The first part is the [*front matter*](#front-matter-metadata)
- The second, the [*content*](#content).

A page must have front matter, while content is optional.


# Front matter (metadata)

Front matter is an easy way to add meta information to your page. By default, Parvula use the **YAML** format for the front matter (it is possible to choose your own parser; check [how to change your parsers](parser)).

The front matter is placed on the top of each page and delimited from the content by `---`.

Example:

```yaml
---
title: My page
---

# Content
```

## Required fields

<!-- // pas required ? -->

{:.table .table-striped}
| Field    | Description   |
| -------- | ------------- |
| `title`  |  `title` is a required field for each page. If none, title is converted from the file name |
| `slug`   |  `slug` is a required field for each page. If none, slug is converted from the file name (`a-z0-9-_+/`) |

<!-- // TODO -->
<!-- OR title = filename -->

If no title, title value will be lisp-case converted file name.
`why-i-use-parvula` will become `Why I Use Parvula`.

<!-- `why-i_use_parvula` will become `Why I use parvula` ??. -->


## Predefined fields

{:.table .table-striped}
| Field    | Default value | Description   |
|----------|---------------|---------------|
| `layout` | `home`        | The layout file to use. In the `_layouts` folder of themes. `home` layout is required in each theme |
| `date` | *creation time*       | Date of the creation in timestamp |
| `hidden` | `false`       | If the page is *hidden*. Can still be seen from the url but will not appear in the menu |
| `secret` | `false`       | If the page is *secret*. The page will not be accessible (either by url or  the menu) |

### File name

Both `hidden` and `secret` fields can be avoided if you correctly name your files.

 - If a file or folder begins with `_`, the field `hidden` will be set at `true`.
 - If a file or folder begins with `.`, the field `secret` will be set as `true`.

 - date -> date of the file // TODO
 - description
 -

## User fields

Any type of field can be added to the metadata and then be used by the theme/layout via php.

For example, if you add a field `author: Alan Turing` you can access this field via :

```php
...
<li>Author: <?= $page->author ?></li>
...
```

## Auto generated fields

- `slug` the slug matches the file path (`/dir/file` will match `/dir/file.md`) MUST BE UNIQUE
<!-- // Can be overridden ?? -->

# Content

The page's content comes just after the front matter. The content can be empty if required.

By default, Parvula supports markdown and html content but it can be customized easily (check [parser](#)).

```yaml
---
title: My super page
---

# My title

This is my **content**
```

## Sections

Parvula allows you to use section in content to divide your content.

```yaml
---
title: My super page
---

# Main content

---
panel
---
# I am the panel content
```

And can be accessed from the theme with

```php
...
<div class="panel">
    <?= $page->getSection('panel') ?>
</div>
...
```
(or directly from `<?= $page->sections->{'panel'} ?>`)


## Misc

### Date

Each page has a date. The default value is the page creation in timestamp.

PHP has the `DateTime` object and can understand those formats : ...

To print the date, simply use `DateTime` like that:

```php
<?= (new DateTime($page->date))->format(DateTime::RFC822); ?>
```

See the PHP doc know [the predefined formats](https://secure.php.net/manual/en/class.datetime.php)

---

---

slug The token to appear in the tail of the URL, or
url The full path to the content from the web root.
If neither slug or url is present, the filename will be used.



Sort

index then date
