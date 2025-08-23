{* Title *}
{$meta_title=$btr->left_multiregions_title scope=global}

<div class="main_header">
    <div class="main_header__item">
        <div class="main_header__inner">
            <div class="box_heading heading_page">
                {$btr->left_multiregions_title|escape}
            </div>
            <div class="box_btn_heading">
                <a class="btn btn_small btn-info" href="{url controller='OkayCMS.Multiregions.MultiregionAdmin'}">
                    {include file='svg_icon.tpl' svgId='plus'}
                    <span>{$btr->add_subdomain|escape}</span>
                </a>
            </div>
        </div>
    </div>
</div>

<div class="boxed fn_toggle_wrap">
    {if $subdomains}
        <div class="row">
            <div class="col-lg-12 col-md-12">
                <div class="boxed_sorting">
                    <div class="row">
                        <div class="col-lg-3 col-md-3 col-sm-12">
                            <select onchange="location = this.value;" class="selectpicker form-control">
                                <option value="{url limit=5}" {if $current_limit == 5}selected{/if}>{$btr->general_show_by|escape} 5</option>
                                <option value="{url limit=10}" {if $current_limit == 10}selected{/if}>{$btr->general_show_by|escape} 10</option>
                                <option value="{url limit=25}" {if $current_limit == 25}selected{/if}>{$btr->general_show_by|escape} 25</option>
                                <option value="{url limit=50}" {if $current_limit == 50}selected{/if}>{$btr->general_show_by|escape} 50</option>
                                <option value="{url limit=100}" {if $current_limit == 100}selected=""{/if}>{$btr->general_show_by|escape} 100</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <form method="post" class="fn_form_list">
            <input type="hidden" name="session_id" value="{$smarty.session.id}" />

            <div class="okay_list products_list fn_sort_list">
                {* Шапка таблицы *}
                <div class="okay_list_head">
                    <div class="okay_list_heading okay_list_check">
                        <input class="hidden_check fn_check_all" type="checkbox" id="check_all_1" name="" value=""/>
                        <label class="okay_ckeckbox" for="check_all_1"></label>
                    </div>
                    <div class="okay_list_heading okay_list_brands_name">{$btr->multiregions_subdomain|escape}</div>
                    <div class="okay_list_heading okay_list_brands_name">{$btr->multiregions_city|escape}</div>
                    <div class="okay_list_heading okay_list_features_tag">{$btr->multiregions_seo_patterns|escape}</div>
                    <div class="okay_list_heading okay_list_status">{$btr->multiregions_enabled|escape}</div>
                    <div class="okay_list_heading okay_list_close"></div>
                </div>

                {* Тело таблицы *}
                <div class="okay_list_body">
                    {foreach $subdomains as $subdomain}
                        <div class="fn_row okay_list_body_item">
                            <div class="okay_list_row">
                                <div class="okay_list_boding okay_list_check">
                                    <input class="hidden_check" type="checkbox" id="id_{$subdomain->id}" name="check[]" value="{$subdomain->id}" />
                                    <label class="okay_ckeckbox" for="id_{$subdomain->id}"></label>
                                </div>

                                <div class="okay_list_boding okay_list_brands_name">
                                    <a href="{url controller='OkayCMS.Multiregions.MultiregionAdmin' id=$subdomain->id return=$smarty.server.REQUEST_URI}">
                                        {$subdomain->subdomain|escape}
                                    </a>
                                </div>

                                <div class="okay_list_boding okay_list_brands_name">
                                    {$subdomain->city_name|escape}
                                </div>

                                <div class="okay_list_boding okay_list_features_tag">
                                    {if $subdomain->seo_patterns_count > 0}
                                        <span class="tag tag-info">{$subdomain->seo_patterns_count} шаблонов</span>
                                    {else}
                                        <span class="tag tag-warning">Не настроено</span>
                                    {/if}
                                </div>

                                <div class="okay_list_boding okay_list_status">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" 
                                               type="checkbox" 
                                               {if $subdomain->enabled}checked{/if}
                                               disabled>
                                    </div>
                                </div>

                                <div class="okay_list_boding okay_list_close">
                                    <button data-hint="Удалить поддомен" type="button" class="btn_close fn_remove hint-bottom-right-t-info-s-small-mobile hint-anim" data-toggle="modal" data-target="#fn_action_modal" onclick="success_action($(this));">
                                        {include file='svg_icon.tpl' svgId='trash'}
                                    </button>
                                </div>
                            </div>
                        </div>
                    {/foreach}
                </div>

                {* Блок массовых действий *}
                <div class="okay_list_footer fn_action_block">
                    <div class="okay_list_foot_left">
                        <div class="okay_list_heading okay_list_check">
                            <input class="hidden_check fn_check_all" type="checkbox" id="check_all_2" name="" value=""/>
                            <label class="okay_ckeckbox" for="check_all_2"></label>
                        </div>
                        <div class="okay_list_option">
                            <select name="action" class="selectpicker form-control">
                                <option value="">{$btr->general_select_action|escape}</option>
                                <option value="enable">{$btr->general_do_enable|escape}</option>
                                <option value="disable">{$btr->general_do_disable|escape}</option>
                                <option value="delete">{$btr->general_delete|escape}</option>
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="btn btn_small btn_blue">
                        {include file='svg_icon.tpl' svgId='checked'}
                        <span>{$btr->general_apply|escape}</span>
                    </button>
                </div>
            </div>
        </form>
        
        {if $pages_count > 1}
            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm 12 txt_center">
                    {include file='pagination.tpl'}
                </div>
            </div>
        {/if}
    {else}
        <div class="heading_box mt-1">
            <div class="text_grey">{$btr->subdomains_no|escape}</div>
        </div>
    {/if}
</div>