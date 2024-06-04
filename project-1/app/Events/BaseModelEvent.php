<?php

namespace App\Events;

use Illuminate\Database\Eloquent\Model;

abstract class BaseModelEvent
{
    /** 
     * @var  Model 
     */
    protected $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * @return Model
     */
    public function getModel()
    {
        return $this->model;
    }
}
