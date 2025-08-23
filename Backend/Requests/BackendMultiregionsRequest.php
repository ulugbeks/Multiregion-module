<?php

namespace Okay\Modules\OkayCMS\Multiregions\Backend\Requests;

use Okay\Core\Modules\Extender\ExtenderFacade;
use Okay\Core\Request;

class BackendMultiregionsRequest
{
    private $request;
    
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function postSubdomain()
    {
        $subdomain = new \stdClass();
        $subdomain->id = $this->request->post('id', 'integer');
        $subdomain->subdomain = $this->request->post('subdomain', 'string');
        $subdomain->city_name = $this->request->post('city_name', 'string');
        $subdomain->city_nominative = $this->request->post('city_nominative', 'string');
        $subdomain->city_genitive = $this->request->post('city_genitive', 'string');
        $subdomain->city_dative = $this->request->post('city_dative', 'string');
        $subdomain->city_accusative = $this->request->post('city_accusative', 'string');
        $subdomain->city_instrumental = $this->request->post('city_instrumental', 'string');
        $subdomain->city_prepositional = $this->request->post('city_prepositional', 'string');
        $subdomain->enabled = $this->request->post('enabled', 'integer', 0);
        $subdomain->position = $this->request->post('position', 'integer');

        return ExtenderFacade::execute(__METHOD__, $subdomain, func_get_args());
    }

    public function postSeoPatterns()
    {
        $seoPatterns = $this->request->post('seo_patterns', 'array');
        return ExtenderFacade::execute(__METHOD__, $seoPatterns, func_get_args());
    }

    public function postCheck()
    {
        $check = (array) $this->request->post('check');
        return ExtenderFacade::execute(__METHOD__, $check, func_get_args());
    }

    public function postAction()
    {
        $action = $this->request->post('action');
        return ExtenderFacade::execute(__METHOD__, $action, func_get_args());
    }

    public function postPositions()
    {
        $positions = $this->request->post('positions');
        return ExtenderFacade::execute(__METHOD__, $positions, func_get_args());
    }
}