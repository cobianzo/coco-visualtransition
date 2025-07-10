TODO
===

- Make it work with group sections (problems so far, so i've restricted to make it work only with <div>)- Add z-index 1 and see if it works.
- Add option to change height in mobile or disable the visual transition in mobile at least
- add e2e tests and more phpunit tests.
- create option to validate the plugin with my server, so we can create the pro version of the plugin.
- Eslint doesnt apply prettier formatting (ie, if a line doesnt end with semicolon, it doest detect the error, and doesnt add it on save)
- Add help hints to explain the difference between % and px. Change the controls for UnitControl

WHAT IS THIS PROJECT
===

This plugin is meant to add new attributes and contols to the core block `core/group`. These attributes allow
the user to add a visual fret, based on a pattern, to the top of the group container. This fret clips the group container,
making it "melt" with the previous container.

HOW IT WORKS
===

1) Adds the new attributes to the core/block using Gutenberg js.
2) Depending on the selected pattern, includes inline css for every group having a pattern selected.
3) The inline css includes a svg with an id, which is associated to the core/group as a mask with clip-path or mark-image.
4) We use an SVG generator in php, which dyanmicaly generates a svg with the pattern, and includes it in the page.
We use the development pattern 'factory' for it.

DETAILS
===
All visual transitions, or shapes, must be defined in `patterns.json`.
They must be included in the `inc/svg-generators/class-svg-generator-factory.php`,and the child class must be created.
There are different ways to define a visual transition:
- with the field 'pattern' of `pattern.json`, which defines the svg path points of the sape
- overwriting the method generate_svg, in the php class of the svg pattern. ie inc/svg-generators/class-waves-svg.php
- overwriting the method generate_points, "  ".

TO ADD NEW PATTERNS
===
- Use an SVG editor, like Boxy https://boxy-svg.com/app
- Set viewBox to 0 0 100 100.
- Create an horizontal line, from 0 to 100 in the x axis.
- Copy and paste the shape in your VSCode editor.
- Retrieve the value inside of d="". The fist vertex must be an 'L', not an 'M', and start by 0 for the x.
- Better if the right edge x arrives to 120, so it compensate that the mask has negative x boundaries.
- Copy the path points into the key 'pattern' in `patterns.json`.
- Set the key 'scale' to 100.
- If you want, you can use the placeholders {x_size} and {y_size}, and even{2*x_size} in the pattern.

## TO ADD A MORE CUSTOMIZED NEW PATTERN (several masks with transparencias)

- For more control on the pattern, you can create your own SVG with more complex mask paths, including more than one.
- See the example of the duomask-slope-1.
- In this case the SVG is created totally in the PHP, so we can add more than one path.
- Create a new key in `patterns.json`, and set the key 'type' to 'custom'.
- Create a new class in inc/svg-generators/custom-patterns/
- Reference this new class in `inc/svg-generators/class-svg-generator-factory.php`.
- Overwrite the method generate_svg, and return the svg code.
- follow inc/svg-generators/custom-patterns/class-duomask-slope-1.php as an example.

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

# PHPSTAN

@TODO:
I had problems creating a centralized php with types that I can reuse and import.
I was not able to make it work, so sometimes I need to repeat relative complex types in every file.
Iused scanFiles in phpstan.neon, added the lines in composer.json, cleared the cache...

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

...

## TRANSLATIONS (localization)

To create the .pot with all tranlatable strings, get into the container with
`npm run cli bash`
and
```
cd wp-content/plugins/coco-visualtransition
wp i18n make-pot . languages/coco-visualtransition.pot --domain=coco-visualtransition
```
To make the translations you can use Poedit, opening `coco-visualtransition-es_ES.po`, and update the catalogue from the .pot ( Translation > Update from POT File ).
Then translate what you want. To make the translations you can use Poedit, but I like to open the .po with VSCode, and translate the strings with IA, under the promt 'for every empty msgstr, set the translation from msgid into italian.'.
Then, to create the .mo files, we need to run
```
wp i18n make-mo ./languages/
```
it takes a while to be created.
And for the `.json`, needed for the typescript tranlations:
```
wp i18n make-json ./languages/
```

# Useful tools to develop shapes

Helps to design the path
https://boxy-svg.com/app

Helps to undesrstand every point of the path.
https://svg-path-visualizer.netlify.app/
