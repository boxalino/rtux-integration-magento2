<?php declare(strict_types=1);
namespace BoxalinoClientProject\BoxalinoIntegration\Controller;

/**
 * Custom router for the Boxalino Narrative dynamically rendered pages
 * Manages the default match logic
 * (for ex: based on the constants defined, there should exist a controller for rtux_narrative_dynamic)
 *
 * ex: router that matches a request path like <store-url>/self::BOXALINO_INTEGRATION_ROUTE_MATCH/<campaign-parameter-value>
 */
class Dynamic extends \Boxalino\RealTimeUserExperience\Controller\AbstractRouter
{

    /**
     * @required constant (used in abstract)
     */
    const BOXALINO_INTEGRATION_ROUTER_CONTROLLER = "narrative";

    /**
     * @required constant (used in abstract)
     */
    const BOXALINO_INTEGRATION_ROUTER_ACTION = "dynamic";

    /**
     * route (ex: <store-url>/BOXALINO_INTEGRATION_ROUTE_MATCH/..)
     */
    const BOXALINO_INTEGRATION_ROUTE_MATCH = "campaign";

    /**
     * optional: the parameters set as request parameters for the API request
     */
    const BOXALINO_NARRATIVE_REQUEST_PARAMETER = "campaign";

    /**
     * Match the router path against the logic the landing pages url are being generated
     *
     * @return boolean
     */
    protected function matchPath() : bool
    {
        if(in_array(self::BOXALINO_NARRATIVE_REQUEST_PARAMETER, array_keys($this->request->getParams())))
        {
            return false;
        }

        $path = $this->_requestPath;
        if (count($path) != 2) {
            return false;
        }

        if (empty($path[0]) || $path[0] != self::BOXALINO_INTEGRATION_ROUTE_MATCH || empty($path[1])) {
            return false;
        }

        return true;
    }

    /**
     * Return the parameters that has to be set on the request
     * "campaign" parameter is dynamic per your integration use-case
     * these parameters are used in TPO and narrative for dynamic content matching
     *
     * @return array
     */
    protected function getParams() : array
    {
        return [self::BOXALINO_NARRATIVE_REQUEST_PARAMETER => $this->_requestPath[1]];
    }

    /**
     * The module name as declared in the routes.xml
     *
     * @return string
     */
    protected function getIntegrationModuleName(): string
    {
        return "rtux";
    }

}
