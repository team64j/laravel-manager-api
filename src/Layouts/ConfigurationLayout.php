<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Layouts;

use DateTimeImmutable;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Str;
use Team64j\LaravelEvolution\Models\SiteTemplate;
use Team64j\LaravelManagerApi\Components\ActionsButtons;
use Team64j\LaravelManagerApi\Components\Input;
use Team64j\LaravelManagerApi\Components\Number;
use Team64j\LaravelManagerApi\Components\Panel;
use Team64j\LaravelManagerApi\Components\Select;
use Team64j\LaravelManagerApi\Components\Tabs;
use Team64j\LaravelManagerApi\Components\Textarea;
use Team64j\LaravelManagerApi\Components\Title;

class ConfigurationLayout extends Layout
{
    /**
     * @return array
     */
    public function default(): array
    {
        return [
            ActionsButtons::make()
                ->setCancel()
                ->setSave(),

            Title::make()
                ->setTitle(Lang::get('global.settings_title'))
                ->setIcon('fa fa-sliders'),

            Tabs::make()
                ->setId('configuration')
                ->addTab('tab1', Lang::get('global.settings_site'), null, 'flex flex-wrap py-4 mb-4')
                ->addTab('tab2', Lang::get('global.settings_furls'), null, 'flex flex-wrap py-4 mb-4')
                ->addTab('tab3', Lang::get('global.settings_ui'), null, 'flex flex-wrap py-4 mb-4')
                ->addTab('tab4', Lang::get('global.settings_security'), null, 'flex flex-wrap py-4 mb-4')
                ->addTab('tab5', Lang::get('global.settings_misc'), null, 'flex flex-wrap py-4 mb-4')
                ->addTab('tab6', Lang::get('global.settings_KC'), null, 'flex flex-wrap py-4 mb-4')
                ->addTab('tab7', Lang::get('global.settings_email_templates'), null, 'flex flex-wrap py-4 mb-4')
                ->addSlot('tab1', $this->tab1())
                ->addSlot('tab2', $this->tab2())
                ->addSlot('tab3', $this->tab3())
                ->addSlot('tab4', $this->tab4())
                ->addSlot('tab5', $this->tab5())
                ->addSlot('tab6', $this->tab6())
                ->addSlot('tab7', $this->tab7()),
        ];
    }

