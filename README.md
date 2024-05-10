# Boxalino Real Time User Experience (RTUX) Integration - Magento2

# SEARCH & NAVIGATION INTEGRATION
The search integration on Magento2 is done with the use of the content of this branch.
More explanation & details into the technical decisions are presented on wiki
https://github.com/boxalino/rtux-integration-magento2/wiki/Search-Context
https://github.com/boxalino/rtux-integration-magento2/wiki/Navigation-Context

> As an integrator, it is your responsibility to identify&fix possible conflicts of this generic Magento2 search-rewrite flow with other active plugins in your project.
> Once the API request is sent, it is expected for you to follow the API response tree of _Magento2 blocks definitions_ (with template, name, type) and follow the rendering flow.
> Use test=1 at all times when developing new integrations with the Boxalino plugins.

## Recommendations
The recommended approach is to include the code presented in this branch in your integration layer.
**Make sure to replace `BoxalinoClientProject_BoxalinoIntegration` and `BoxalinoClientProject\BoxalinoIntegration` with your own project namespace.**

Once the generic/base Magento2 + Boxalino API search is working, you can pursue to make adjustments:
1. Add extensive facets configurations in Intelligence Admin
2. Configure new sorting options in your di.xml
3. Add top facets (changes both in IA narrative layout and in your projects` layout.xml)
4. Add listing injections https://boxalino.atlassian.net/wiki/spaces/BPKB/pages/844857345/Listing+Injections

**You can reach out to Boxalino to load the default narrative & layout blocks for Search and Navigation.**

## Introduction
This repository is provided as guidelines (tutorials) on how to integrate Boxalino Winning Interractions in Magento 2.
The integration is managed by the client. Boxalino can provide further documentation and assistance upon request.

Included functionalities (with samples of templates):
1. Search As you Type (Autocomplete Flyout) integration - Ajax (faster alternative to Server Side)
2. Search result page with facets (refinement filters), pagination and sorting
3. PDP product recommendations (upsell, crosssell, related, etc)
4. Category Product-listing results (a.k.a. Navigation) with facets (refinement filters), pagination and sorting
5. CMS elements pages/blocks
6. Dynamic router (ex: for brand pages or landing pages)

*If you are new to using Boxalino in a Magento 2 environment, please have first a look at our introduction documentation [here](https://boxalino.atlassian.net/wiki/spaces/BPKB/pages/392396801/Magento+2)*

The repository is being updated with new guidelines & scenario.
**Check the wiki page for instructions https://github.com/boxalino/rtux-integration-magento2/wiki**

**We value your feedback and we welcome the community's pull/merge-requests. Thank you.**


## Integration
Boxalino provides several repositories you will need. This one plays the role of the initial environment you will extend with your team.
You also need the [data layer](https://github.com/boxalino/exporter-magento2) and the [framework layer](https://github.com/boxalino/rtux-magento2).
The data layer repository manages the export of your data to Boxalino.
The framework layout is built following Magento 2 practices and provides elements (blocks, observers, helpers, etc) which your integration plugin can re-use and which is maintained by Boxalino.

While the other repositories (data layer and framework layer) will be maintained by Boxalino, this repository will not, as it is your integration environment in which you will work and use what you need and extend what you want.
**Therefore, as this repository is provided as a guideline (tutorial), you will need to integrate it (which means replicate it) in a repository/moduled maintainted&developed by the client's team.**
For this reason, this repository is generally not subject to Boxalino maintenance on client setup.

This repository can be deployed for testing Boxalino features or in order to prepare your own integration.
In order to deploy it as is in a local environment, check the *Setup* steps bellow.

This repository can also be deployed for checking out Boxalino features (as a demo).
By requiring this repository, it will also install other dependencies. Please review the [composer.json](https://github.com/boxalino/rtux-integration-magento2/blob/master/composer.json)
and follow-up with other dependencies deployments guidelines.

**In order to create your own integration layer All At Once - please check the [wiki on Initial Setup (All-At-Once)](https://github.com/boxalino/rtux-integration-magento2/wiki/Initial-Setup-(All-At-Once)).**

**In order to create your own integration layer - please start in the [wiki](https://github.com/boxalino/rtux-integration-magento2/wiki#before-you-start).**


**In order to deploy it for local demo (without Boxalino assistance or a need to maintain the code in a client setup), check the *Setup* steps bellow.**


## Setup (for local/testing purposes)
**ONLY FOLLOW THESE STEPS IF YOU ARE DEPLOYING A LOCAL DEMO, OTHERWISE, GO TO THE [wiki](https://github.com/boxalino/rtux-integration-magento2/wiki#before-you-start).**

1. Add the plugin to your project via composer 
``composer require boxalino/rtux-integration-magento2``

2. Activate the plugin (Magento2 command)
``php bin/magento module:enable BoxalinoClientProject_BoxalinoIntegration``

3. Flush cache (Magento2 command)/ run setup:upgrade / setup:di:compile depending on your environment
``php bin/magento cache:flush``

4. Import the content of the [complete-guidelines JSONs](https://github.com/boxalino/rtux-integration-magento2/wiki/JSON-Guidelines-(Complete)) in Boxalino Intelligence Admin; test, save & publish.
    * Layout Blocks (Boxalino Intelligence Admin >> Marketing >> Layout Blocks)
    * Template Resources (Boxalino Intelligence Admin >> Advanced >> Template Resources)
    * Narratives  (Boxalino Intelligence Admin >> Marketing >> Narratives)

5. Update the Boxalino Framework configurations

6. Run the exporter (is declared as dependency via composer.json)  
```php bin/magento indexer:reindex boxalino_exporter```

## Documentation

Documentation available in the [Magento 2 Integration Introduction](https://boxalino.atlassian.net/wiki/spaces/BPKB/pages/392396801/Magento+2)

More documentation is available on each dependency repository wiki page:
1. [exporter layer](https://github.com/boxalino/exporter-magento2/wiki)
2. [framework layer](https://github.com/boxalino/rtux-magento2/wiki)
3. [integration usecases](https://github.com/boxalino/rtux-integration-magento2/wiki)
4. [API php wiki](https://github.com/boxalino/rtux-api-php/wiki)

## Contact us!

If you have any question, just contact us at support@boxalino.com
