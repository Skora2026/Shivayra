<?php

namespace App\Http\Controllers\Admin;

use App\DataTables\UserDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\CreateRequest;
use App\Http\Requests\User\UpdateRequest;
use App\Services\UserService;

class UserController extends Controller
{
    protected $userService;

    /**
     * Create a new Controller instance.
     */
    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Render the users grid using Yajra DataTables.
     */
    public function index(UserDataTable $dataTable)
    {
        return $dataTable->render('admin.users.index');
    }

    /**
     * Show the user creation form.
     */
    public function create()
    {
        return view('admin.users.create');
    }

    /**
     * Store a newly created user.
     */
    public function store(CreateRequest $request)
    {
        $data = $this->userService->getDataFromRequest($request);

        // Add full name concatenated
        $data['name'] = trim(($data['first_name'] ?? '').' '.($data['last_name'] ?? ''));

        // Hash password if not cast automatically
        if (isset($data['password'])) {
            $data['password'] = bcrypt($data['password']);
        }

        // Single-admin invariant: accounts created here are always customers.
        unset($data['role_id']);

        $user = $this->userService->addData($data);
        $user->syncRoles(['user']);

        return redirect()->route('admin.users.index')->with('success', 'User created successfully.');
    }

    /**
     * Show the edit user form.
     */
    public function edit($id)
    {
        $user = $this->userService->getDataById($id);
        $userRole = $user->roles->first();

        return view('admin.users.edit', compact('user', 'userRole'));
    }

    /**
     * Update an existing user.
     */
    public function update(UpdateRequest $request, $id)
    {
        $target = $this->userService->getDataById((int) $id);

        $data = $this->userService->getDataFromRequest($request);

        // Add full name concatenated
        $data['name'] = trim(($data['first_name'] ?? '').' '.($data['last_name'] ?? ''));

        // Remove password if empty
        if (empty($data['password'])) {
            unset($data['password']);
        } else {
            $data['password'] = bcrypt($data['password']);
        }

        // Single-admin invariant: roles are never editable from the UI.
        unset($data['role_id']);

        // The owner account can never be deactivated from the UI
        if ($target && strtolower($target->email) === strtolower(config('shop.owner.email'))) {
            $data['status'] = 'active';
        }

        $user = $this->userService->updateData($id, $data);

        return redirect()->route('admin.users.index')->with('success', 'User updated successfully.');
    }

    /**
     * Delete an existing user.
     */
    public function destroy($id)
    {
        // An admin must not be able to delete their own account from the UI
        if ((int) $id === (int) auth()->id()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'You cannot delete your own account.');
        }

        // The single owner account is irreplaceable from the UI
        $target = $this->userService->getDataById((int) $id);
        if ($target && strtolower($target->email) === strtolower(config('shop.owner.email'))) {
            return redirect()->route('admin.users.index')
                ->with('error', 'The store owner account cannot be deleted.');
        }

        $this->userService->deleteData($id);

        return redirect()->route('admin.users.index')->with('success', 'User deleted successfully.');
    }
}
