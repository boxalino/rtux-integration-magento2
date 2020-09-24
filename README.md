# Boxalino Real Time User Experience (RTUX) Integration - Magento2

## Introduction
This repository is to be used as a sample on how to integrate and define desired Boxalino features.
The integration is managed by the client (also known as *integrator*).
Boxalino can provide further documentation and assistance upon request.

Included functionalities (with samples of templates):
1. Autocomplete integration (javascript)
2. Search integration
3. PDP recommendations (upsell, crosssell, related, etc)
4. Navigation
5. CMS pages/blocks


The repository is being updated with new guidelines & scenario.
The repository has dependencies for *Boxalino RTUX API PHP* library and the *Boxalino RTUX Magento2* plugin.

https://github.com/boxalino/rtux-api-php

https://github.com/boxalino/rtux-magento2

## Integration
This repository is **not** subject to Boxalino maintenance on client setup.
This means, the guidelines are supposed to be integrated in a repository/plugin maintainted & developed by the client`s team.

This repository can be deployed for testing Boxalino features or in order to prepare your own integration.
In order to deploy it as is in a local environment, check the *Setup* steps bellow.

## Prerequisites
A full data sync is required in order to activate the Boxalino client data index.
For more information, check the [Boxalino Exporter for Magento2](https://github.com/boxalino/exporter-magento2)

## Setup (for local/testing purposes)
1. Add the plugin to your project via composer 
``composer require boxalino/rtux-integration-magento2``

2. Activate the plugin (Magento2 command)
``php bin/magento module:enable BoxalinoClientProject_BoxalinoRealTimeUserExperienceIntegration``

3. Flush cache (Magento2 command)
``php bin/magento cache:flush``

4. Import the content of the [complete-guidelines JSONs](https://github.com/boxalino/rtux-integration-magento2/wiki/JSON-Guidelines-(Complete)) in Boxalino Intelligence Admin; test, save & publish.
    * Layout Blocks (Boxalino Intelligence Admin >> Marketing >> Layout Blocks)
    * Template Resources (Boxalino Intelligence Admin >> Advanced >> Template Resources)
    * Narratives  (Boxalino Intelligence Admin >> Marketing >> Narratives)

5. Update the Boxalino Framework configurations

6. Run the exporter (is declared as dependency via composer.json)  
```php bin/magento indexer:reindex boxalino_exporter```

## Documentation

The latest documentation is available upon request.
Specific feature integration is available in the [repository Wiki](https://github.com/boxalino/rtux-integration-magento2/wiki).
Magento2 integration elements documentation is available on the [Magento2 Framework repository Wiki](https://github.com/boxalino/rtux-magento2/wiki)
Core API documentation is available on the [RTUX API PHP wiki](https://github.com/boxalino/rtux-api-php/wiki)

## Contact us!

If you have any question, just contact us at support@boxalino.com
