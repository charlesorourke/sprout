#Sprout

The goal of this framework is for it to be fast, efficient, and very light weight--with only the minimum amount of code loaded at runtime.

Some of the feature goals are:

##General
* Convention over configuration wherever possible with sensible defaults
* Encourage DRY coding principles
* Well-defined coding and documentation standards

##Framework
* Framework, app, and webroot can be independly stored anywhere on the drive
* Namespace-based autoloading
* Regular expression tokenized routing
* Ajax-friendly routes for partials
* Built-in options for User and Auth classes to support basic authentication
  - User-defined roles
  - Cached access tables
  - Simple access checks like $user->is('admin') and/or $user->can('edit_posts')
* Built-in PHP ActiveRecord as the default ORM
* Migration-based schema management
* Command-line utility with executing application tasks and code generation
  - Generation of models and controllers, optionally with predefined actions and
    views, and database schema migrations
  - Database schema management through migrations
  - Documentation generation
  - Executing test
  - User-defined tasks - possibly for release scripts etc.
* RESTful API

##Applications
* Before and after callback options
* Plain PHP view templates with alternate syntax PHP control structures and 
* Really great HTML view helpers for building forms, etc.
* Automatic minifying for HTML, CSS, and JS that can be disabled
* View and asset caching
* Default .gitignore and .htaccess
* App support for plugins/extensions
