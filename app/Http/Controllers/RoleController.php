<?php

namespace App\Http\Controllers;

use App\Services\RoleService;
use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    protected $roleService;

    public function __construct(RoleService $roleService)
    {
        $this->roleService = $roleService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        abort_unless(auth()->user()->can('role.view'), 403);
        $search = $request->get('search');
        $roles = $this->roleService->getAllRoles($search);
        return view('roles.index', compact('roles', 'search'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        abort_unless(auth()->user()->can('role.create'), 403);
        $groupedPermissions = $this->roleService->getGroupedPermissions();
        return view('roles.create', compact('groupedPermissions'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRoleRequest $request)
    {
        abort_unless(auth()->user()->can('role.create'), 403);
        $this->roleService->createRole($request->validated());

        return redirect()->route('roles.index')->with('success', 'Role created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Role $role)
    {
        abort_unless(auth()->user()->can('role.view'), 403);
        return view('roles.show', compact('role'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Role $role)
    {
        abort_unless(auth()->user()->can('role.edit'), 403);
        $groupedPermissions = $this->roleService->getGroupedPermissions();
        return view('roles.edit', compact('role', 'groupedPermissions'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRoleRequest $request, Role $role)
    {
        abort_unless(auth()->user()->can('role.edit'), 403);
        $this->roleService->updateRole($role->id, $request->validated());

        return redirect()->route('roles.index')->with('success', 'Role updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Role $role)
    {
        abort_unless(auth()->user()->can('role.delete'), 403);
        $this->roleService->deleteRole($role->id);

        return redirect()->route('roles.index')->with('success', 'Role deleted successfully.');
    }
}