# Symbiotic Full (BETA EDITION)
README.RU.md  [РУССКОЕ ОПИСАНИЕ](https://github.com/symbiotic-php/full/blob/master/README.RU.md)
## Installation
```
composer require symbiotic/full
```

## Features

- PSR friendly
- Only a few dependencies (only PSR interfaces and PSR-7 implementation).
- Light weight (440 kb with formatting and comments, [assembly in one 200 kb file](https://github.com/symbiotic-php/full-single/)).
- Optimized to work in symbiosis with other frameworks.
- Multilevel DI container system **(Core <- Application <- Plugin)**, with access to the parent container.
- Virtual file system (proxying static from the package folder to the web).
- The familiar api of the container (laravel/container).
- Blade template engine (stripped down), + the ability to add your own template engine.
- No static collectors (Each package must have already compiled files).
- Deferred routing (only the routes of the requested application are loaded, determined by the prefix-settlement).
- The ability to extend the kernel via Bootstrap and Service Provider.
- Each application has its own container service and services.
- Cache support (PSR-16 Simple Cache) + Cached DI Container.
- Middleware support for intercepting a request before loading the core of the framework (response in 1 ms).

For faster work on hosting without PHP optimization, build in one file [symbiotic/full-single](https://github.com/symbiotic-php/full-single/)


## Description

**The framework was created to simplify the integration of independent small applications into other CMS and frameworks,
as well as to expand the functionality of the composer packages.**

Ideology is a separate ecosystem of small applications for collaboration with other frameworks and convenient
integration of additional functionality.

There are many packages and separately written applications
that deliver useful functionality, have their own business logic and sometimes even have their own separate web interface.

In Laravel packages, in Symfony Bundles, in various CMS in the form of plugins and add-ons, all have their own implementation of routing,
events, caching, etc.
A package written for Laravel to integrate another framework or CMS will be problematic in most cases,
and in some cases impossible due to certain dependencies on the framework.

Application developers themselves have to write adaptations for each framework and CMS,
which creates a lot of problems and does not cover all ecosystems.

**Also, such applications have to do various integrations into the system:**

1. Configure ACL
2. Integrate the necessary scripts to the admin panel and to the site
3. Create query handlers and a structure in the database
4. Configure a bundle with the file system
5. To save settings and configuration

**Such applications include:**

- Single Page Applications
- Text editors and their plugins with multiple levels of dependency (plugin for plugin)
- Media Handlers
- Various optimizers and compressors
- Applications for administrative work with files and databases
- Chat bots, messengers, widgets
- Integration API components, OAuth authorization providers
- Hosting administration and monitoring tools, analytical tools
- Landing pages and other micro applications ....

**The framework is optimized to work with a large amount of applications, as well as to work as a subsystem for
the main framework.**

Each application is a composer package,
with an additional description directly in the composer.json file.

## Run


```php
// If you are already using the framework, then you need to enable the symbiosis mode in the config
// In this mode of operation, the framework will respond only to requests related to it and will not block work for "other" requests.
$config['symbiosis'] = true;
```

#### Initialization
The framework is attached from the composer directly to your index.php.
```php
$basePath = dirname(__DIR__);// root folder of the project

include_once $basePath . '/vendor/autoload.php';

include $basePath.'/vendor/symbiotic/full/src/symbiotic.php';

// Then the initialization code and the work of another framework can go on when the symbiosis mode is enabled...
//.... $laravel->handle();


```

### Advanced method with its own configuration

```php

$basePath = dirname(__DIR__);// root folder of the project

include_once $basePath. '/vendor/autoload.php';

$config = include $basePath.'/vendor/symbiotic/full/src/config.sample.php';

//.. Redefining the configuration array

// Basic construction of the Core container
$core = new \Symbiotic\Core\Core($config);

/**
 * When installing the symbiotic/full package, a cached container is available
 * Initialization in this case occurs through the Builder:
 */
$cache = new Symbiotic\Cache\FilesystemCache($config['storage_path'] . '/cache/core');
$core = (new \Symbiotic\Core\ContainerBuilder($cache))
    ->buildCore($config);
    
// Starting request processing
$core->run();

// Then the initialization code and the work of another framework can go on when the symbiosis mode is enabled...
// $laravel->handle();

```

## Package scheme for the framework

**The minimum scheme of the application description in the composer.json file:**

```json
{
  "name": "vendor/package",
  "require": {
    // ...
  },
  "autoload": {
    // ...
  },
  "extra": {
    "symbiotic": {
      "app": {
        // Application ID
        "id": "my_package_id",
        // Routing provider
        "routing": "\\MyVendor\\MySuperPackage\\Routing",
        // Basic namespace for application controllers
        "controllers_namespace": "\\MyVendor\\MySuperPackage\\Http\\Controllers"
      }
    }
  }
}

```

### Full scheme of package for the framework

```json
{
  "name": "vendor/package",
  "require": {
    // ...
  },
  "autoload": {
    // ...
  },
  // Adding a description of the package for the symbiotic
  "extra": {
    "symbiotic": {
      // Package ID  
      "id": "my_super_package",
      
      // Application description, the package may not have an application section
      "app": {
        // Application ID, specified without the prefix of the parent application
        "id": "image_optimizer",
        // ID of the parent application (optional)
        "parent_app": "media",
        // Application name, used in the application list and menu
        "name": "Media images optimizer",
        // Routing class (optional)
        "routing": "\\MyVendor\\MySuperPackage\\Routing",
        // Basic namespace for controllers (optional)
        "controllers_namespace": "\\Symbiotic\\Develop\\Controllers",
        // Application providers (optional)
        "providers": [
          "MyVendor\\MySuperPackage\\Providers\\AppProvider"
        ],
        // Application container class (optional)
        // Heir from \\Symbiotic\\App\\Application
        "app_class": "MyVendor\\MySuperPackage\\MyAppContainer"
      },
      // Folder with static relative to the package root (will be accessible via the web) (optional)
      "public_path": "assets",
      // Folder with templates and other resources (not accessible via the Web) (optional)
      "resources_path": "my_resources",
      
      // Framework Core Extensions

      // Bootstrappers (optional)
      "bootstrappers": [
        "MyVendor\\MySuperPackage\\CoreBootstrap"
      ],
      // Providers (optional)
      "providers": [
        "MyVendor\\MySuperPackage\\MyDbProvider"
      ],
      // Exclusion of kernel providers (optional)
      "providers_exclude": [
        // Exclusion of providers from downloading
        // For example, with two packages of the same library, it allows you to exclude unnecessary
      ],
      // Event subscribers (optional)
      "events": {
        "handlers": {
          "Symbiotic\\Form\\FormBuilder": "MyVendor\\MyApp\\Events\\FilesystemFieldHandler",
          "Symbiotic\\Settings\\FieldTypesRepository": "MyVendor\\MyApp\\Events\\FieldsHandler",
          "Symbiotic\\UIBackend\\Events\\MainSidebar": "MyVendor\\MyApp\\Events\\Menu"
        }
      },
      //Package settings fields (optional)
      "settings_fields": [
        {
          "title": "Fields group 1",
          "name": "group_1",
          "collapsed": 0,
          "type": "group",
          "fields": [
            {
              "label": "Field 1",
              "name": "filed_name_1",
              "type": "text"
            },
            {
              "label": "Select 1",
              "name": "select_1",
              "type": "select",
              "variants": {
                "value1" :"title1",
                "value12" :"title2"
              }
            },
            {
              "label": "Boolean checkbox",
              "name": "debug",
              "description": "Debug mode",
              "type": "boolean"
            }
          ]
        }
      ],
      // Default settings (optional)
      "settings": {
        "filed_name_1": "demo_value",
        "select_1": "value12",
        "debug": "0"
      },
      // Console commands (optional)
      "commands": {
        "worker": "MyVendor\\MyApp\\Commands\\Worker",
        "stop": "MyVendor\\MyApp\\Commands\\Stop"
      }
    }
  }
}

```

When configuring the application, you can not specify paths for statics and resources, then default paths will be defined.:
- public_path = assets
- resources_path = resources

Templates should always be in the `/view/` directory in the resources folder!

## Types of packages

All packages for the framework can be divided into several logical categories:

- Application or plugin
- Component (any composer package that needs settings or working with resources)
- Core extension (replaces or adds key core components of the framework)
- Static package (design theme, package with public files for the web)

**Any package can combine all of the above.**

## Sample file structure of the package
There is no clear mandatory structure, you can use any one.
If you are making an application based on the composer package (library), to avoid confusion,
it is recommended to put all the code for the application in the `src/Symbiotic` folder.

```text
vendor/
   -/my_vendor
      -/my_package_name
           -/assets          - Public files
                -/js
                -/css
                -/...
           -/resources       - Resources
                -/views      - View templates
                -/...
           -/src             - php code
               -/Http
                   -/Cоntrollers
                   -/...
               -/Services
                ...
               -/Routing.php
          -/composer.json
```