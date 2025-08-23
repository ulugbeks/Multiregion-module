{if $subdomain->id}
    {$meta_title = $subdomain->city_name|cat:" - редактирование" scope=global}
{else}
    {$meta_title = $btr->add_subdomain scope=global}
{/if}

<div class="row">
    <div class="col-lg-12 col-md-12">
        <div class="wrap_heading">
            <div class="box_heading heading_page">
                {if !$subdomain->id}
                    {$btr->add_subdomain|escape}
                {else}
                    {$subdomain->city_name|escape}
                {/if}
            </div>
        </div>
    </div>
</div>

{* Вывод сообщений *}
{if $message_success}
    <div class="row">
        <div class="col-lg-12 col-md-12 col-sm-12">
            <div class="alert alert--center alert--icon alert--success">
                <div class="alert__content">
                    <div class="alert__title">
                        {if $message_success=='added'}
                            {$btr->subdomain_add|escape}
                        {elseif $message_success=='updated'}
                            {$btr->subdomain_updated|escape}
                        {else}
                            {$message_success|escape}
                        {/if}
                    </div>
                </div>
                {if $smarty.get.return}
                    <a class="alert__button" href="{$smarty.get.return}">
                        {include file='svg_icon.tpl' svgId='return'}
                        <span>{$btr->general_back|escape}</span>
                    </a>
                {/if}
            </div>
        </div>
    </div>
{/if}

{if $message_error}
    <div class="row">
        <div class="col-lg-12 col-md-12 col-sm-12">
            <div class="alert alert--center alert--icon alert--error">
                <div class="alert__content">
                    <div class="alert__title">
                        {if $message_error=='empty_subdomain'}
                            {$btr->empty_subdomain|escape}
                        {elseif $message_error=='invalid_subdomain'}
                            {$btr->invalid_subdomain|escape}
                        {elseif $message_error=='empty_city_name'}
                            {$btr->empty_city_name|escape}
                        {elseif $message_error=='subdomain_exists'}
                            {$btr->subdomain_exists|escape}
                        {else}
                            {$message_error|escape}
                        {/if}
                    </div>
                </div>
            </div>
        </div>
    </div>
{/if}

