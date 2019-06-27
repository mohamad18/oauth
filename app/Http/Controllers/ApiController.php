<?php

namespace App\Http\Controllers;

use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use League\Fractal\Manager;
use League\Fractal\Resource\ResourceAbstract;
use League\Fractal\TransformerAbstract;
use Illuminate\Support\Facades\Request;

class ApiController extends Controller
{
    protected $statusCode = 200;
    protected $meta = [];
    protected $fractal;

    const CODE_WRONG_ARGS = 'GEN-FUBARGS';
    const CODE_NOT_FOUND = 'GEN-LIKETHEWIND';
    const CODE_INTERNAL_ERROR = 'GEN-AAAGGH';
    const CODE_FORBIDDEN = 'GEN-GTFO';
    const CODE_UNAUTHORIZED = 'GEN-MAYBGTFO';

    public function __construct()
    {
        parent::__construct();

        $this->fractal = new Manager;

        // Are we going to try and include embedded data?
        $this->fractal->parseIncludes(explode(',', Request::get('include')));
    }

    /**
     * Getter for statusCode
     *
     * @return mixed
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * Setter for statusCode
     *
     * @param int $statusCode Value to set
     *
     * @return self
     */
    public function setStatusCode($statusCode)
    {
        $this->statusCode = $statusCode;

        return $this;
    }

    /**
     * Setter for meta
     *
     * @param int $meta Values to set
     *
     * @return self
     */
    public function setMeta(array $meta)
    {
        $this->meta = $meta;

        return $this;
    }

    /**
     * Create the response for an item.
     *
     * @param  mixed                                $item
     * @param  \League\Fractal\TransformerAbstract  $transformer
     * @param  int                                  $status
     * @param  array                                $headers
     * @return Response
     */
    protected function respondWithItem($item, TransformerAbstract $transformer, $statusCode = 200, array $headers = [])
    {
        $resource = new Item($item, $transformer);
        $resource->setMeta($this->meta);

        return $this->buildResourceResponse($resource, $statusCode, $headers);
    }

    /**
     * Create the response for a collection.
     *
     * @param  mixed                                $collection
     * @param  \League\Fractal\TransformerAbstract  $transformer
     * @param  int                                  $status
     * @param  array                                $headers
     * @return Response
     */
    protected function respondWithCollection($collection, TransformerAbstract $transformer, $statusCode = 200, array $headers = [])
    {
        $resource = new \League\Fractal\Resource\Collection($collection, $transformer);
        $resource->setMeta($this->meta);

        return $this->buildResourceResponse($resource, $statusCode, $headers);
    }

    /**
     * Create the response for a resource.
     *
     * @param  \League\Fractal\Resource\ResourceAbstract  $resource
     * @param  int                                        $status
     * @param  array                                      $headers
     * @return Response
     */
    protected function buildResourceResponse(ResourceAbstract $resource, $statusCode = 200, array $headers = [])
    {
        $data = $this->fractal->createData($resource)->toArray();

        $response = [
            'status' => empty($data['data'])?false:true,
            'http_code' => $statusCode,
            'message' => empty($data['data'])?'Data Empty':'success',
            'data' => isset($data)?$data['data']:null,
            'meta' => isset($data['meta'])?$data['meta']:null
        ];

        return response()->json(
            $response,
            $statusCode,
            $headers
        );
    }

    protected function respondWithArray(array $array, array $headers = [])
    {
        $response = response()->json($array, $this->statusCode, $headers);

        return $response;
    }

    protected function respondWithError($message, $errorCode)
    {
        if ($this->statusCode === 200) {
            trigger_error(
                "You better have a really good reason for erroring on a 200...",
                E_USER_WARNING
            );
        }

        $response = [
            'status' => false,
            'http_code' => $this->statusCode,
            'message' => $message,
            'data' => null,
            'meta' => null
        ];

        return $this->respondWithArray($response);
    }

    /**
     * Generates a Response with a 403 HTTP header and a given message.
     *
     * @return  Response
     */
    public function errorForbidden($message = 'Forbidden')
    {
        return $this->setStatusCode(403)->respondWithError($message, self::CODE_FORBIDDEN);
    }

    /**
     * Generates a Response with a 500 HTTP header and a given message.
     *
     * @return  Response
     */
    public function errorInternalError($message = 'Internal Error')
    {
        return $this->setStatusCode(500)->respondWithError($message, self::CODE_INTERNAL_ERROR);
    }

    /**
     * Generates a Response with a 404 HTTP header and a given message.
     *
     * @return  Response
     */
    public function errorNotFound($message = 'Resource Not Found')
    {
        return $this->setStatusCode(404)->respondWithError($message, self::CODE_NOT_FOUND);
    }

    /**
     * Generates a Response with a 400 HTTP header and a given message.
     *
     * @return  Response
     */
    public function errorWrongArgs($message = 'Wrong Arguments')
    {
        return $this->setStatusCode(400)->respondWithError($message, self::CODE_WRONG_ARGS);
    }

    /**
     * Generates a Response with a 403 HTTP header and a given message.
     *
     * @return  Response
     */
    public function errorUnauthorized($message = 'Unauthorized')
    {
        return $this->setStatusCode(401)->respondWithError($message, self::CODE_UNAUTHORIZED);
    }

    protected function checkRole($access = [], $role = ['admin'])
    {
        $response = [
          'access' => false,
          'access_uuid'=> null
        ];
        // $searchKey = "uuid_".$role;

        foreach ($access as $acc) {

            // $roles = (array)$acc;
            // if (array_key_exists($searchKey,$roles)) {
            if (in_array($acc->group,$role)) {
              $access_uuid = "uuid_".$acc->group;
              $response = [
                'access' => true,
                'access_group' => $acc->group,
                'access_uuid'=> $acc->$access_uuid
                // 'access_uuid'=> $roles[$searchKey]
              ];
              break;
            }
        }

        return $response;
    }
}
