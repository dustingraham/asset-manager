Asset Manager
=============
Support functionality for assetic to integrate into Laravel 4 and handle configuration.

Composer
--------

Add to your composer.json
    "require": {
        "dustingraham/asset-manager": "dev-master"
    },

Run `composer update`

Laravel
-------

Once installed, add the service provider and alias to `app/config/app.php`.

    'providers' => array (
        ...
        'Aja\AssetManager\ServiceProvider',
        ...
    ),
    'aliases' => array (
        ...
        'Asset' => 'Aja\AssetManager\Facades\Asset',
        ...
    ),

Config
------

Publish the config file.

    > php artisan config:publish dustingraham/asset-manager

Usage
-----

Compile production assets using

    > php artisan aja:asset


