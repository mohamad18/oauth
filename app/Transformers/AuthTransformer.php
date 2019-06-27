<?php

namespace App\Transformers;

use App\User;
use League\Fractal\TransformerAbstract;

class AuthTransformer extends TransformerAbstract
{
    public function transform(User $user)
    {
        return $user->toArray();
    }
}
