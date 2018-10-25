<?php
namespace Numerique1\Components\Restresources\Repository;

/**
 * Class ResourceRepositoryInterface
 * @package Numerique1\Components\Restresources\Repository
 */
interface ResourceRepositoryInterface
{
    /**
     * @param array        $filters
     * @param array|null   $sort
     * @param integer|null $limit
     * @param integer|null $offset
     */
    public function cget(array $filters, array $sort = null, $limit = null, $offset = null);

    /**
     * @param $id
     */
    public function get($id);

}