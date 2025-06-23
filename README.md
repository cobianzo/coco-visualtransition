TODO
===

- Include frontend inline style and svg to show the result of the visualtransition
- add the option to move the group up to merge with the previous group.
- Use masks
- Include option to load the pattern from a svg file.

- localization

WHAT IS THIS PROJECT
===

This plugin is meant to add new attributes and contols to the core block core/group. These attributes allow
the user to add a visual fret, based on a pattern, to the top of the group container. This fret clips the group container,
making it "melt" with the previous container.

HOW IT WORKS
===

1) Adds the new attributes to the core/block using Gutenberg js.
2) Depending on the selected pattern, includes inline css for every group having a pattern selected.
3) The inline css includes a svg with an id, which is associated to the core/group as a mask with clip-path or mark-image.
4) We use an SVG generator in php, which dyanmicaly generates a svg with the pattern, and includes it in the page.
We use the development pattern 'factory' for it.

DEVELOPMENT
===
Dependencies
- v18.20.3
- npm 10.7.0
- composer 2.7.9
- install wp-env globally (when I run it locally it takes too long to load).

`npm run up`
this will also install a folder /wordpress for better development.

or, the first time use the global package of wp-env if it doesnt work
`npm run upglobal`
WordPress development site started at http://localhost:8666
WordPress test site started at http://localhost:8667
MySQL is listening on port 54868
MySQL for automated testing is listening on port 54878
> Use `docker ps | grep mysql` to know the port at anytime.

## Use CLI

`wp-env run cli`
`wp-env run cli bash`

### Use CLI for DB connection (MySQL/MariaDB)

The raw connection would be (replacing the port with the one created by wp-env):

`mysql -h127.0.0.1 -P54868 -uroot -p`

Using DB CLI
`wp-env run cli wp db cli`

To know more info, which can be used to connect from a DB Client.

```
wp-env run cli wp config get DB_HOST   # Host is 127.0.0.1
wp-env run cli wp config get DB_NAME   # Name is wordpress
wp-env run cli wp config get DB_USER   # User is root
wp-env run cli wp config get DB_PASSWORD   # Password is password
And the port you'll have to find out with
> `docker ps | grep mysql`
```

Simple way to export and import DB into the root of the project
`wordpress.sql`:

```>export db
sh ./bin/export-db.sh
```
```>import db
sh ./bin/import-db.sh
```

### Use WP CLI

`wp-env run cli wp get option siteurl`

# PHPCS

Installed Alleys PHPCS standards, which uses WordPress VIP.
Installed PHPStan too.
Both work ok, check composer.json scripts to run the phpcs, phpcbf and phpstan commands.
Check AI-AGENT.md for more info.

## commands
```
composer run lint,
composer run format,
composer analyze
npm run cs .
npm run cbf .
```

# PHPUNIT

```
npm run test:php
npm run test:php:watch
```
it uses wp-env run tests-wordpress ...

Following the last PHPUnit installation working version: https://github.com/cobianzo/wp-env-sidebar-related-article/
PHPUnit 9.4.

Important, we need to use php 8.3 in wp-env, so we can run the package
`wp-env run tests-wordpress` which works perfectly out of the box.

The watch version of the phpunit run works like charm!!

If run teh tests outside the container, it's still not tested.

packages:
- `phpunit/phpunit`: ! important, version 9 max, or it will be incompatible with function inside teh tests.
Then we can access locally o inside the container to wp-content/plugins/coco-visualtransition/vendor/bin/phpunit
- `yoast/phpunit-polyfills` it must be installed, and `wp-env run tests-wordpress` finds it automatically. When installed, it install phpunit, as it depends on it, but the version 12. We need to install phpunit ourselves, the version 9, so there are no incompatibilites.
- `spatie/phpunit-watcher`: for the phpUnit watcher, ran with `npm run test:php:watch`.
- ~~wp-phpunit/wp-phpunit~~: not needed, all the bootstrap is handled by `wp-env run tests-wordpress`

# TESTS PHP

## Useful WP CLI commands

