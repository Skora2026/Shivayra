<?php

namespace App\Services;

use App\Models\Role;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class RoleService extends BaseService
{
    /**
     * Create a new class instance.
     */
    public function __construct(Role $model)
    {
        parent::__construct($model);
    }

    /**
     * Function to get all the role from the request
     *
     * @param  object  $requestData
     * @return array
     */
    public function getDataFromRequest(Request $requestData)
    {
        return $requestData->only([
            'name',
            'parents',
            'children',
        ]);
    }

    /**
     * Get roles by their IDs.
     *
     * @return Collection
     */
    public function getRoleIds(array $roleIds)
    {
        return $this->model::whereIn('id', $roleIds)->get();
    }
}
