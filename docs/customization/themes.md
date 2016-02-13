---
layout: doc
title: Theming
section: customization
---

# Introduction

Parvula themes are completely PHP based, you don't need to learn a new syntax. It uses [Plates](http://platesphp.com/) to render themes.

> Plates is a native PHP template system that’s fast, easy to use and easy to extend. It’s inspired by the excellent Twig template engine and strives to bring modern template language functionality to native PHP templates.

# Structure

Themes are located in the `themes` folder. Each theme is in a separate folder and can have multiple layouts.

The minimum structure is :

```_
themes                       // Parvula themes
└── myTheme                  // Theme folder
    ├── _layouts             // Theme's layouts
    │   ├── default.html     // Default layout
    └── theme.yaml           // Theme configuration
```

A theme must have, at least, one default layout named `default.html`. Layouts must be located in `_layouts` (for basic themes, layouts can be located at the root folder).

Each theme must also have a [configuration file](#configuration-themeyaml), `theme.yaml`.

A more standard example can be :

```_
themes
└── superTheme
    ├── _includes
    │   ├── footer.html
    │   ├── head.html
    │   └── header.html
    ├── _layouts
    │   ├── _base.html
    │   ├── default.html
    │   ├── home.html
    │   ├── page.html
    ├── style
    │   └── main.css
    └── theme.yaml
```

## Name convention

 - **Files**: As for [pages](./pages), files beginning with a leading `_` will not be listed as a layout (`$theme->getLayouts()`).
 - **Folders**: Folders beginning with a leading `_` will be registered as a *Plates* folder and then can be accessed easily. Check the [layout documentation](./layout.html) for more information.

# Configuration (*theme.yaml*)

Theme configuration sets some theme parameters. Configuration file is in the [YAML format](https://en.wikipedia.org/wiki/YAML).

```yaml
name: Enlightenment          # Theme name
description: My super theme  # Theme description (optional)
author: Erasmus Darwin       # Author (optional)
homepage: https://github.com/BafS/parvula # Homepage url (optional)
```
