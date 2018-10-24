<?php

namespace Numerique1\Components\Restresources\Repository;
use Doctrine\ORM\QueryBuilder;

/**
 * Trait EntityMetadataFilterTrait
 * @package Numerique1\Components\Restresources\Repository
 */
trait EntityMetadataFilterTrait
{

    /**
     * Check if filters name exists in the EntityMetadata:fieldNames and filter with a LIKE value%
     * @param array $filters
     * @param Doctrine\ORM\QueryBuilder|null $qb
     * @return Doctrine\ORM\QueryBuilder
     */
    public function filterByMetadataFieldNames(array $filters, QueryBuilder $qb = null)
    {
        if (!$qb) {
            $qb = $this->createQueryBuilder('entity');
        }
        foreach ($filters as $name => $value) {
            if(!is_string($value)){
                continue;
            }
            $fields = $this->getClassMetadata()->getFieldNames();
            if (in_array($name, $fields)) {
                $this->createGuessExprFilter($qb,"LOWER({$qb->getRootAliases()[0]}.{$name})", $value);
            }
        }
        return $qb;
    }

    /**
     * @param QueryBuilder $qb
     * @param string $path
     * @param string $value
     */
    public function createGuessExprFilter(QueryBuilder &$qb, string $path, string $value)
    {
        list($expr, $value) = $this->guessExpr($value);
        if(!empty($value)){
            $qb->andWhere(
                $qb->expr()->$expr($path, ":value")
            )
            ->setParameter(':value', $value);
        }
        else{
            $qb->andWhere(
                $qb->expr()->$expr($path)
            );
        }
    }

    /**
     * Guess which expression to use from sent value ex : /api/users?name=likemarie
     * @param $value
     * @return array
     */
    public function guessExpr($value){
        $operators = [
            'isNotNull',
            'isNull',
            'like',
            'lte',
            'gte',
            'neq',
            'gt',
            'lt',
            'eq'
        ];

        $expr = 'eq';
        foreach ($operators as $operator) {
            $_operator = substr($value, 0, strlen($operator));
            if ($_operator === $operator) {
                $expr = $operator;

                $_value = substr($value, strlen($operator), strlen($value));
                $value = ($operator === 'like') ?  strtolower($_value)."%" :  strtolower($_value);
                ;
                break;
            }
        }
        return [$expr, $value];
    }
}
