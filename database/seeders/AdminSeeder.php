<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\PermissionRegistrar;

class AdminSeeder extends Seeder
{
    /**
     * Seed roles, permissions and THE single store-owner account.
     *
     * The owner is provisioned from config/shop.php (.env: ADMIN_EMAIL /
     * ADMIN_PASSWORD) and is idempotent: re-running converges on one owner
     * and demotes any other admin to a plain customer, so no environment can
     * end up with two admins or a locked-out panel.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            'user-list',
            'user-create',
            'user-edit',
            'user-delete',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission);
        }

        // Roles: admin (full) and user (plain customer)
        $adminRole = Role::findOrCreate('admin');
        $adminRole->givePermissionTo(Permission::all());

        $userRole = Role::findOrCreate('user');
        $userRole->givePermissionTo(['user-list']);

        // ---- The one owner ----
        $ownerEmail = config('shop.owner.email');
        $owner = User::updateOrCreate(
            ['email' => $ownerEmail],
            [
                'name' => config('shop.owner.name'),
                'first_name' => config('shop.owner.name'),
                'password' => Hash::make(config('shop.owner.password')),
                'status' => 'active',
            ]
        );
        $owner->syncRoles([$adminRole->name]);

        // ---- Single-admin invariant: nobody else holds the admin role ----
        User::role('admin')
            ->where('email', '!=', $ownerEmail)
            ->get()
            ->each(function (User $user) use ($userRole) {
                $user->syncRoles([$userRole->name]);
            });
    }
}
