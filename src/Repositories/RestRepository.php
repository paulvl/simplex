<?php

namespace Simplex\Repositories;

use Illuminate\Http\Request;
use Simplex\Validation\ValidationTrait;
use Simplex\Http\StatusCode;

class RestRepository
{
    use ValidationTrait;

    protected $model;
    protected $relationsToEagerLoad = [];

    public function __construct($model, $relationsToEagerLoad = [])
    {
        $this->isEloquentModel($model);
        $this->model = $model;
        $this->relationsToEagerLoad = empty($relationsToEagerLoad) ? $this->relationsToEagerLoad : $relationsToEagerLoad;
    }

    public function getAll(Request $request)
    {
        return $this->assembleResult($this->query($request), StatusCode::OK);
    }

    public function getById($idOrModel)
    {
        if (!$this->isEloquentModel($idOrModel, false)) {
            $obj = $this->model::find($id);
            if (!is_null($obj)) {
                $obj = $obj->load($this->relationsToEagerLoad);
            }
        }
        else {
            $obj = $idOrModel->load($this->relationsToEagerLoad);
        }
        return $this->assembleResult($obj, is_null($obj) ? StatusCode::NOT_FOUND : StatusCode::OK);
    }

    public function create(Request $request)
    {
        $this->model::create($request->all());
        return $this->assembleResult($this->getSuccessfullCreationMessage(), StatusCode::CREATED);
    }

    public function update($obj, Request $request)
    {
        $data = $request->all();
        if (!($obj instanceof $this->model)) {
            $obj = $this->model::find($id);
        }
        $fillableAttrs = $obj->getfillable();
        foreach ($fillableAttrs as $attr) {
            if (!empty($data[$attr])) {
                $obj->$attr = $data[$attr];
            }
        }
        $obj->save();
        return $this->assembleResult($this->getSuccessfullUpdateMessage(), StatusCode::NO_CONTENT);
    }

    public function delete($obj)
    {
        if (!($obj instanceof $this->model)) {
            $this->model::destroy($obj);
        }
        else {
            $obj->delete();
        }
        return $this->assembleResult($this->getSuccessfullEliminationMessage(), StatusCode::NO_CONTENT);
    }

    public function query(Request $request)
    {
        $query =  $this->model::with($this->relationsToEagerLoad);

        if ($request->sort) {
            list($sortCol, $sortDir) = explode('|', $request->sort);
            $query = $query->orderBy($sortCol, $sortDir);
        } else {
            $query = $query->orderBy('id', 'asc');
        }

        if ($request->where) {
            $queryConstraints = explode('|', $request->where);
            foreach ($queryConstraints as $constraint) {
                list($col, $val) = explode(',', $constraint);
                $query = $query->where($col, $val);
            }
        }

        if ($request->paginate) {
            $perPage = $request->per_page ? (($intVal = intval($request->per_page)) <= 0 ? null : $intVal) : null;
            $query = $query->paginate($perPage);
        } else {
            $query = $query->get();
        }

        return $query;
    }

    protected function assembleResult($data, $code)
    {
        return [
            'data' => $data,
            'code' => $code
        ];
    }

    protected function getSuccessfullCreationMessage()
    {
        return 'Record created successfully';
    }

    protected function getSuccessfullUpdateMessage()
    {
        return 'Record updated successfully';
    }

    protected function getSuccessfullEliminationMessage()
    {
        return 'Record deleted successfully';
    }
}