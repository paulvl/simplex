<?php

namespace Simplex\Http\Controllers;

use Illuminate\Http\Request;
use Simplex\Http\StatusCode;
use Simplex\Repositories\RestRepository;
use Simplex\Validation\ValidationTrait;

abstract class RestController extends Controller
{
    use ValidationTrait;

    protected $repository;
    protected $model;
    protected $relationsToEagerLoad;
    protected $createFormRequest;
    protected $updateFormRequest;

    public function __construct()
    {
        if (empty($this->model)) {
            $this->isRestRepository($this->repository);
            $this->repository = new $this->repository;
        }
        $this->isEloquentModel($this->model);
        $this->repository = new RestRepository($this->model, (empty($this->relationsToEagerLoad) ? [] : $this->relationsToEagerLoad));
    }

    public function getIndex(Request $request, $idOrModel =  null)
    {
        if (is_null($idOrModel))
        {
            $result = $this->repository->getAll($request);
        } else {
            $result = $this->repository->getById($idOrModel);
        }
        return $this->response($result);
    }

    public function postIndex(Request $request)
    {
        if (method_exists($this, 'create')) {
            return $this->create($request);
        }
        if (!empty($this->createFormRequest)) {
            $this->initiateFormRequest($request, $this->createFormRequest);
        }
        if (method_exists($this, 'createAfterValidation')) {
            return $this->createAfterValidation($request);
        }
        return $this->response($this->repository->create($request));
    }

    public function putIndex(Request $request, $idOrModel)
    {
        if (method_exists($this, 'update')) {
            return $this->update($request);
        }
        if (!empty($this->updateFormRequest)) {
            $this->initiateFormRequest($request, $this->updateFormRequest);
        }
        if (method_exists($this, 'updateAfterValidation')) {
            return $this->updateAfterValidation($request);
        }
        return $this->response($this->repository->update($idOrModel, $request));
    }

    public function deleteIndex(Request $request, $idOrModel)
    {
        if (method_exists($this, 'delete')) {
            return $this->delete($request);
        }
        return $this->response($this->repository->delete($idOrModel, $request));
    }

    protected function initiateFormRequest(Request &$request, $formRequest)
    {
        $this->isFormRequest($formRequest);
        $request = $formRequest::createFromBase($request);
        $request->setContainer(app());
        $request->validate();
    }

    protected function response(array $result)
    {
        return response()->json($result['data'], $result['code']);
    }
}
