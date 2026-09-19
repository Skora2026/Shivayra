<?php

namespace App\Services;

use App\Interfaces\UserInterface;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class UserService extends BaseService implements UserInterface
{
    /**
     * Create a new class instance.
     */
    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    /**
     * function to get the data from the request
     *
     * @param  mixed  $request
     * @return mixed
     */
    public function getDataFromRequest($request)
    {
        return $request->only(
            [
                'first_name',
                'last_name',
                'email',
                'profile_pic',
                'password',
                'status',
                'send_notification',
                'role_id',
                'date_of_birth',
                'pin_code',
                'guardian_name',
                'contact_number',
                'phone_office_res',
                'nominee_name',
                'gender',
                'address',
                'relationship',
                'sponsor_name',
                'sponsor_id',
                'referral_code',
                'wallet_balance',
                'total_income',
                'total_withdrawn',
                'parent_id',
                'lft',
                'rgt',
                'depth',
                'project_id',
                'rank_id',
                'paid_visit_count',
                'rate_per_sq_yard',
            ]
        );
    }

    /**
     * Build a tree structure from the user and their children recursively.
     *
     * @param  User  $user
     * @return array
     */
    public function buildTree($user, $offset = 0, $limit = 3)
    {
        $children = $user->children->slice($offset, $limit);
        $childrenCount = $children->count();
        if ($childrenCount < $limit) {
            for ($i = 0; $i < $limit - $childrenCount; $i++) {
                $children->push(null);
            }
        }
        $rootNode = [
            'text' => ['title' => $user->formatted_id, 'name' => $user->name],
            'link' => ['href' => route('user.tree-view', ['id' => encrypt($user->id)])],
            'children' => [],
        ];
        foreach ($children as $child) {
            if ($child) {
                $grandChildren = $child->children->take(3);
                $grandCount = $grandChildren->count();
                if ($grandCount < 3) {
                    for ($i = 0; $i < 3 - $grandCount; $i++) {
                        $grandChildren->push(null);
                    }
                }
                $childNode = [
                    'text' => ['title' => $child->formatted_id, 'name' => $child->name],
                    'link' => ['href' => route('user.tree-view', ['id' => encrypt($child->id)])],
                    'children' => [],
                ];
                foreach ($grandChildren as $grand) {
                    if ($grand) {
                        $childNode['children'][] = [
                            'text' => ['title' => $grand->formatted_id, 'name' => $grand->name],
                            'link' => ['href' => route('user.tree-view', ['id' => encrypt($grand->id)])],
                        ];
                    } else {
                        $childNode['children'][] = [
                            'text' => ['title' => 'Available', 'name' => ''],
                            'HTMLclass' => 'node-available',
                        ];
                    }
                }
                $rootNode['children'][] = $childNode;
            } else {
                $rootNode['children'][] = [
                    'text' => ['title' => 'Available', 'name' => ''],
                    'HTMLclass' => 'node-available',
                    'children' => array_fill(0, 3, [
                        'text' => ['title' => 'Available'],
                        'HTMLclass' => 'node-available',
                    ]),
                ];
            }
        }

        return $rootNode;
    }

    /**
     * Search users by first name or last name.
     *
     * If a search term is provided, it filters users whose first or last name
     * contains the search term (case-insensitive).
     *
     * If the search term is empty, it returns the first 20 users without filtering.
     *
     * Results are limited to 20 records in both cases.
     *
     * @param  string  $search  The search term to filter users by name.
     * @return \Illuminate\Database\Eloquent\Collection List of users matching the criteria.
     */
    public function searchUsers($search = '')
    {
        $query = $this->model
            ->where(function ($query) use ($search) {
                $query->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%");
            });

        return $query->get(['id', 'first_name', 'last_name']);
    }

    /**
     * Retrieve paginate users from the database.
     *
     * This method applies a filter to the model to only return users
     * whose 'status' field matches the configured  status value.
     *
     * @return Builder
     */
    public function getPaginateUsers()
    {
        return $this->model->query();
    }
}
