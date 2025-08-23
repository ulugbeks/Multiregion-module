<?php

namespace Okay\Modules\OkayCMS\Multiregions\Backend\Helpers;

use Okay\Core\EntityFactory;
use Okay\Core\Modules\Extender\ExtenderFacade;
use Okay\Core\Request;
use Okay\Modules\OkayCMS\Multiregions\Entities\SubdomainsEntity;
use Okay\Modules\OkayCMS\Multiregions\Entities\SubdomainSeoEntity;
use Okay\Modules\OkayCMS\Multiregions\Helpers\CityDeclension;

class BackendMultiregionsHelper
{
    private $subdomainsEntity;
    private $subdomainSeoEntity;
    private $request;
    private $cityDeclension;

    public function __construct(
        EntityFactory $entityFactory,
        Request $request
    ) {
        $this->subdomainsEntity = $entityFactory->get(SubdomainsEntity::class);
        $this->subdomainSeoEntity = $entityFactory->get(SubdomainSeoEntity::class);
        $this->request = $request;
        $this->cityDeclension = new CityDeclension();
    }

    public function getSubdomainValidateError($subdomain)
    {
        $error = '';

        if (empty($subdomain->subdomain)) {
            $error = 'empty_subdomain';
        } elseif (!preg_match('/^[a-z0-9-]+$/', $subdomain->subdomain)) {
            $error = 'invalid_subdomain';
        } elseif (empty($subdomain->city_name)) {
            $error = 'empty_city_name';
        } else {
            // Проверка уникальности
            $existing = $this->subdomainsEntity->findOne(['subdomain' => $subdomain->subdomain]);
            if ($existing && $existing->id != $subdomain->id) {
                $error = 'subdomain_exists';
            }
        }
        
        return ExtenderFacade::execute(__METHOD__, $error, func_get_args());
    }

    public function getSubdomain($id)
    {
        $subdomain = $this->subdomainsEntity->get((int)$id);
        
        if ($subdomain) {
            // Получаем SEO шаблоны
            $subdomain->seo_patterns = $this->subdomainSeoEntity->find(['subdomain_id' => $subdomain->id]);
        }
        
        return ExtenderFacade::execute(__METHOD__, $subdomain, func_get_args());
    }

    public function prepareUpdate($subdomain)
    {
        return ExtenderFacade::execute(__METHOD__, $subdomain, func_get_args());
    }

    public function update($subdomain)
    {
        $this->subdomainsEntity->update($subdomain->id, $subdomain);
        ExtenderFacade::execute(__METHOD__, $subdomain, func_get_args());
    }

    public function prepareAdd($subdomain)
    {
        // Устанавливаем позицию
        $subdomain->position = $this->subdomainsEntity->count() + 1;
        return ExtenderFacade::execute(__METHOD__, $subdomain, func_get_args());
    }

    public function add($subdomain)
    {
        $insertedId = $this->subdomainsEntity->add($subdomain);
        return ExtenderFacade::execute(__METHOD__, $insertedId, func_get_args());
    }

    public function buildFilter()
    {
        $filter = [];
        $filter['page'] = max(1, $this->request->get('page', 'integer'));

        if ($filter['limit'] = $this->request->get('limit', 'integer')) {
            $filter['limit'] = max(5, $filter['limit']);
            $filter['limit'] = min(100, $filter['limit']);
            $_SESSION['multiregions_num_admin'] = $filter['limit'];
        } elseif (!empty($_SESSION['multiregions_num_admin'])) {
            $filter['limit'] = $_SESSION['multiregions_num_admin'];
        } else {
            $filter['limit'] = 25;
        }

        $keyword = $this->request->get('keyword', 'string');
        if (!empty($keyword)) {
            $filter['keyword'] = $keyword;
        }

        return ExtenderFacade::execute(__METHOD__, $filter, func_get_args());
    }

    public function countSubdomains($filter)
    {
        $subdomainsCount = $this->subdomainsEntity->count($filter);
        return ExtenderFacade::execute(__METHOD__, $subdomainsCount, func_get_args());
    }

    public function makePagination($subdomainsCount, $filter)
    {
        if ($this->request->get('page') == 'all') {
            $filter['limit'] = $subdomainsCount;
        }

        if ($filter['limit'] > 0) {
            $pagesCount = ceil($subdomainsCount / $filter['limit']);
        } else {
            $pagesCount = 0;
        }

        $filter['page'] = min($filter['page'], $pagesCount);

        return [$filter, $pagesCount];
    }

    public function findSubdomains($filter)
    {
        $subdomains = $this->subdomainsEntity->mappedBy('id')->find($filter);
        
        // Подсчитываем SEO шаблоны для каждого
        foreach ($subdomains as $subdomain) {
            $subdomain->seo_patterns_count = $this->subdomainSeoEntity->count(['subdomain_id' => $subdomain->id]);
        }
        
        return ExtenderFacade::execute(__METHOD__, $subdomains, func_get_args());
    }

    public function delete($ids)
    {
        ExtenderFacade::execute(__METHOD__, null, func_get_args());
        
        // Удаляем SEO шаблоны
        foreach ($ids as $id) {
            $this->subdomainSeoEntity->deleteBySubdomain($id);
        }
        
        // Удаляем поддомены
        $this->subdomainsEntity->delete($ids);
    }

    public function autoDeclension($cityName)
    {
        return $this->cityDeclension->getCases($cityName);
    }

    public function updateSeoPatterns($subdomainId, $seoPatterns)
    {
        if (!is_array($seoPatterns)) {
            return;
        }

        foreach ($seoPatterns as $pageType => $pattern) {
            $this->subdomainSeoEntity->updatePattern($subdomainId, $pageType, $pattern);
        }
    }
}