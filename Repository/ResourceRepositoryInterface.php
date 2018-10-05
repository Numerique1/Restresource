<?php
namespace Numerique1\Components\Restresources\Repository;

/**
 * Class ResourceRepositoryInterface
 * @package Numerique1\Components\Restresources\Repository
 */
interface ResourceRepositoryInterface
{
    /**
     * @param array $filters
     */
    public function cget(array $filters);

    /**
     * @param $id
     */
    public function get($id);

}