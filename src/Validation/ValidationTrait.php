<?php

namespace Simplex\Validation;

use Illuminate\Database\Eloquent\Model as EloquentModel;
use Simplex\Exceptions\ModelClassNotReferencedException;
use Simplex\Repositories\RestRepository;
use Simplex\Exceptions\RestRepositoryClassNotReferencedException;
use Illuminate\Foundation\Http\FormRequest;
use Simplex\Exceptions\FormRequestClassNotReferencedException;

trait ValidationTrait
{
	public function isEloquentModel($model, $throwException = true)
	{
		$model = is_string($model) ? new $model : $model;
		if (!($model instanceof EloquentModel)) {
			if ($throwException) {
            	throw new ModelClassNotReferencedException();
            }
            return false;
        }
        return true;
	}

	public function isRestRepository($repository, $throwException = true)
	{
		$repository = is_string($repository) ? new $repository : $repository;
		if (!(($repository) instanceof RestRepository)) {
			if ($throwException) {
            	throw new RestRepositoryClassNotReferencedException();
            }
            return false;
        }
        return true;
	}

	public function isFormRequest($formRequest, $throwException = true)
	{
		$formRequest = is_string($formRequest) ? new $formRequest : $formRequest;
		if (!(($formRequest) instanceof FormRequest)) {
			if ($throwException) {
            	throw new FormRequestClassNotReferencedException();
            }
            return false;
        }
        return true;
	}
}