CONTEXT
===

- I am devloping a plugin for WordPress, I am using the development environment of wp-env, where:
```wp-env.json
{
  "core": null,
  "plugins": ["."],
  "phpVersion": "8.1",
  "env": {
		"development": {
			"phpVersion": "8.1",
			"port": 8666
		},
}
```

Other keywords:
- phpcs
- phpstan
- phpunit
- tests e2e with Playwright
- github actions
- husky and precommit

- My plugin will use typescript and it will compile by using wp-scripts package, by wordpress.

- Use the standards of WordPress VIP, creating OOP instead of procedural functions.

- The main folder structure :
```
coco-visualtransition.php
inc/ my php files.
src/ my ts files which compile into /build/
```
