---
layout: doc
title: Create a blog
section: customization
since: 0.6.0
---

You can easily create a blog with Parvula.
Some themes already provide a blog layout but let's try to make our own layout.

# Use the right hierarchy

Before creating a layout, we will create some posts. We do not want our posts to appear in the menu thus we can create a folder beginin with `_`. In the example it will be `_posts`.

The parent page of those posts need to be named as the folder, `_posts.md`. Because the filename begin with `_` it will be hidden, to see this page in the menu we force the visibility with `hidden: false`.

We can then create some pages inside our folder. The structure in `data/pages` should be like that:

```_
├── _posts
│   ├── my-super-first-post.md
│   └── how-i-discovered-parvula.md
└── _posts.md
```

# Create our blog layout

Add a file in your layout folder ([for more info about layout](layout)) for example `blog.html`.

We can list our children pages (our posts in fact).

```php
<?php
$children = $page->getChildren();

// Check if there is children
if ($children):
	// Order children by date
	usort($children, function (Page $p1, Page $p2) {
		return $p1->getDateTime() < $p2->getDateTime();
	});
	foreach ($children as $child): ?>
		<h4 class="post-title"><?= $child->title ?> (<?= $this->e($child, 'pageDateFormat') ?>)</h4>
		<a href="<?= $baseUrl . $child->slug ?>">Read more</a>
	<?php endforeach; ?>
<?php else: ?>
	<em>No post for the moment...</em>
<?php endif; ?>
```

Voilà, use this new layout with `layout: blog` in `_posts.md` and you have your mini blog ready !
