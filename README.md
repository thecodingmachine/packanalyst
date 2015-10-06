Packanalyst
===========

A PHP package analyzer for Composer/Packagist.
This is the code of the http://packanalyst.com website.

Requirements
------------

Packanalyst requires a MongoDB database and an ElasticSearch database.

Install
-------

- Clone the application from the Git repository
- Run `php composer.phar install`

Configuring Packanalyst
-----------------------

Packanalyst is an application based on [Mouf 2](http://mouf-php.com). After installing, you can
configure the application by opening http://[yourserver]/[app_path]/vendor/mouf/mouf.

- Create a user / password to access the Mouf UI.
- In the Mouf UI, click on "Project > Edit configuration"
- Edit each parameter (usually, the default parameter will be OK).

In case of troubles, refer to the [Mouf installation guide](http://mouf-php.com/packages/mouf/mouf/doc/installing_mouf.md)

Initializing the database
-------------------------

Once Packanalyst is configured, you must set up the databases (MongoDB and ElasticSearch indexes are configured at this step).

- Init the databases: `./console.php reset`

Loading the database with data
------------------------------

The `./console.php` is a CLI based interface to Packanalyst that lets you load some or all packages from Packanalyst. 

Here is a list of some common commands:

- `./console.php run`: Runs the fetching of ALL packages from Packanalyst. This is a VERY long process (it will take
about a month), and therefore, is only meant to be fully run on Packanalyst production server. You can still use
this command on your local development environment to fetch a few packages to perform tests.
The run command accepts parameters:
	- `./console.php run --package="mouf/mouf"` will load only *mouf/mouf* package (useful for testing)
	- `./console.php run --retry` will force retrying packages that were considered in error
	- `./console.php run --force` will force reloading a package, even if it has not been modified since last check
- `./console.php reset`: deletes all data and restores indexes
- `./console.php get-scores`: retrieves the number of downloads from each package from Packagist
- `./console.php force-refresh` will mark each package for "force retrying" on the next "run"

MongoDB implementation details
------------------------------

MongoDB item collection:

```js
{
	"name": "FQDN",
	"inherits": [ FQDN1, FQDN2... ],
	"globalInherits": [ FQDN1, FQDN2... ], // inherits + inherits of parents, recursively
	"type": "class",
	"packageName": "packagename",
	"packageVersion": "version",
	"phpDoc": "doc class",
	"refresh": bool // Set to true to force refresh
}
```

```
index on: packageName + packageVersion
index on: name
index on: inherits
index on: globalInherits
```

MongoDB package collection:

```js
{
	packageName: ""
	version: ""
	type: ""
	releaseDate: date
	downloads: int
	favers: int
}
```

Packanalyst uses Grunt
-------------------------
Here is the documentation : [Grunt documentation](http://gruntjs.com/)

Quick use :

1. First install NodeJS and add it to your PATH
2. Go to `src/views`, here are your `Gruntfile.js` & `package.json`. Download your dependencies by using command : `npm install`
3. Now you can use grunt by using `grunt` or `grunt dev`
