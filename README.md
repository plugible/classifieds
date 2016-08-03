[![Build Status](https://travis-ci.org/kadimi/starter.svg?branch=master)](https://travis-ci.org/kadimi/starter)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/kadimi/starter/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/kadimi/starter/?branch=master)

# WordPress starter plugin

## Require other plugins

The plugin can require other plugins using the `require_plugin` method in the `init` method, the `require_plugin` method first parameter should be the plugin slug although in some circumstances the plugin name works too.

###Example:

```php
/**
 * Initializes plugin
 */
protected function init() {
	// ...
	// ...
	$this->require_plugin( 'titan-framework' );
	$this->require_plugin( 'Advanced Custom Fields' );
	// ...
	// ...
}
```

## Code Sniffing

The PHPCS ruleset included has the following specifications:

- It uses the WordPress standard
- It includes CSS, JavaScript and PHP files
- It excludes files under `inc/external/`
- It excludes files under any folder called `vendor`

The command you need is:

```bash
phpcs --standard=codesniffer.ruleset.xml
```
