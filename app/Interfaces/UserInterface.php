<?php

namespace App\Interfaces;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

interface UserInterface
{
    /**
     * Recursively builds a genealogy tree starting from the given user.
     *
     * This function traverses the user and their descendants to create
     * a nested array structure representing the hierarchical tree.
     *
     * @param  array  $user  The user data as an associative array, including any children.
     * @return array A nested array representing the user and their descendants.
     */
    public function buildTree(array $user);

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
     * @return Collection List of users matching the criteria.
     */
    public function searchUsers($search = '');

    /**
     * Retrieve paginate users from the database.
     *
     * This method applies a filter to the model to only return users
     * whose 'status' field matches the configured  status value.
     *
     * @return Builder
     */
    public function getPaginateUsers();

    /**
     * Mark users as inactive based on their role and last login date.
     *
     * This method checks users with the specified role and marks them as inactive
     * if they haven't logged in within a certain period (7 days for associates, 30 days for clients).
     *
     * @param  string  $roleName  The name of the role to filter users (e.g., 'associate', 'client').
     * @return void
     */
}