    /**
     * @return array
     */
    protected function columns(): array
    {
        return [
            [
                'name' => 'name',
                'label' => Lang::get('global.description'),
                'width' => '25rem',
                'style' => [
                    'minWidth' => '15rem',
                ],
            ],
            [
                'name' => 'key',
                'label' => Lang::get('global.name'),
                'style' => [
                    'fontWeight' => 500,
                ],
            ],
            [
                'name' => 'value',
                'label' => Lang::get('global.value'),
                'width' => '35rem',
                'style' => [
                    'minWidth' => '15rem',
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    protected function tab1(): array
    {
        /** @var SiteTemplate $template */
        $template = SiteTemplate::query()->findOrNew(Config::get('global.default_template'));
        $defaultTemplate = [
            'key' => $template->id ?: 0,
            'value' => $template->templatename ?: 'blank',
            'selected' => true,
        ];

        $serverTimes = [];
        $serverTimesRange = range(-24, 24);

        for ($i = 0; $i < count($serverTimesRange); $i++) {
            $serverTimes[] = [
                'key' => $serverTimesRange[$i],
                'value' => $serverTimesRange[$i],
            ];
        }

        $auto_template_logic = [
            'system' => Lang::get('global.defaulttemplate_logic_system_message'),
            'parent' => Lang::get('global.defaulttemplate_logic_parent_message'),
            'sibling' => Lang::get('global.defaulttemplate_logic_sibling_message'),
        ];

        $auto_template_logic_values = [];

        foreach ($auto_template_logic as $key => $value) {
            $auto_template_logic_values[] = [
                'key' => $key,
                'value' => explode(':', strip_tags($value))[0],
            ];
        }

        return [
            Panel::make()
                ->setId('tab1')
                ->setColumns($this->columns())
                ->setData([
                    [
                        'name' => Lang::get('global.sitename_title'),
                        'name.help' => Lang::get('global.sitename_message'),
                        'key' => 'site_name',
                        'value' => Input::make('site_name'),
                    ],
                    [
                        'name' => Lang::get('global.sitestatus_title'),
                        'key' => 'site_status',
                        'value' => Select::make('site_status')
                            ->addYesNo(
                                Lang::get('global.online'),
                                Lang::get('global.offline')
                            ),
                    ],
                    [
                        'name' => Lang::get('global.sitestart_title'),
                        'name.help' => Lang::get('global.sitestart_message'),
                        'key' => 'site_start',
                        'value' => Number::make('site_start'),
                    ],
                    [
                        'name' => Lang::get('global.errorpage_title'),
                        'name.help' => Lang::get('global.errorpage_message'),
                        'key' => 'error_page',
                        'value' => Number::make('error_page'),
                    ],
                    [
                        'name' => Lang::get('global.unauthorizedpage_title'),
                        'name.help' => Lang::get('global.unauthorizedpage_message'),
                        'key' => 'unauthorized_page',
                        'value' => Number::make('unauthorized_page'),
                    ],
                    [
                        'name' => Lang::get('global.siteunavailable_page_title'),
                        'name.help' => Lang::get('global.siteunavailable_page_message'),
                        'key' => 'site_unavailable_page',
                        'value' => Number::make('site_unavailable_page'),
                    ],
                    [
                        'name' => Lang::get('global.controller_namespace'),
                        'name.help' => Lang::get('global.controller_namespace_message'),
                        'key' => 'ControllerNamespace',
                        'value' => Input::make('ControllerNamespace'),
                    ],
                    [
                        'name' => Lang::get('global.update_repository'),
                        'name.help' => Lang::get('global.update_repository_message'),
                        'key' => 'UpgradeRepository',
                        'value' => Input::make('UpgradeRepository'),
                    ],
                    [
                        'name' => Lang::get('global.siteunavailable_title'),
                        'name.help' => Lang::get('global.siteunavailable_message'),
                        'key' => 'site_unavailable_message',
                        'value' => Textarea::make('site_unavailable_message'),
                    ],
                    [
                        'name' => Lang::get('global.defaulttemplate_title'),
                        'name.help' => Lang::get('global.defaulttemplate_message'),
                        'key' => 'default_template',
                        'value' => Select::make('default_template')
                            ->setData([$defaultTemplate])
                            ->setUrl('/templates/select'),
                    ],
                    [
                        'name' => Lang::get('global.defaulttemplate_logic_title'),
                        'name.help' => implode('<br>', $auto_template_logic),
                        'key' => 'auto_template_logic',
                        'value' => Select::make('auto_template_logic')
                            ->setData($auto_template_logic_values),
                    ],
                    [
                        'name' => Lang::get('global.chunk_processor'),
                        'key' => 'chunk_processor',
                        'value' => Select::make('chunk_processor')
                            ->addOption(null, 'DocumentParser')
                            ->addOption('DLTemplate'),
                    ],
                    [
                        'name' => Lang::get('global.defaultpublish_title'),
                        'name.help' => Lang::get('global.defaultpublish_message'),
                        'key' => 'publish_default',
                        'value' => Select::make('publish_default')->addYesNo(),
                    ],
                    [
                        'name' => Lang::get('global.defaultcache_title'),
                        'name.help' => Lang::get('global.defaultcache_message'),
                        'key' => 'cache_default',
                        'value' => Select::make('cache_default')->addYesNo(),
                    ],
                    [
                        'name' => Lang::get('global.defaultsearch_title'),
                        'name.help' => Lang::get('global.defaultsearch_message'),
                        'key' => 'search_default',
                        'value' => Select::make('search_default')->addYesNo(),
                    ],
                    [
                        'name' => Lang::get('global.defaultmenuindex_title'),
                        'name.help' => Lang::get('global.defaultmenuindex_message'),
                        'key' => 'auto_menuindex',
                        'value' => Select::make('auto_menuindex')->addYesNo(),
                    ],
                    [
                        'name' => Lang::get('global.track_visitors_title'),
                        'name.help' => Lang::get('global.track_visitors_message'),
                        'key' => 'track_visitors',
                        'value' => Select::make('track_visitors')->addYesNo(),
                    ],
                    [
                        'name' => Lang::get('global.docid_incrmnt_method_title'),
                        'key' => 'docid_incrmnt_method',
                        'value' => Select::make('docid_incrmnt_method')
                            ->addOption(0, Lang::get('global.docid_incrmnt_method_0'))
                            ->addOption(1, Lang::get('global.docid_incrmnt_method_1'))
                            ->addOption(2, Lang::get('global.docid_incrmnt_method_2')),
                    ],
                    [
                        'name' => Lang::get('global.enable_cache_title'),
                        'key' => 'enable_cache',
                        'value' => Select::make('enable_cache')
                            ->addOption(1, Lang::get('global.enabled'))
                            ->addOption(0, Lang::get('global.disabled'))
                            ->addOption(2, Lang::get('global.disabled_at_login')),
                    ],
                    [
                        'name' => Lang::get('global.disable_chunk_cache_title'),
                        'key' => 'disable_chunk_cache',
                        'value' => Select::make('disable_chunk_cache')->addYesNo(),
                    ],
                    [
                        'name' => Lang::get('global.disable_snippet_cache_title'),
                        'key' => 'disable_snippet_cache',
                        'value' => Select::make('disable_snippet_cache')->addYesNo(),
                    ],
                    [
                        'name' => Lang::get('global.disable_plugins_cache_title'),
                        'key' => 'disable_plugins_cache',
                        'value' => Select::make('disable_plugins_cache')->addYesNo(),
                    ],
                    [
                        'name' => Lang::get('global.cache_type_title'),
                        'key' => 'cache_type',
                        'value' => Select::make('cache_type')
                            ->addOption(1, Lang::get('global.cache_type_1'))
                            ->addOption(2, Lang::get('global.cache_type_2')),
                    ],
                    [
                        'name' => Lang::get('global.minifyphp_incache_title'),
                        'name.help' => Lang::get('global.minifyphp_incache_message'),
                        'key' => 'minifyphp_incache',
                        'value' => Select::make('minifyphp_incache')
                            ->addYesNo(
                                Lang::get('global.enabled'),
                                Lang::get('global.disabled')
                            ),
                    ],
                    [
                        'name' => Lang::get('global.serveroffset_title'),
                        'name.help' => sprintf(
                            Lang::get('global.serveroffset_message'),
                            DateTimeImmutable::createFromFormat('U', (string) time())->format('H:i:s'),
                            DateTimeImmutable::createFromFormat(
                                'U',
                                (string) (time() + Config::get('global.server_offset_time'))
                            )->format('H:i:s')
                        ),
                        'key' => 'server_offset_time',
                        'value' => Select::make('server_offset_time')->setData($serverTimes),
                    ],
                    [
                        'name' => Lang::get('global.server_protocol_title'),
                        'name.help' => Lang::get('global.server_protocol_message'),
                        'key' => 'server_protocol',
                        'value' => Select::make('server_protocol')
                            ->addOption('http', Lang::get('global.server_protocol_http'))
                            ->addOption('https', Lang::get('global.server_protocol_https')),
                    ],
                    [
                        'name' => Lang::get('global.rss_url_news_title'),
                        'name.help' => Lang::get('global.rss_url_news_message'),
                        'key' => 'rss_url_news',
                        'value' => Input::make('rss_url_news'),
                    ],
                    [
                        'name' => Lang::get('global.rss_url_security_title'),
                        'name.help' => Lang::get('global.rss_url_security_message'),
                        'key' => 'rss_url_security',
                        'value' => Input::make('rss_url_security'),
                    ],
                ]),
        ];
    }

    /**
     * @return array
     */
    protected function tab2(): array
    {
        return [
            Panel::make()
                ->setId('tab2')
                ->setColumns($this->columns())
                ->setData([
                    [
                        'name' => Lang::get('global.friendlyurls_title'),
                        'name.help' => Lang::get('global.friendlyurls_message'),
                        'key' => 'friendly_urls',
                        'value' => Select::make('friendly_urls')
                            ->addYesNo(
                                Lang::get('global.yes'),
                                Lang::get('global.no')
                            ),
                    ],
                    [
                        'name' => Lang::get('global.xhtml_urls_title'),
                        'name.help' => Lang::get('global.xhtml_urls_message'),
                        'key' => 'xhtml_urls',
                        'value' => Select::make('xhtml_urls')
                            ->addYesNo(
                                Lang::get('global.yes'),
                                Lang::get('global.no')
                            ),
                    ],
                    [
                        'name' => Lang::get('global.friendlyurlsprefix_title'),
                        'name.help' => Lang::get('global.friendlyurlsprefix_message'),
                        'key' => 'friendly_url_prefix',
                        'value' => Input::make('friendly_url_prefix'),
                    ],
                    [
                        'name' => Lang::get('global.friendlyurlsuffix_title'),
                        'name.help' => Lang::get('global.friendlyurlsuffix_message'),
                        'key' => 'friendly_url_suffix',
                        'value' => Input::make('friendly_url_suffix'),
                    ],
                    [
                        'name' => Lang::get('global.make_folders_title'),
                        'name.help' => Lang::get('global.make_folders_message'),
                        'key' => 'make_folders',
                        'value' => Select::make('make_folders')
                            ->addYesNo(
                                Lang::get('global.yes'),
                                Lang::get('global.no')
                            ),
                    ],
                    [
                        'name' => Lang::get('global.seostrict_title'),
                        'name.help' => Lang::get('global.seostrict_message'),
                        'key' => 'seostrict',
                        'value' => Select::make('seostrict')
                            ->addYesNo(
                                Lang::get('global.yes'),
                                Lang::get('global.no')
                            ),
                    ],
                    [
                        'name' => Lang::get('global.friendly_alias_title'),
                        'name.help' => Lang::get('global.friendlyurls_message'),
                        'key' => 'friendly_alias_urls',
                        'value' => Select::make('friendly_alias_urls')
                            ->addYesNo(
                                Lang::get('global.yes'),
                                Lang::get('global.no')
                            ),
                    ],
                    [
                        'name' => Lang::get('global.use_alias_path_title'),
                        'name.help' => Lang::get('global.use_alias_path_message'),
                        'key' => 'use_alias_path',
                        'value' => Select::make('use_alias_path')
                            ->addYesNo(
                                Lang::get('global.yes'),
                                Lang::get('global.no')
                            ),
                    ],
                    [
                        'name' => Lang::get('global.alias_listing_title'),
                        'name.help' => Lang::get('global.alias_listing_message'),
                        'key' => 'alias_listing',
                        'value' => Select::make('alias_listing')
                            ->setData(
                                [
                                    [
                                        'key' => 1,
                                        'value' => Lang::get('global.alias_listing_enabled'),
                                    ],
                                    [
                                        'key' => 2,
                                        'value' => Lang::get('global.alias_listing_folders'),
                                    ],
                                    [
                                        'key' => 0,
                                        'value' => Lang::get('global.alias_listing_disabled'),
                                    ],
                                ]
                            ),
                    ],
                    [
                        'name' => Lang::get('global.duplicate_alias_title'),
                        'name.help' => Lang::get('global.duplicate_alias_message'),
                        'key' => 'allow_duplicate_alias',
                        'value' => Select::make('allow_duplicate_alias')
                            ->addYesNo(
                                Lang::get('global.yes'),
                                Lang::get('global.no')
                            ),
                    ],
                    [
                        'name' => Lang::get('global.automatic_alias_title'),
                        'name.help' => Lang::get('global.automatic_alias_message'),
                        'key' => 'automatic_alias',
                        'value' => Select::make('automatic_alias')
                            ->addYesNo(
                                Lang::get('global.yes'),
                                Lang::get('global.no')
                            ),
                    ],
                ]),
        ];
    }

    /**
     * @return array
     */
    protected function tab3(): array
    {
        $settings_group_tv_options = explode(',', Lang::get('global.settings_group_tv_options'));

        return [
            Panel::make()
                ->setId('tab3')
                ->setColumns($this->columns())
                ->setData([
                    [
                        'name' => Lang::get('global.language_title'),
                        'name.help' => Lang::get('global.language_message'),
                        'key' => 'manager_language',
                        'value' => Select::make('manager_language')
                            ->setData(
                                array_map(fn($dir) => [
                                    'key' => basename($dir),
                                    'value' => Str::upper(basename($dir)),
                                ], File::directories(App::langPath()))
                            ),
                    ],
                    [
                        'name' => Lang::get('global.charset_title'),
                        'name.help' => Lang::get('global.charset_message'),
                        'key' => 'modx_charset',
                        'value' => Select::make('modx_charset')
                            ->setData([
                                [
                                    'key' => 'UTF-8',
                                    'value' => 'Unicode (UTF-8) - utf-8',
                                ],
                            ]),
                    ],
                    [
                        'name' => Lang::get('global.manager_theme'),
                        'name.help' => Lang::get('global.manager_theme'),
                        'key' => 'manager_theme',
                        'value' => Select::make('manager_theme')
                            ->setData([
                                [
                                    'key' => 'default',
                                    'value' => 'Default',
                                ],
                            ]),
                    ],
                    [
                        'name' => Lang::get('global.manager_theme_mode'),
                        'name.help' => Lang::get('global.manager_theme_mode_message'),
                        'key' => 'manager_theme_mode',
                        'value' => Select::make('manager_theme_mode')
                            ->setData([
                                [
                                    'key' => 1,
                                    'value' => Lang::get('global.manager_theme_mode1'),
                                ],
                                [
                                    'key' => 2,
                                    'value' => Lang::get('global.manager_theme_mode2'),
                                ],
                                [
                                    'key' => 3,
                                    'value' => Lang::get('global.manager_theme_mode3'),
                                ],
                                [
                                    'key' => 4,
                                    'value' => Lang::get('global.manager_theme_mode4'),
                                ],
                            ]),
                    ],
                    [
                        'name' => Lang::get('global.login_logo_title'),
                        'name.help' => Lang::get('global.login_logo_message'),
                        'key' => 'login_logo',
                        'value' => \Team64j\LaravelManagerApi\Components\File::make('login_logo'),
                    ],
                    [
                        'name' => Lang::get('global.login_bg_title'),
                        'name.help' => Lang::get('global.login_bg_message'),
                        'key' => 'login_bg',
                        'value' => \Team64j\LaravelManagerApi\Components\File::make('login_bg'),
                    ],
                    [
                        'name' => Lang::get('global.login_form_position_title'),
                        'key' => 'login_form_position',
                        'value' => Select::make('login_form_position')
                            ->setData([
                                [
                                    'key' => 'left',
                                    'value' => Lang::get('global.login_form_position_left'),
                                ],
                                [
                                    'key' => 'center',
                                    'value' => Lang::get('global.login_form_position_center'),
                                ],
                                [
                                    'key' => 'right',
                                    'value' => Lang::get('global.login_form_position_right'),
                                ],
                            ]),
                    ],
                    [
                        'name' => Lang::get('global.login_form_style'),
                        'key' => 'login_form_style',
                        'value' => Select::make('login_form_style')
                            ->setData([
                                [
                                    'key' => 'dark',
                                    'value' => Lang::get('global.login_form_style_dark'),
                                ],
                                [
                                    'key' => 'light',
                                    'value' => Lang::get('global.login_form_style_light'),
                                ],
                            ]),
                    ],
                    [
                        'name' => Lang::get('global.manager_menu_position_title'),
                        'key' => 'manager_menu_position',
                        'value' => Select::make('manager_menu_position')
                            ->setData([
                                [
                                    'key' => 'top',
                                    'value' => Lang::get('global.manager_menu_position_top'),
                                ],
                                [
                                    'key' => 'left',
                                    'value' => Lang::get('global.manager_menu_position_left'),
                                ],
                            ]),
                    ],
                    [
                        'name' => Lang::get('global.show_picker'),
                        'name.help' => Lang::get('global.settings_show_picker_message'),
                        'key' => 'show_picker',
                        'value' => Select::make('show_picker')
                            ->addYesNo(
                                Lang::get('global.yes'),
                                Lang::get('global.no')
                            ),
                    ],
                    [
                        'name' => Lang::get('global.warning_visibility'),
                        'name.help' => Lang::get('global.warning_visibility_message'),
                        'key' => 'warning_visibility',
                        'value' => Select::make('warning_visibility')
                            ->addYesNo(
                                Lang::get('global.everybody'),
                                Lang::get('global.administrators'),
                                0,
                                1
                            ),
                    ],
                    [
                        'name' => Lang::get('global.tree_page_click'),
                        'name.help' => Lang::get('global.tree_page_click_message'),
                        'key' => 'tree_page_click',
                        'value' => Select::make('tree_page_click')
                            ->addYesNo(
                                Lang::get('global.edit'),
                                Lang::get('global.resource_overview'),
                                27,
                                3
                            ),
                    ],
                    [
                        'name' => Lang::get('global.use_breadcrumbs'),
                        'name.help' => Lang::get('global.use_breadcrumbs_message'),
                        'key' => 'use_breadcrumbs',
                        'value' => Select::make('use_breadcrumbs')
                            ->addYesNo(
                                Lang::get('global.yes'),
                                Lang::get('global.no')
                            ),
                    ],
                    [
                        'name' => Lang::get('global.remember_last_tab'),
                        'name.help' => Lang::get('global.remember_last_tab_message'),
                        'key' => 'remember_last_tab',
                        'value' => Select::make('remember_last_tab')
                            ->addYesNo(
                                Lang::get('global.yes'),
                                Lang::get('global.no')
                            ),
                    ],
                    [
                        'name' => Lang::get('global.use_global_tabs'),
                        'key' => 'global_tabs',
                        'value' => Select::make('global_tabs')
                            ->addYesNo(
                                Lang::get('global.yes'),
                                Lang::get('global.no')
                            ),
                    ],
                    [
                        'name' => Lang::get('global.group_tvs'),
                        'name.help' => Lang::get('global.settings_group_tv_message'),
                        'key' => 'group_tvs',
                        'value' => Select::make('group_tvs')
                            ->setData(
                                array_map(fn($key, $value) => [
                                    'key' => $key,
                                    'value' => $value,
                                ],
                                    array_keys($settings_group_tv_options),
                                    $settings_group_tv_options
                                )
                            ),
                    ],
                    [
                        'name' => Lang::get('global.show_newresource_btn'),
                        'name.help' => Lang::get('global.show_newresource_btn_message'),
                        'key' => 'show_newresource_btn',
                        'value' => Select::make('show_newresource_btn')
                            ->addYesNo(
                                Lang::get('global.yes'),
                                Lang::get('global.no')
                            ),
                    ],
                    [
                        'name' => Lang::get('global.show_fullscreen_btn'),
                        'name.help' => Lang::get('global.show_fullscreen_btn_message'),
                        'key' => 'show_fullscreen_btn',
                        'value' => Select::make('show_fullscreen_btn')
                            ->addYesNo(
                                Lang::get('global.yes'),
                                Lang::get('global.no')
                            ),
                    ],
                    [
                        'name' => Lang::get('global.setting_resource_tree_node_name'),
                        'name.help' => Lang::get('global.setting_resource_tree_node_name_desc'),
                        'key' => 'resource_tree_node_name',
                        'value' => Select::make('resource_tree_node_name')
                            ->setData([
                                [
                                    'key' => 'pagetitle',
                                    'value' => '[*pagetitle*]',
                                ],
                                [
                                    'key' => 'longtitle',
                                    'value' => '[*longtitle*]',
                                ],
                                [
                                    'key' => 'menutitle',
                                    'value' => '[*menutitle*]',
                                ],
                                [
                                    'key' => 'alias',
                                    'value' => '[*alias*]',
                                ],
                                [
                                    'key' => 'createdon',
                                    'value' => '[*createdon*]',
                                ],
                                [
                                    'key' => 'editedon',
                                    'value' => '[*editedon*]',
                                ],
                                [
                                    'key' => 'publishedon',
                                    'value' => '[*publishedon*]',
                                ],
                            ]),
                    ],
                    [
                        'name' => Lang::get('global.session_timeout'),
                        'name.help' => Lang::get('global.session_timeout_msg'),
                        'key' => 'session_timeout',
                        'value' => Number::make('session_timeout'),
                    ],
                    [
                        'name' => Lang::get('global.tree_show_protected'),
                        'name.help' => Lang::get('global.tree_show_protected_message'),
                        'key' => 'tree_show_protected',
                        'value' => Select::make('tree_show_protected')
                            ->addYesNo(
                                Lang::get('global.yes'),
                                Lang::get('global.no')
                            ),
                    ],
                    [
                        'name' => Lang::get('global.datepicker_offset'),
                        'name.help' => Lang::get('global.datepicker_offset_message'),
                        'key' => 'datepicker_offset',
                        'value' => Number::make('datepicker_offset'),
                    ],
                    [
                        'name' => Lang::get('global.datetime_format'),
                        'name.help' => Lang::get('global.datetime_format_message'),
                        'key' => 'datetime_format',
                        'value' => Select::make('datetime_format')
                            ->setData([
                                [
                                    'key' => 'dd-mm-YYYY',
                                    'value' => 'dd-mm-YYYY',
                                ],
                                [
                                    'key' => 'mm/dd/YYYY',
                                    'value' => 'mm/dd/YYYY',
                                ],
                                [
                                    'key' => 'YYYY/mm/dd',
                                    'value' => 'YYYY/mm/dd',
                                ],
                            ]),
                    ],
                    [
                        'name' => Lang::get('global.nologentries_title'),
                        'name.help' => Lang::get('global.nologentries_message'),
                        'key' => 'number_of_logs',
                        'value' => Number::make('number_of_logs'),
                    ],
                    [
                        'name' => Lang::get('global.noresults_title'),
                        'name.help' => Lang::get('global.noresults_message'),
                        'key' => 'number_of_results',
                        'value' => Number::make('number_of_results'),
                    ],
                ]),
        ];
    }

    /**
     * @return array
     */
    protected function tab4(): array
    {
        return [
            Panel::make()
                ->setId('tab4')
                ->setColumns($this->columns())
                ->setData([]),
        ];
    }

    /**
     * @return array
     */
    protected function tab5(): array
    {
        return [
            Panel::make()
                ->setId('tab5')
                ->setColumns($this->columns())
                ->setData([]),
        ];
    }

    /**
     * @return array
     */
    protected function tab6(): array
    {
        return [
            Panel::make()
                ->setId('tab6')
                ->setColumns($this->columns())
                ->setData([]),
        ];
    }

    /**
     * @return array
     */
    protected function tab7(): array
    {
        return [
            Panel::make()
                ->setId('tab7')
                ->setColumns($this->columns())
                ->setData([]),
        ];
    }

    /**
     * @return string
     */
    public function getIcon(): string
    {
        return 'fa fa-sliders';
    }
}
