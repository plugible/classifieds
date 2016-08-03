# WordPress starter plugin

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
