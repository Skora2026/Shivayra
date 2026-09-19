<?php

namespace App\Services;

use App\Interfaces\BaseInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class BaseService implements BaseInterface
{
    public $model;

    /**
     * Create a new class instance.
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * function to add the data
     *
     * @return mixed
     */
    public function addData(array $data)
    {
        return $this->model::create($data);
    }

    /**
     * function to update the data
     *
     * @return mixed
     */
    public function updateData(string $id, array $updatedData)
    {
        $data = $this->model::findOrFail($id);
        $data->update($updatedData);

        return $data;
    }

    /**
     * function to get the data by Id
     *
     * @return mixed
     */
    public function getDataById(string $id)
    {
        return $this->model::find($id);
    }

    /**
     * function to get all the data
     *
     * @return mixed
     */
    public function getAllData()
    {
        return $this->model::all();
    }

    /**
     * function to delete the data by id
     *
     * @return mixed
     */
    public function deleteData(string $id)
    {
        $data = $this->model::find($id);

        if (! $data) {
            return null; // already gone — stale admin tab must not fatal
        }

        $data->delete();

        return $data;
    }

    /**
     * function to get the data on the basis of filter provided
     *
     * @return mixed
     */
    public function getDataOnBasisOfFilter(array $filters)
    {
        $query = $this->model::query();
        foreach ($filters as $key => $value) {
            $query->where($key, $value);
        }

        return $query->get();
    }

    /**
     * function to get all the trashed data
     *
     * @return mixed
     */
    public function getTrashData()
    {
        return $this->model::onlyTrashed()->get();
    }

    /**
     * function to restore the data by id
     *
     * @return mixed
     */
    public function restoreData(int $id)
    {
        $data = $this->model::onlyTrashed()->findOrFail($id);
        $data->restore();

        return $data;
    }

    /**
     * function to permanently delete the data by id
     *
     * @return mixed
     */
    public function deleteTrashData(int $id)
    {
        $data = $this->model::onlyTrashed()->findOrFail($id);
        $data->forceDelete();

        return $data;
    }

    /**
     * function to get the data from the request
     *
     * @return array
     */
    public function getDataFromRequest(Request $request)
    {
        return $request->only([]);
    }

    /**
     * Check if a record exists in the database that matches the given conditions.
     *
     * @param  array  $conditions  An associative array of column-value pairs to match.
     * @return Model|null The first matching model instance, or null if no match is found.
     */
    public function exists(array $conditions)
    {
        return $this->model::where($conditions)->first();
    }

    /**
     * Update an existing record that matches the given conditions, or create a new one.
     *
     * This method checks for a record matching the provided conditions.
     * If found, it updates it with the given data; if not found, it creates a new record.
     *
     * @param  array  $conditions  Key-value pairs used to locate the existing record.
     * @param  array  $data  Data to be used for updating or creating the record.
     * @return Model The updated or newly created model instance.
     */
    public function updateOrCreateData(array $conditions, array $data)
    {
        return $this->model::updateOrCreate($conditions, $data);
    }
}