<form method="post" enctype="multipart/form-data">
    <input type="hidden" name="session_id" value="{$smarty.session.id}">
    <input type="hidden" name="id" value="{$subdomain->id}">
    
    {* Основные настройки *}
    <div class="row">
        <div class="col-lg-12">
            <div class="boxed">
                <div class="heading_box">
                    Основные настройки
                </div>
                
                <div class="row">
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label>{$btr->multiregions_subdomain}</label>
                            <input class="form-control" name="subdomain" type="text" value="{$subdomain->subdomain|escape}" placeholder="spb">
                            <small class="form-text text-muted">Только латинские буквы, цифры и дефис</small>
                        </div>
                    </div>
                    
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label>{$btr->multiregions_city}</label>
                            <input class="form-control" name="city_name" type="text" value="{$subdomain->city_name|escape}" placeholder="Санкт-Петербург">
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="enabled" value="1" id="enabled" {if !$subdomain->id || $subdomain->enabled}checked{/if}>
                        <label class="form-check-label" for="enabled">
                            {$btr->multiregions_enabled}
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    {* Склонения *}
    <div class="row">
        <div class="col-lg-12">
            <div class="boxed">
                <div class="heading_box">
                    {$btr->multiregions_declensions}
                    <button type="submit" name="auto_declension" value="1" class="btn btn-sm btn-info float-md-right">
                        {$btr->multiregions_auto_declension}
                    </button>
                </div>
                
                <div class="row">
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label>{$btr->multiregions_nominative}</label>
                            <input class="form-control" name="city_nominative" type="text" value="{$subdomain->city_nominative|escape}" placeholder="Москва">
                        </div>
                        
                        <div class="form-group">
                            <label>{$btr->multiregions_genitive}</label>
                            <input class="form-control" name="city_genitive" type="text" value="{$subdomain->city_genitive|escape}" placeholder="Москвы">
                        </div>
                        
                        <div class="form-group">
                            <label>{$btr->multiregions_dative}</label>
                            <input class="form-control" name="city_dative" type="text" value="{$subdomain->city_dative|escape}" placeholder="Москве">
                        </div>
                    </div>
                    
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label>{$btr->multiregions_accusative}</label>
                            <input class="form-control" name="city_accusative" type="text" value="{$subdomain->city_accusative|escape}" placeholder="Москву">
                        </div>
                        
                        <div class="form-group">
                            <label>{$btr->multiregions_instrumental}</label>
                            <input class="form-control" name="city_instrumental" type="text" value="{$subdomain->city_instrumental|escape}" placeholder="Москвой">
                        </div>
                        
                        <div class="form-group">
                            <label>{$btr->multiregions_prepositional}</label>
                            <input class="form-control" name="city_prepositional" type="text" value="{$subdomain->city_prepositional|escape}" placeholder="Москве">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    {* SEO шаблоны *}
    <div class="row">
        <div class="col-lg-12">
            <div class="boxed">
                <div class="heading_box">
                    {$btr->multiregions_seo_patterns}
                </div>
                
                <div class="alert alert-info">
                    <strong>{$btr->multiregions_available_variables}:</strong><br>
                    {foreach $available_variables as $group_name => $variables}
                        <div class="mt-2">
                            <strong>{$group_name}:</strong><br>
                            {foreach $variables as $var => $desc name=vars}
                                <code>{$var}</code> - {$desc}{if !$smarty.foreach.vars.last}, {/if}
                            {/foreach}
                        </div>
                    {/foreach}
                </div>
                
                {foreach $page_types as $type => $type_name}
                    <div class="card mb-3">
                        <div class="card-header">
                            <h5 class="mb-0">{$type_name}</h5>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label>{$btr->multiregions_meta_title}</label>
                                <input type="text" 
                                       name="seo_patterns[{$type}][meta_title_pattern]" 
                                       value="{if isset($seo_patterns[$type])}{$seo_patterns[$type]->meta_title_pattern|escape}{/if}" 
                                       class="form-control">
                            </div>
                            
                            <div class="form-group">
                                <label>{$btr->multiregions_h1}</label>
                                <input type="text" 
                                       name="seo_patterns[{$type}][h1_pattern]" 
                                       value="{if isset($seo_patterns[$type])}{$seo_patterns[$type]->h1_pattern|escape}{/if}" 
                                       class="form-control">
                            </div>
                            
                            <div class="form-group">
                                <label>{$btr->multiregions_meta_description}</label>
                                <textarea name="seo_patterns[{$type}][meta_description_pattern]" 
                                          class="form-control" 
                                          rows="2">{if isset($seo_patterns[$type])}{$seo_patterns[$type]->meta_description_pattern|escape}{/if}</textarea>
                            </div>
                            
                            <div class="form-group">
                                <label>{$btr->multiregions_meta_keywords}</label>
                                <input type="text" 
                                       name="seo_patterns[{$type}][meta_keywords_pattern]" 
                                       value="{if isset($seo_patterns[$type])}{$seo_patterns[$type]->meta_keywords_pattern|escape}{/if}" 
                                       class="form-control">
                            </div>
                        </div>
                    </div>
                {/foreach}
            </div>
        </div>
    </div>
    
    {* Кнопки *}
    <div class="row">
        <div class="col-lg-12 col-md-12 mt-3">
            <button type="submit" class="btn btn_small btn_blue float-md-right ml-1" name="apply_and_quit" value="1">
                {include file='svg_icon.tpl' svgId='checked'}
                <span>{$btr->general_apply_and_quit|escape}</span>
            </button>
            <button type="submit" class="btn btn_small btn_blue float-md-right">
                {include file='svg_icon.tpl' svgId='checked'}
                <span>{$btr->general_apply|escape}</span>
            </button>
        </div>
    </div>
</form>