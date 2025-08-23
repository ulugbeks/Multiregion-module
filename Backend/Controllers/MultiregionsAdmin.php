<?php

namespace Okay\Modules\OkayCMS\Multiregions\Backend\Controllers;

use Okay\Admin\Controllers\IndexAdmin;
use Okay\Modules\OkayCMS\Multiregions\Backend\Helpers\BackendMultiregionsHelper;
use Okay\Modules\OkayCMS\Multiregions\Backend\Requests\BackendMultiregionsRequest;

class MultiregionsAdmin extends IndexAdmin
{
    public function fetch(
        BackendMultiregionsHelper $backendMultiregionsHelper,
        BackendMultiregionsRequest $backendMultiregionsRequest
    ) {
        $filter = $backendMultiregionsHelper->buildFilter();
        $this->design->assign('current_limit', $filter['limit']);

        // Обработка POST запросов
        if ($this->request->method('post')) {
            $ids = $backendMultiregionsRequest->postCheck();

            switch ($backendMultiregionsRequest->postAction()) {
                case 'delete':
                    $backendMultiregionsHelper->delete($ids);
                    break;
                    
                case 'enable':
                    foreach ($ids as $id) {
                        $subdomain = $backendMultiregionsHelper->getSubdomain($id);
                        if ($subdomain) {
                            $subdomain->enabled = 1;
                            $backendMultiregionsHelper->update($subdomain);
                        }
                    }
                    break;
                    
                case 'disable':
                    foreach ($ids as $id) {
                        $subdomain = $backendMultiregionsHelper->getSubdomain($id);
                        if ($subdomain) {
                            $subdomain->enabled = 0;
                            $backendMultiregionsHelper->update($subdomain);
                        }
                    }
                    break;
            }
        }

        // Получаем данные
        $subdomainsCount = $backendMultiregionsHelper->countSubdomains($filter);
        list($filter, $pagesCount) = $backendMultiregionsHelper->makePagination($subdomainsCount, $filter);
        $subdomains = $backendMultiregionsHelper->findSubdomains($filter);
        $keyword = isset($filter['keyword']) ? $filter['keyword'] : '';

        // Передаем в шаблон
        $this->design->assign('subdomains_count', $subdomainsCount);
        $this->design->assign('pages_count', $pagesCount);
        $this->design->assign('current_page', $filter['page']);
        $this->design->assign('keyword', $keyword);
        $this->design->assign('subdomains', $subdomains);

        $this->response->setContent($this->design->fetch('multiregions.tpl'));
    }
}