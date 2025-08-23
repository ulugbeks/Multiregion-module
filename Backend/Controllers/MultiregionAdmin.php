<?php

namespace Okay\Modules\OkayCMS\Multiregions\Backend\Controllers;

use Okay\Admin\Controllers\IndexAdmin;
use Okay\Core\EntityFactory;
use Okay\Modules\OkayCMS\Multiregions\Entities\SubdomainsEntity;
use Okay\Modules\OkayCMS\Multiregions\Entities\SubdomainSeoEntity;
use Okay\Modules\OkayCMS\Multiregions\Helpers\CityDeclension;
use Okay\Modules\OkayCMS\Multiregions\Helpers\SeoProcessor;

class MultiregionAdmin extends IndexAdmin
{
    public function fetch(EntityFactory $entityFactory)
    {
        $subdomainsEntity = $entityFactory->get(SubdomainsEntity::class);
        $subdomainSeoEntity = $entityFactory->get(SubdomainSeoEntity::class);
        $cityDeclension = new CityDeclension();
        
        $subdomain = new \stdClass();
        $seoPatterns = [];
        
        // Получаем ID поддомена
        $id = $this->request->get('id', 'integer');
        
        if (!empty($id)) {
            $subdomain = $subdomainsEntity->get($id);
            if ($subdomain) {
                // Получаем SEO шаблоны
                $patterns = $subdomainSeoEntity->find(['subdomain_id' => $subdomain->id]);
                foreach ($patterns as $pattern) {
                    $seoPatterns[$pattern->page_type] = $pattern;
                }
            }
        }
        
        // Обработка сохранения
        if ($this->request->method('post') && !empty($_POST)) {
            // Получаем данные из POST
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
            
            $seoData = $this->request->post('seo_patterns');
            
            // Автоматическое определение склонений
            if ($this->request->post('auto_declension')) {
                $cases = $cityDeclension->getCases($subdomain->city_name);
                $subdomain->city_nominative = $cases['nominative'];
                $subdomain->city_genitive = $cases['genitive'];
                $subdomain->city_dative = $cases['dative'];
                $subdomain->city_accusative = $cases['accusative'];
                $subdomain->city_instrumental = $cases['instrumental'];
                $subdomain->city_prepositional = $cases['prepositional'];
            }
            
            // Валидация
            $error = '';
            if (empty($subdomain->subdomain)) {
                $error = 'empty_subdomain';
            } elseif (!preg_match('/^[a-z0-9-]+$/', $subdomain->subdomain)) {
                $error = 'invalid_subdomain';
            } elseif (empty($subdomain->city_name)) {
                $error = 'empty_city_name';
            }
            
            if ($error) {
                $this->design->assign('message_error', $error);
            } else {
                if (empty($subdomain->id)) {
                    // Добавление нового
                    $subdomain->position = $subdomainsEntity->count() + 1;
                    $insertedId = $subdomainsEntity->add($subdomain);
                    $subdomain->id = $insertedId;
                    
                    $this->design->assign('message_success', 'added');
                } else {
                    // Обновление существующего
                    $subdomainsEntity->update($subdomain->id, $subdomain);
                    $this->design->assign('message_success', 'updated');
                }
                
                // Сохраняем SEO шаблоны
                if (is_array($seoData) && !empty($subdomain->id)) {
                    foreach ($seoData as $pageType => $patterns) {
                        // Сохраняем только если есть хотя бы одно заполненное поле
                        if (!empty($patterns['meta_title_pattern']) || 
                            !empty($patterns['meta_description_pattern']) || 
                            !empty($patterns['meta_keywords_pattern']) || 
                            !empty($patterns['h1_pattern']) || 
                            !empty($patterns['description_pattern'])) {
                            
                            $subdomainSeoEntity->updatePattern($subdomain->id, $pageType, $patterns);
                        }
                    }
                }
                
                // Перезагружаем данные для отображения
                $subdomain = $subdomainsEntity->get($subdomain->id);
                $patterns = $subdomainSeoEntity->find(['subdomain_id' => $subdomain->id]);
                $seoPatterns = [];
                foreach ($patterns as $pattern) {
                    $seoPatterns[$pattern->page_type] = $pattern;
                }
                
                // Кнопка "Применить и выйти"
                if ($this->request->post('apply_and_quit', 'integer', 0) == 1) {
                    header('Location: ' . $this->config->root_url . '/backend/index.php?module=OkayCMS.Multiregions.MultiregionsAdmin');
                    exit();
                }
            }
        }
        
        // Типы страниц для SEO
        $pageTypes = [
            'main' => 'Главная страница',
            'category' => 'Категории товаров',
            'product' => 'Страницы товаров',
            'brand' => 'Страницы брендов',
            'blog' => 'Страницы блога',
            'page' => 'Информационные страницы'
        ];
        
        // Доступные переменные
        $availableVariables = SeoProcessor::getAvailableVariables();
        
        // Примеры городов
        $citySuggestions = [
            'Москва', 'Санкт-Петербург', 'Новосибирск', 'Екатеринбург',
            'Нижний Новгород', 'Казань', 'Челябинск', 'Омск', 'Самара'
        ];
        
        $this->design->assign('subdomain', $subdomain);
        $this->design->assign('seo_patterns', $seoPatterns);
        $this->design->assign('page_types', $pageTypes);
        $this->design->assign('available_variables', $availableVariables);
        $this->design->assign('city_suggestions', $citySuggestions);
        
        // Отладка - показываем что сохранилось
        if ($this->request->method('post')) {
            $this->design->assign('debug_info', [
                'subdomain_id' => $subdomain->id,
                'seo_data_received' => $seoData,
                'patterns_saved' => $seoPatterns
            ]);
        }
        
        $this->response->setContent($this->design->fetch('multiregion.tpl'));
    }
}