<?php

namespace App\Services;

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class RoleService
{
    /**
     * Get all roles with permissions, with optional search and pagination.
     */
    public function getAllRoles($search = null)
    {
        return Role::with('permissions')
            ->when($search, fn($q) => $q->where('name', 'like', "%$search%"))
            ->paginate(10);
    }

    /**
     * Get role by ID with permissions.
     */
    public function getRoleById($id)
    {
        return Role::with('permissions')->findOrFail($id);
    }

    /**
     * Create a new role with permissions.
     */
    public function createRole(array $data)
    {
        DB::transaction(function () use ($data) {
            $role = Role::create([
                'name' => $data['name'],
                'guard_name' => $data['guard_name'] ?? 'web',
            ]);

            if (isset($data['permissions'])) {
                $role->syncPermissions($data['permissions']);
            }

            $this->clearPermissionCache();
        });
    }

    /**
     * Update an existing role.
     */
    public function updateRole($id, array $data)
    {
        $role = Role::findOrFail($id);

        DB::transaction(function () use ($role, $data) {
            $role->update([
                'name' => $data['name'],
                'guard_name' => $data['guard_name'] ?? 'web',
            ]);

            if (isset($data['permissions'])) {
                $role->syncPermissions($data['permissions']);
            }

            $this->clearPermissionCache();
        });
    }

    /**
     * Delete a role.
     */
    public function deleteRole($id)
    {
        $role = Role::findOrFail($id);

        DB::transaction(function () use ($role) {
            $role->delete();
            $this->clearPermissionCache();
        });
    }

    /**
     * Get all permissions grouped.
     */
    public function getGroupedPermissions()
    {
        $permissions = Permission::all();

        return $permissions->groupBy(function ($permission) {
            return explode('.', $permission->name)[0];
        })->mapWithKeys(function ($group, $module) {
            return [ucfirst($module) . ' Management' => $group];
        });
    }

    /**
     * Clear permission cache.
     */
    private function clearPermissionCache()
    {
        Cache::forget(config('permission.cache.key'));
    }
}