<?php

namespace App\Http\Controllers;


use App\User;
use Auth;
use App\Http\Controllers\Controller;
use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use App\Repositories\AuthRepository;
use App\Transformers\AuthTransformer;
use Firebase\JWT\ExpiredException;
use Illuminate\Support\Facades\Hash;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use Laravel\Lumen\Routing\Controller as BaseController;
use Validator;
use Illuminate\Http\JsonResponse;

class AuthController extends ApiController
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    private $request;
    private $user;

    public function __construct(Request $request, AuthRepository $user)
    {
        parent::__construct();

        $this->request = $request;
        $this->user = $user;
        $this->statuscode = 200;
    }

    //

    /**
     * Create a new token.
     *
     * @param  \App\User   $user
     * @return string
     */
    protected function jwt(User $user) {
        $payload = [
            'iss' => "lumen-jwt", // Issuer of the token
            'sub' => $user->id, // Subject of the token
            'iat' => time(), // Time when JWT was issued.
            'exp' => time() + 60*60 // Expiration time
        ];

        // As you can see we are passing `JWT_SECRET` as the second parameter that will
        // be used to decode the token in the future.
        return JWT::encode($payload, env('JWT_SECRET'));
    }

    /**
     * register a user and return the token if the provided credentials are correct.
     *
     * @param  \App\User   $user
     * @return mixed
     */
    public function authenticate(User $user) {
        // code
        $this->validate($this->request, [
            'nik'     => 'required|int',
            'password'  => 'required'
        ]);

        // Find the user by nik
        $user = User::where('nik', $this->request->input('nik'))->first();
        // $user = $user->attributesToArray());

        // if user not exist, please response with error
        if (empty($user))
            return $this->errorWrongArgs('User not registered');

        // Verify the password and generate the token
        if (($this->request->get('nik') && $this->request->get('password'))) {
            if (Hash::check($this->request->input('password'), $user->password)) {
                if ($this->request->get('nik'))
                    // $credentials = $this->request->only('nik', 'password');

                    date_default_timezone_set("Asia/Jakarta");
                    $d = date("Y-m-d h:i:sa");

                    $item = [
                        "nik" => $user->nik,
                        "name" => $user->name,
                        "access" => $user->access,
                        "created" => $user->created_at
                    ];

                    $data = fractal()
                            ->item($user)
                            ->transformWith(new AuthTransformer)
                            ->toArray();

                    $account = base64_encode(json_encode($data));
                    $token = $this->jwt($user);

                    $users = [
                        "user" => $account,
                        "token" => $token,
                        "login_date" => $d
                    ];
                    $meta = "";

                    $response = [
                        'status' => empty($data['data'])?false:true,
                        'http_code' => $this->statuscode,
                        'message' => empty($data['data'])?'Data Empty':'success',
                        'data' => isset($data)?$users:null,
                        'meta' => $meta?$meta:null
                    ];

                    // return fractal()
                    //     -> item($user)
                    //     -> transformWith(new AuthTransformer)
                    //     -> addMeta([
                    //         'token' => $this->jwt($user)
                    //     ])
                    //     -> toArray();
                    // $this->setMeta([
                    //         'token' => $this->jwt($user)
                    //     ]);
                    // return $this->respondWithItem($user, new AuthTransformer);

            }else {
                return $this->errorUnauthorized('Wrong Password');
            }

            if (!$token = $this->jwt($user))
                return $this->errorUnauthorized('Unauthorized');

            return response()->json(
                $response,
                $this->statuscode
            );

        }else {
            return $this->errorUnauthorized('Unauthorized');
        }

        // Bad Request response
        return response()->json([
            'error' => 'nik or password is wrong.'
        ], 400);
    }

    /**
     * add a user and return result.
     *
     * @param  \App\User   $user
     * @return mixed
     */
    public function add(Request $request){

        $validator = Validator::make($input = $request->all(), [
                        'name' => 'required|string',
                        'access'=>'required|string',
                        'nik'=>'required|int',
                        'email'=>'required|string',
                        'password'=>'required'
                        // 'c_password' => 'required|same:password'
                    ]);

        // if ($validator->fails()) {
        //     return $this->errorWrongArgs($validator->errors()->first());
        // }
        // dd($input);
        if ($validator->fails())
            return response()->json(['error'=>$validator->errors()], 401);

        // Find the user by nik
        $user = $this->user->exist('nik', $input['nik']);
        if ($user)
            return response()->json(['error' => 'data does exist.'], 400);

        try {
            $payload = [];
            $payload['name'] = $input['name'];
            $payload['access'] = $input['access'];
            $payload['nik'] = $input['nik'];
            $payload['email'] = $input['email'];
            $payload['password'] = $this->bcrypt($input['password']);

            $user = $this->user->insert($payload);
            // dd($user);
            // $fractal = new Manager();
            // $resource = new Item($user, new AuthTransformer);

            // return fractal($user, new AuthTransformer())->respond(200, [], JSON_PRETTY_PRINT);
            // return $fractal->createData($resource)->toJson();
            return $this->respondWithItem($user, new AuthTransformer);
            // return fractal($user, new AuthTransformer())->respond(function(JsonResponse $response) {
            //     $response
            //         ->setStatusCode(403)
            //         ->header('a-header', 'a value')
            //         ->withHeaders([
            //             'another-header' => 'another value',
            //             'yet-another-header' => 'yet another value',
            //         ]);
            // });
        }
        catch(Expection $e) {
            return $this->errorWrongArgs($e->getMessage());
        }

        // $payload['name'] = $input['name'];
        // $payload['access'] = $input['access'];
        // $payload['nik'] = $input['nik'];
        // $payload['password'] = $this->bcrypt($input['password']);
        // $user = User::create($payload);
        // dd($user);
        // return $this->item($user, new AuthTransformer);
        //
        // $success['token'] =  $this->jwt($user);
        // $success['name'] =  $user->name;
        //
        // return response()
        //     ->json(['success'=>$success], $this->successStatus);

    }

    /**
     * bycrypt password and return result.
     *
     * @param  \App\User   $user
     * @return mixed
     */
    private function bcrypt($value, $options = []){
        return app('hash')->make($value, $options);
    }

}
