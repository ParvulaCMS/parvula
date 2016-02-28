---
layout: doc
title: Menu
section: customization
---

{:.alert .alert-warning}
This page is being written

# Introduction

Parvula can handle all kind of menu. From the basic flat one to the nested one with multiple levels.

# Basic menu

## A simple menu

This menu will simply list all pages without taking in account the hierarchy.

```php
<nav>
    <ul>
        <?php foreach ($pages() as $myPage) : ?>
        <li>
            <a href="<?= $baseUrl . $myPage->slug ?>"><?= $myPage->title ?></a>
        </li>
        <?php endforeach; ?>
    </ul>
</nav>
```

### A simple with main pages

This menu will list all the *main* pages (pages without a parent).

```php
<nav>
    <ul>
        <?php foreach ($pages() as $myPage) : ?>
        <?php if (!$page->parent) : // Page WITHOUT a parent ?>
        <li>
            <a href="<?= $baseUrl . $myPage->slug ?>"><?= $myPage->title ?></a>
        </li>
        <?php endif; ?>
        <?php endforeach; ?>
    </ul>
</nav>
```

## A sub menu

This menu will list all sub pages (pages with a parent).

```php
<nav>
    <ul>
        <?php foreach ($pages() as $myPage) : ?>
        <?php if ($page->parent) : // Page WITH a parent ?>
        <li>
            <a href="<?= $baseUrl . $myPage->slug ?>"><?= $myPage->title ?></a>
        </li>
        <?php endif; ?>
        <?php endforeach; ?>
    </ul>
</nav>
```

## Advanced menu

## A nested menu with sub pages

This menu will list pages with sub pages.

```php
<nav>
    <ul>
        <?php foreach ($pages() as $myPage) : ?>
        <?php if (!$page->parent) : // Page WITHOUT a parent (main pages) ?>
        <li>
            <a href="<?= $baseUrl . $myPage->slug ?>"><?= $myPage->title ?></a>
            <?php if ($page->getChildren()) : // If the page have children ?>
                <ul>
                <?php foreach ($page->getChildren() as $myPage) : // List children ?>
                    <li><a href="<?= $baseUrl . $myPage->slug ?>"><?= $myPage->title ?></a></li>
                <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </li>
        <?php endif; ?>
        <?php endforeach; ?>
    </ul>
</nav>
```

## A generic nested menu

For a more generic way to create menu (and without any limit of the *deepness*) we need to use the recursion. Hopefully Parvula provide a function to help you to generate any type of menu.

**options**

 - `(string) li`: Will be added in each li tag.
 - `(string) ul`: Will be added in each ul tag.
 - `(callable) liCallback(Page $page)`: Call for each li, the result will be added in the current li.
 - `(int) level`: Level max of recursion.

```php
<nav>
    <?= $this->listPages($pages(), [
        // Call for each 'li'
        'liCallback' => function($myPage) use ($page, $baseUrl) {
            $href = $baseUrl . $myPage->slug;
            return "<a href=\"{$href}\">{$myPage->title}</a>";
        },
        'level' => 3 // Level of deepness
    ]) ?>
</nav>
```
