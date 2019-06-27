<?php

namespace App\Transformers;

use League\Fractal\TransformerAbstract as BaseTransformerAbstract;

class TransformerAbstract extends BaseTransformerAbstract
{
    protected function skeleton()
    {
        return new SkeletonTransformer;
    }
}
