---
layout: doc
title: Plugins
section: customization
---

## The plugin calls

 - `onBootstrap(Parvula $app)`: The first function to be called, useful to get services in $app.
 - `onLoad()`
 - `onRouter(Router $router)`
 - `onDispatch(string $method, string $uri)`
 - `onUri(string $uri)`: ex: `/pictures/islands?test` //TODO check
 - `onSlug(string $slug)`: ex: `pictures/islands`
 - `onPage(Page $page)`: When the page mapped to the slug is loaded
 - `onPreRender(string $layout)`: Before the layout is rendered
 - `onPostRender(string $output)`: After the layout is rendered
 - `onEnd()`:
 - `on404(Page $page)`:
