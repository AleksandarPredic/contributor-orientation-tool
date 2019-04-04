# contributor-orientation-tool
A WordPress plugin aiming to help new contributors decide which make team/s to contribute to or join at Contributor Day.

## Getting Started
Install [node.js](http://nodejs.org).

	npm install

You are done.

Please not that you need to have [webpack and webpack cli](https://webpack.js.org/guides/installation) globally for ease of use.

### `npm run dev` or `npm run watch`

Use `npm run dev` or `npm run watch` for on–the–fly updates of your code (templates, js, css)...

### `npm run prod`

This will create a `dist` directory for project. **This directory represents distribution package** to zip and install on your WordPress site. 

## Directory Structure

Plese keep in mind that `dist` directory is rebuilt with each `npm dev`, `npm watch` or `npm prod` command.

	dist/ [generated via webpack]
	src/
	├── languages/ [translation files]
	├── src/ [images, stylesheets]
	├────── WPCOTool/ [Namespace root]
	├──────── Admin/
	├──────── assets/ [images, stylesheets]
	├──────── Frontend/
	├── contributor-orientation-tool.php [WordPress plugin main file]
	└── README.txt [WordPress plugin readme file]
	.babelrc
	.editorconfig
	.eslintrc
	.gitignore
	.stylelintrc
	LICENSE
	package.json
	README.md
	webpack.config.js

## Workflow

Workflow for adding new features is as follows:

* Add new issue 
* Fork repository
* Checkout new brancs with name `feature/name-of-the-feature`, usually name of the issue
* Commit with message `#{issue id} Name of the issue - description`
* Push to the forked repository
* Create pull request to develop branch
* Checkout to develop branch
* Fetch upstream [more info](https://help.github.com/en/articles/syncing-a-fork)

Please note: If changing version of the plugin you need to modify version in this files:
* src/README.txt 
* src/src/WPCOTool/Plugin.php
* src/contributor-orientation-tool.php

## Licence

The WordPress Widget Boilerplate is licensed under the GPL v2 or later.

>This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License, version 2, as published by the Free Software Foundation.

>This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

>You should have received a copy of the GNU General Public License along with this program; if not, write to the Free Software Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA

## Developers

Miss a feature? Pull requests are welcome.

Hopefully the project will receive contributions from awesome WordPress Developers throughout the world.
