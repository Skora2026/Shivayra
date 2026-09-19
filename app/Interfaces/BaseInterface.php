<?php

namespace App\Interfaces;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

interface BaseInterface
{
    /**
     * function to add the data
     *
     * @return mixed
     */
    public function addData(array $data);

    /**
     * function to update the data
     *
     * @return mixed
     */
    public function updateData(string $id, array $updatedData);

    /**
     * function to get the data by Id
     *
     * @return mixed
     */
    public function getDataById(string $id);

    /**
     * function to get all the data
     *
     * @return mixed
     */
    public function getAllData();

    /**
     * function to delete the data by id
     *
     * @return mixed
     */
    public function deleteData(string $id);

    /**
     * function to get the data on the basis of filter provided
     *
     * @return mixed
     */
    public function getDataOnBasisOfFilter(array $filters);

    /**
     * function to get all the trashed data
     *
     * @return mixed
     */
    public function getTrashData();

    /**
     * function to restore the data by id
     *
     * @return mixed
     */
    public function restoreData(int $id);

    /**
     * function to permanently delete the data by id
     *
     * @return mixed
     */
    public function deleteTrashData(int $id);

    /**
     * function to get the data from the request
     *
     * @return array
     */
    public function getDataFromRequest(Request $request);

    /**
     * Update an existing record that matches the given conditions, or create a new one.
     *
     * @param  array  $conditions  Key-value pairs to identify the record.
     * @param  array  $data  Data to update or insert into the record.
     * @return Model
     */
    public function updateOrCreateData(array $conditions, array $data);
}
