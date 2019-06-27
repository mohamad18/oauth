<?php

namespace App\Repositories;

use App\User;

class AuthRepository
{
    protected $user;

    function __construct(User $user)
    {
        $this->user = $user;
    }


    /**
     * Management Hooq Account Controller
     * method : finde
     * action : for find data hooq account base on id
     * dev : Aspullah
    */
    public function find($id)
    {
        return $this->hooq->find($id);
    }

    /**
     * Management Hooq Account Controller
     * method : showAll
     * action : for display data on datatable and pagination
     * dev : Aspullah
    */
    public function showAll($order, $limit, $skip, $where=[], $whereRaw=NULL)
    {
        $model = $this->hooq;
        $countAll = $model->count();

        if ($whereRaw) {
            $model = $model->whereRaw($whereRaw);
        }

        foreach ($order as $key => $value){
            $model = $model->orderBy($key,$value);
        }

        foreach ($where as $key1 => $value1){
            $model = $model->where($key1,'=',$value1);
        }

        $tabtheme = $model->take((int) $limit)
                        ->skip((int) $skip)
                        ->get()
                        ->all();

        $result = [
            'total' => $countAll,
            'data' => $tabtheme
        ];

        return $result;
    }

    /**
     * Management Hooq Account Controller
     * method : insert
     * action : for post data hooq account
     * dev : Aspullah
    */
    public function insert($input)
    {
        $date = date('Y-m-d H:i:s');
        $user = new User();
        $user->name = $input['name'];
        $user->access = $input['access'];
        $user->nik = $input['nik'];
        $user->email = $input['email'];
        $user->password = $input['password'];
        $user->created_at = $date;
        $user->updated_at = $date;
        $user->save();

        return $user;
    }

    /**
     * Management Hooq Account Controller
     * method : update
     * action : for update data hooq account
     * dev : Aspullah
    */
    public function update($data)
    {
          $hooq = [];
          if($this->hooq){
              $id = $data['id'];
              $date = date('Y-m-d H:i:s');
              $hooq = $this->hooq::where('id',$id)->first();
                  if(!empty($hooq->martbox_id))
                      if(!is_null($data['username'])) $hooq->username  = $data['username'];
                      if(!is_null($data['password'])) $hooq->password  = $data['password'];
                      if(!is_null($data['voucher_code'])) $hooq->voucher_code  = $data['voucher_code'];
                      if(!is_null($data['voucher_description'])) $hooq->voucher_code  = $data['voucher_description'];
                      if(!is_null($data['status'])) $hooq->status  = $data['status'];
                  else
                      if(is_null($data['driver_id'])) $hooq->driver_id  = $data['driver_id'];
                      if(is_null($data['driver_uuid'])) $hooq->driver_uuid  = $data['driver_uuid'];
                      if(is_null($data['martbox_id'])) $hooq->martbox_id  = $data['martbox_id'];
                      if(is_null($data['martbox_display_id'])) $hooq->martbox_display_id  = $data['martbox_display_id'];
              $hooq->updated_at = $date;
              $hooq->save();

              return $hooq;
          }
          return ['code' => 404 , 'msg' => 'hooq not found'];
    }

    /**
     * Management Hooq Account Controller
     * method : insert
     * action : for destroy data hooq account
     * dev : Aspullah
    */
    public function destroy($id)
    {
        $date = date('Y-m-d H:i:s');
        $hooq= $this->hooq::where('id',$id)->first();

        $hooq->updated_at = $date;
        $hooq->delete();

        return $hooq;
    }

    /**
     * Management Hooq Account Controller
     * method : exist
     * action : for ceck data martbox_id at hooq account
     * dev : Aspullah
    */
    public function exist($field, $inputData)
    {
        return $this->user->where($field, $inputData)
                        ->first();
    }

    /**
     * Management Hooq Account Controller
     * method : findLabel
     * action : for detail data assignment base on martbox_display_id
     * dev : Aspullah
    */
    public function findLabel($name)
    {
        $model = $this->hooq->where('martbox_display_id', $name)->first();
        return $model;
    }
}
