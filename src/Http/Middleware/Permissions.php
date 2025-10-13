<?php

declare(strict_types=1);

namespace Team64j\LaravelManagerApi\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Permissions
{
    protected array $permission = [
        'manager.api' => [],

        'manager.api.auth.forgot'      => [],
        'manager.api.auth.forgot-form' => [],
        'manager.api.auth.login'       => [],
        'manager.api.auth.login-form'  => [],
        'manager.api.auth.refresh'     => [],

        'manager.api.bootstrap.index'        => ['frames', 'home'],
        'manager.api.bootstrap.init'         => ['frames', 'home'],
        'manager.api.bootstrap.select-pages' => ['frames', 'home'],

        'manager.api.cache.index' => [],

        'manager.api.categories.destroy' => ['category_manager'],
        'manager.api.categories.index'   => ['category_manager'],
        'manager.api.categories.list'    => ['category_manager'],
        'manager.api.categories.select'  => ['category_manager'],
        'manager.api.categories.show'    => ['category_manager'],
        'manager.api.categories.sort'    => ['category_manager'],
        'manager.api.categories.store'   => ['category_manager'],
        'manager.api.categories.tree'    => ['category_manager'],
        'manager.api.categories.update'  => ['category_manager'],

        'manager.api.chunks.destroy' => ['delete_chunk'],
        'manager.api.chunks.index'   => ['edit_chunk'],
        'manager.api.chunks.list'    => ['edit_chunk', 'new_chunk', 'save_chunk', 'delete_chunk'],
        'manager.api.chunks.news'    => ['edit_chunk'],
        'manager.api.chunks.show'    => ['edit_chunk'],
        'manager.api.chunks.store'   => ['new_chunk'],
        'manager.api.chunks.update'  => ['save_chunk'],

        'manager.api.configuration.index' => ['settings'],
        'manager.api.configuration.store' => ['settings'],

        'manager.api.dashboard.index'         => ['frames', 'home'],
        'manager.api.dashboard.news'          => ['frames', 'home'],
        'manager.api.dashboard.news-security' => ['frames', 'home'],

        'manager.api.resource.destroy'    => ['delete_document'],
        'manager.api.resource.index'      => ['edit_document', 'view_document'],
        'manager.api.resource.parents'    => ['view_document'],
        'manager.api.resource.set-parent' => ['view_document'],
        'manager.api.resource.show'       => ['view_document'],
        'manager.api.resource.store'      => ['new_document'],
        'manager.api.resource.tree'       => ['view_document'],
        'manager.api.resource.update'     => ['save_document'],

        'manager.api.resources' => ['view_document'],

        'manager.api.event-log.destroy' => ['delete_eventlog'],
        'manager.api.event-log.index'   => ['view_eventlog'],
        'manager.api.event-log.show'    => ['view_eventlog'],

        'manager.api.file.show' => ['file_manager'],
        'manager.api.file.tree' => ['file_manager'],

        'manager.api.files.index' => ['file_manager'],
        'manager.api.files.show'  => ['file_manager'],
        'manager.api.files.tree'  => ['file_manager'],

        'manager.api.help' => ['help'],

        'manager.api.modules.destroy' => ['delete_module'],
        'manager.api.modules.exec'    => ['list_module'],
        'manager.api.modules.index'   => ['edit_module'],
        'manager.api.modules.list'    => ['edit_module', 'new_module', 'save_module', 'delete_module'],
        'manager.api.modules.run'     => ['exec_module'],
        'manager.api.modules.show'    => ['edit_module', 'new_module', 'save_module', 'delete_module'],
        'manager.api.modules.store'   => ['new_module'],
        'manager.api.modules.tree'    => ['edit_module', 'new_module', 'save_module', 'delete_module'],
        'manager.api.modules.update'  => ['save_module'],

        'manager.api.password.index' => [],
        'manager.api.password.store' => [],

        'manager.api.permissions.group'     => ['access_permissions'],
        'manager.api.permissions.groups'    => ['access_permissions'],
        'manager.api.permissions.relation'  => ['access_permissions'],
        'manager.api.permissions.relations' => ['access_permissions'],
        'manager.api.permissions.resource'  => ['access_permissions'],
        'manager.api.permissions.resources' => ['access_permissions'],
        'manager.api.permissions.select'    => ['access_permissions'],

        'manager.api.plugins.destroy' => ['delete_plugin'],
        'manager.api.plugins.index'   => ['edit_plugin'],
        'manager.api.plugins.list'    => ['edit_plugin', 'new_plugin', 'save_plugin', 'delete_plugin'],
        'manager.api.plugins.show'    => ['edit_plugin', 'new_plugin', 'save_plugin', 'delete_plugin'],
        'manager.api.plugins.sort'    => ['edit_plugin', 'new_plugin', 'save_plugin', 'delete_plugin'],
        'manager.api.plugins.store'   => ['new_plugin'],
        'manager.api.plugins.tree'    => ['edit_plugin', 'new_plugin', 'save_plugin', 'delete_plugin'],
        'manager.api.plugins.update'  => ['save_plugin'],

        'manager.api.roles.categories.index'  => ['new_role', 'edit_role', 'save_role', 'delete_role'],
        'manager.api.roles.categories.show'   => ['new_role', 'edit_role', 'save_role', 'delete_role'],
        'manager.api.roles.permissions.index' => ['new_role', 'edit_role', 'save_role', 'delete_role'],
        'manager.api.roles.permissions.show'  => ['new_role', 'edit_role', 'save_role', 'delete_role'],
        'manager.api.roles.users.index'       => ['new_role', 'edit_role', 'save_role', 'delete_role'],
        'manager.api.roles.users.show'        => ['new_role', 'edit_role', 'save_role', 'delete_role'],

        'manager.api.schedule.index' => ['settings'],

        'manager.api.search.index' => ['settings'],

        'manager.api.snippets.destroy' => ['delete_snippet'],
        'manager.api.snippets.index'   => ['edit_snippet'],
        'manager.api.snippets.list'    => ['edit_snippet', 'new_snippet', 'save_snippet', 'delete_snippet'],
        'manager.api.snippets.show'    => ['edit_snippet', 'new_snippet', 'save_snippet', 'delete_snippet'],
        'manager.api.snippets.store'   => ['new_snippet'],
        'manager.api.snippets.tree'    => ['edit_snippet', 'new_snippet', 'save_snippet', 'delete_snippet'],
        'manager.api.snippets.update'  => ['save_snippet'],

        'manager.api.system-info.index'   => ['logs'],
        'manager.api.system-info.phpinfo' => ['logs'],

        'manager.api.system-log.phpinfo' => ['logs'],

        'manager.api.templates.destroy' => ['delete_template'],
        'manager.api.templates.index'   => ['edit_template'],
        'manager.api.templates.list'    => ['edit_template', 'new_template', 'save_template', 'delete_template'],
        'manager.api.templates.select'  => ['edit_template', 'new_template', 'save_template', 'delete_template'],
        'manager.api.templates.show'    => ['edit_template', 'new_template', 'save_template', 'delete_template'],
        'manager.api.templates.store'   => ['new_template'],
        'manager.api.templates.tree'    => ['edit_template', 'new_template', 'save_template', 'delete_template'],
        'manager.api.templates.tvs'     => ['edit_template', 'new_template', 'save_template', 'delete_template'],
        'manager.api.templates.update'  => ['save_template'],

        'manager.api.tvs.destroy' => ['edit_template', 'edit_snippet', 'edit_chunk', 'edit_plugin'],
        'manager.api.tvs.index'   => ['edit_template', 'edit_snippet', 'edit_chunk', 'edit_plugin'],
        'manager.api.tvs.list'    => ['edit_template', 'edit_snippet', 'edit_chunk', 'edit_plugin'],
        'manager.api.tvs.show'    => ['edit_template', 'edit_snippet', 'edit_chunk', 'edit_plugin'],
        'manager.api.tvs.sort'    => ['edit_template', 'edit_snippet', 'edit_chunk', 'edit_plugin'],
        'manager.api.tvs.store'   => ['edit_template', 'edit_snippet', 'edit_chunk', 'edit_plugin'],
        'manager.api.tvs.tree'    => ['edit_template', 'edit_snippet', 'edit_chunk', 'edit_plugin'],
        'manager.api.tvs.types'   => ['edit_template', 'edit_snippet', 'edit_chunk', 'edit_plugin'],
        'manager.api.tvs.update'  => ['edit_template', 'edit_snippet', 'edit_chunk', 'edit_plugin'],

        'manager.api.users.active'  => ['edit_user', 'new_user', 'save_user', 'delete_user'],
        'manager.api.users.destroy' => ['delete_user'],
        'manager.api.users.index'   => ['edit_user'],
        'manager.api.users.list'    => ['edit_user', 'new_user', 'save_user', 'delete_user'],
        'manager.api.users.show'    => ['edit_user', 'new_user', 'save_user', 'delete_user'],
        'manager.api.users.store'   => ['new_user'],
        'manager.api.users.update'  => ['save_user'],

        'manager.api.workspace.index' => ['settings'],
        'manager.api.workspace.store' => ['settings'],
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $permission = $this->permission[$request->route()->getName()] ?? null;

        if ($permission && auth()->user()->cant($permission)) {
            abort(403, 'Forbidden');
        }

        return $next($request);
    }
}
