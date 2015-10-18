<?php namespace mfe\core\libs\mvc;

/**
 * Class CBaseRepository
 *
 * @package mfe\core\libs\mvc
 */
abstract class CBaseRepository
{
    /** @var CBaseModel */
    protected $model;

    /**
     * @param CBaseModel $model
     */
    public function __construct(CBaseModel $model)
    {
        $this->model = $model;
    }
}
