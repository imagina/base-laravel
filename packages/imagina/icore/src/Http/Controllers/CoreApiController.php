<?php

namespace Imagina\Icore\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Imagina\Icore\Transformers\CoreResource;
use Illuminate\Database\Eloquent\Model;
use Imagina\Icore\Repositories\CoreRepository;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

abstract class CoreApiController
{
    public function __construct(
        protected Model          $model,
        protected CoreRepository $modelRepository)
    {
    }

    public function getParamsRequest(Request $request): object
    {
        return (object)[
            'order' => $request->input('order'),
            'page' => $request->input('page', 1),
            'take' => $request->input('take', 12),
            'filter' => (object)(json_decode($request->input('filter', '[]'), true) ?? []),
            'include' => array_filter(explode(',', $request->input('include', '')), fn($val) => $val !== ''),
            'fields' => array_filter(explode(',', $request->input('fields', '')), fn($val) => $val !== ''),
        ];
    }

    protected function getHttpStatusCode(\Exception $e): int
    {
        $validCodes = [204, 400, 401, 403, 404, 406, 409, 422, 502, 503, 504];
        $code = $e->getCode();

        return in_array($code, $validCodes) ? $code : 500;
    }

    public function getErrorResponse(\Exception $e): array
    {
        return [
            'messages' => [['message' => $e->getMessage(), 'type' => 'error']],
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ];
    }

