<?php

namespace App\Transformers;

class SkeletonTransformer extends TransformerAbstract
{
    /**
     * Transform object into a generic array
     *
     * @var $resource
     * @return array
     */
    public function transform($resource)
    {
        return $resource;
    }
}
