<?php

namespace App\Repositories;

abstract class AbstractRepository
{
    /**
     * Gets the model.
     *
     * @return  string
     */
    abstract public function getModel();

    /**
     * Return a query object
     *
     * @return  \Illuminate\Database\Eloquent\Builder
     */
    public function query()
    {
        $class = $this->getModel();
        return (new $class())->query();
    }

    /**
     * Create a model
     *
     * @param  array  $data  The data
     *
     * @return  Template
     */
    public function create(array $data)
    {
        $class = $this->getModel();

        /** @var Template $model */
        $model = new $class($data);
        $model->save();

        return $model;
    }

    /**
     * Update template
     *
     * @param  int  $id    The identifier
     * @param  array   $data  The data
     *
     * @return  Template
     */
    public function update($id, array $data)
    {
        $model = $this->find($id);
        $model->update($data);

        return $model;
    }

    /**
     * Delete template
     *
     * @param  int  $id    The identifier
     * @param  array   $data  The data
     *
     * @return  bool
     */
    public function delete($id)
    {
        $model = $this->find($id);
        return $model->delete();
    }

    /**
     * Gets the specified pagination.
     *
     * @param  array  $pagination  The pagination
     *
     * @return  \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function get($pagination = [])
    {
        $defaults = ['per_page' => null, 'columns' => ['*'], 'page_name' => 'page', 'page' => null];
        $pagination = array_values(array_replace($defaults, $pagination));

        $query = $this->query();
        return $query->paginate(...$pagination);
    }

    /**
     * Searches for the first match.
     *
     * @param  int  $id  The identifier
     *
     * @return  Template
     */
    public function find($id)
    {
        $query = $this->query();
        return $query->find($id);
    }
}