    protected function validateWithModelRules(array $data, string $action): void
    {
        //TODO: Validate translatable rules
        $class = $this->model->requestValidation[$action] ?? null;

        if ($class && class_exists($class)) {
            $rules = new $class($data);
            /** @var \Illuminate\Foundation\Http\FormRequest $formRequest */
            $rules->setContainer(app());
            if (method_exists($rules, 'getValidator')) {
                $validator = $rules->getValidator();
            } else {
                $validator = Validator::make($rules->all(), $rules->rules(), $rules->messages());
            }

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }
        }
    }

    public function pageTransformer($data): array
    {
        return [
            'total' => $data->total(),
            'lastPage' => $data->lastPage(),
            'perPage' => $data->perPage(),
            'currentPage' => $data->currentPage(),
        ];
    }

    /**
     * Controller to create model
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function create(Request $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            //Get model data
            $modelData = $request->input('attributes') ?? [];

            //Validate Request
            $this->validateWithModelRules($modelData, 'create');

            //Create model
            $model = $this->modelRepository->create($modelData);

            //Response
            $response = ['data' => CoreResource::transformData($model)];
            DB::commit(); //Commit to Data Base
        } catch (\Exception $e) {
            DB::rollback(); //Rollback to Data Base
            $status = $this->getHttpStatusCode($e);
            $response = $this->getErrorResponse($e);
        }
        //Return response
        return response()->json($response, $status ?? 200);
    }

    /**
     * Controller To request all model data
     *
     * @return mixed
     */
    public function index(Request $request): JsonResponse
    {
        try {
            //Get Parameters from request
            $params = $this->getParamsRequest($request);

            //Request data to Repository
            $models = $this->modelRepository->getItemsBy($params);

            //Response
            $response = ['data' => $this->modelRepository->getItemsByTransformed($models, $params)];

            //If request pagination add meta-page
            if ($params->page) $response['meta'] = ['page' => $this->pageTransformer($models)];
        } catch (\Exception $e) {
            $status = $this->getHttpStatusCode($e);
            $response = $this->getErrorResponse($e);
        }

        //Return response
        return response()->json($response, $status ?? 200);
    }

    /**
     * Controller to request model by criteria
     *
     * @return mixed
     */
    public function show($criteria, Request $request): JsonResponse
    {
        try {
            //Get Parameters from request
            $params = $this->getParamsRequest($request);

            //Request data to Repository
            $model = $this->modelRepository->getItem($criteria, $params);

            //Throw exception if no found item
            if (!$model) {
                throw new \Exception('Item not found', 404);
            }

            //Response
            $response = ['data' => CoreResource::transformData($model)];
        } catch (\Exception $e) {
            $status = $this->getHttpStatusCode($e);
            $response = $this->getErrorResponse($e);
        }

        //Return response
        return response()->json($response, $status ?? 200);
    }

    /**
     * Controller to update model by criteria
     *
     * @return mixed
     */
    public function update($criteria, Request $request): JsonResponse
    {
        DB::beginTransaction(); //DB Transaction
        try {
            //Get model data
            $modelData = $request->input('attributes') ?? [];
            //Get Parameters from URL.
            $params = $this->getParamsRequest($request);

            //auto-insert the criteria in the data to update
            isset($params->filter->field) ? $field = $params->filter->field : $field = 'id';
            $modelData[$field] = $criteria;

            //Validate Request
            $this->validateWithModelRules($modelData, 'update');

            //Update model
            $model = $this->modelRepository->updateBy($criteria, $modelData, $params);

            //Throw exception if no found item
            if (!$model) throw new \Exception('Item not found', 404);

            //Response
            $response = ['data' => CoreResource::transformData($model)];
            DB::commit(); //Commit to DataBase
        } catch (\Exception $e) {
            DB::rollback(); //Rollback to Data Base
            $status = $this->getHttpStatusCode($e);
            $response = $this->getErrorResponse($e);
        }

        //Return response
        return response()->json($response, $status ?? 200);
    }

    /**
     * Controller to delete model by criteria
     *
     * @return mixed
     */
    public function delete($criteria, Request $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            //Get params
            $params = $this->getParamsRequest($request);

            //Get model data
            $modelData = $request->input('attributes') ?? [];

            //Validate Request
            $this->validateWithModelRules($modelData, 'delete');

            //Delete model
            $this->modelRepository->deleteBy($criteria, $params);

            //Response
            $response = ['data' => 'Item deleted'];
            DB::commit(); //Commit to Data Base
        } catch (\Exception $e) {
            DB::rollback(); //Rollback to Data Base
            $status = $this->getHttpStatusCode($e);
            $response = $this->getErrorResponse($e);
        }

        //Return response
        return response()->json($response, $status ?? 200);
    }

    /**
     * Controller to delete model by criteria
     *
     * @return mixed
     */
    public function restore($criteria, Request $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            //Get params
            $params = $this->getParamsRequest($request);

            //Delete model
            $model = $this->modelRepository->restoreBy($criteria, $params);

            //Throw exception if no found item
            if (!$model) throw new \Exception('Item not found', 404);

            //Response
            $response = ['data' => CoreResource::transformData($model)];
            DB::commit(); //Commit to Data Base
        } catch (\Exception $e) {
            DB::rollback(); //Rollback to Data Base
            $status = $this->getHttpStatusCode($e);
            $response = $this->getErrorResponse($e);
        }

        //Return response
        return response()->json($response, $status ?? 200);
    }

    /**
     * Controller to do a bulk order of a model
     *
     * @return mixed
     */
    public function bulkOrder(Request $request): JsonResponse
    {
        DB::beginTransaction(); //DB Transaction
        try {
            //Get model data
            $data = $request->input('attributes') ?? [];
            //Get Parameters from URL.
            $params = $this->getParamsRequest($request);

            //Update model
            $bulkOrderResult = $this->modelRepository->bulkOrder($data, $params);

            //Response
            $response = ['data' => CoreResource::transformData($bulkOrderResult)];
            DB::commit(); //Commit to DataBase
        } catch (\Exception $e) {
            DB::rollback(); //Rollback to Data Base
            $status = $this->getHttpStatusCode($e);
            $response = $this->getErrorResponse($e);
        }

        //Return response
        return response()->json($response, $status ?? 200);
    }

    /**
     * Controller to request all model dashboard
     *
     * @param $entityClass
     * @return mixed
     */
    public function dashboardIndex(Request $request): JsonResponse
    {
        try {
            //Get Parameters from request
            $params = $this->getParamsRequest($request);

            //Request data to Repository
            $dashboardData = $this->modelRepository->getDashboard($params);

            //Response
            $response = ['data' => $dashboardData];
        } catch (\Exception $e) {
            $status = $this->getHttpStatusCode($e);
            $response = ['messages' => [['message' => $this->getErrorMessage($e), 'type' => 'error']]];
        }

        //Return response
        return response()->json($response, $status ?? 200);
    }
}
