# EffectConnect Marketplaces - Magento 2 plugin

[![Latest Version on Packagist](https://img.shields.io/packagist/v/effectconnect/marketplaces-plugin-m2.svg?style=flat-square)](https://packagist.org/packages/effectconnect/marketplaces-plugin-m2)
[![Latest Stable Version](https://poser.pugx.org/effectconnect/marketplaces-plugin-m2/v/stable?style=flat-square)](https://packagist.org/packages/effectconnect/marketplaces-plugin-m2)
[![Total Downloads](https://img.shields.io/packagist/dt/effectconnect/marketplaces-plugin-m2.svg?style=flat-square)](https://packagist.org/packages/effectconnect/marketplaces-plugin-m2)
[![License](https://poser.pugx.org/effectconnect/marketplaces-plugin-m2/license?style=flat-square?style=flat-square)](https://packagist.org/packages/effectconnect/marketplaces-plugin-m2)
[![Monthly Downloads](https://poser.pugx.org/effectconnect/marketplaces-plugin-m2/d/monthly?style=flat-square)](https://packagist.org/packages/effectconnect/marketplaces-plugin-m2)
[![Daily Downloads](https://poser.pugx.org/effectconnect/marketplaces-plugin-m2/d/daily?style=flat-square)](https://packagist.org/packages/effectconnect/marketplaces-plugin-m2)

Use this plugin to connect your Magento 2 webshop with EffectConnect Marketplaces. For more information about EffectConnect, go to the [EffectConnect website](https://www.effectconnect.com "EffectConnect Website").

**Important: before installing this plugin please contact the EffectConnect sales department via +31(0)852088432 and/or sales@effectconnect.com so we can provide you with a new EffectConnect account.**

## Table of Contents
  * [Installation](#installation)
    * *[Composer](#composer)*
  * [Setup module](#setup-module)
    * *[Command-line](#command-line)*

## Installation
Installing the EffectConnect Marketplaces Magento 2 plugin requires multiple steps. Follow the steps below to install the module.

### Install module
Installation of the module can be performed using the composer method (command-line).

#### Composer
1. Install the module using the following command:
```bash
composer require 'effectconnect/marketplaces-plugin-m2'
```

### Setup module
After installing the module, it needs to be setup by the Magento 2 module system. This setup can be achieved using the command-line method. 

#### Command-line method
- When in a production environment, first put in into maintenance mode:
```bash
php bin/magento maintenance:enable
```

- Perform a setup upgrade:
```bash
php bin/magento setup:upgrade
```

- When in a production environment, perform a static-content deploy:
```bash
php bin/magento setup:static-content:deploy [locales]
```

- Perform a cache flush:
```bash
php bin/magento cache:flush
```