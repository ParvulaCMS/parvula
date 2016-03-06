---
layout: doc
title: Change your parser
section: customization
---

# Choose an other parser

Parvula is really flexible, let see how you can easily change the parser.

## Use the Json parser [example]

Open `system.yaml` (in `data/config`) and edit the field `headParser` to use the Json parser.

```yaml
# Class to parse pages (must implement ContentParserInterface), can be null
headParser: \Parvula\Core\Parser\Json
```

You can now use Json in the front-matter of your pages. For example :

```yaml
{
  "title": "My page"
}
---

# My content
```

*PS*: Do not forget to change all pages with new Json format. You cannot mix formats.

## Create a new parser [example]

By default Parvula can parse the Json, Yaml en PHP formats and use Yaml as the default format.

Let's add the INI format to parse our *front-matter*.

### Create a new class

In `Parvula/Core/Parser/` create a new file called `Ini.php`. Create the new class `Ini` and implements `ParserInterface`.

`ParserInterface` will force us to implements the methods `decode($iniString)` and `encode($data)`.

```php
<?php
namespace Parvula\Core\Parser;
class Ini implements ParserInterface {
	/**
	 * Parse ini
	 * @param string $input The string to parse
	 * @return array|object Appropriate PHP type
	 */
	public function decode($iniString) {
		return parse_ini_string($iniString); // Will parse front-matter to array
	}

	public function encode($data) { } // Not in this example
}
```

You can now use your new class. Change `headParser` in `system.yaml` to use your yout Ini class.

```yaml
headParser: \Parvula\Core\Parser\Ini
```

You are now able to write your front-matter in the ini format.
