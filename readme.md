#Sprout PHP framework

The goal of this framework is for it to be fast, efficient, very light weight, well documented and easy to use.

**THIS FRAMEWORK IS IN THE VERY BEGINNING STAGES OF DEVELOPMENT.** If you're looking for a fun development project, you're in luck. If your looking to start building web applications, keep looking.

##Development goals:
Only the minimum amount of code needed to complete a request should be loaded at runtime.

###General
* Convention over configuration with sensible defaults
* Encourage DRY coding principles
* Well-defined coding and documentation standards

###Framework
* Framework, app, and webroot can be independently stored anywhere on the drive
* Namespace-based autoloading
* Regular expression tokenized routing
* Ajax-friendly routes for partials and non-html requests
* Built-in options for User and Auth classes to support basic authentication
  - User-defined roles
  - Cached access tables
  - Simple access checks like $user->is('admin') and/or $user->can('edit_posts')
* Built-in PHP ActiveRecord as the default ORM
* Migration-based schema management
* Command-line utility for executing application tasks and code generation
  - Generation of models and controllers, optionally with predefined actions/views, database schema migrations, etc.
  - Database schema management through migrations
  - Project documentation generation
  - Running tests
  - User-defined tasks - possibly for release scripts etc.
* RESTful API

###Applications
* Before and after callback options
* Plain PHP view templates with alternate syntax PHP control structures and automatic replacement and escaping of short open tags
* Really great HTML view helpers for building forms, etc.
* Automatic minifying for HTML, CSS, and JS that can be disabled
* View and asset caching
* Default .gitignore and .htaccess
* App support for plugins/extensions