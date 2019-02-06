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
     *
     * @param array $filters
     * @param QueryBuilder|null $qb
     *
     * @return QueryBuilder
     */
    public function filterByMetadataFieldNames(array $filters, QueryBuilder $qb = null)
    {
        if (!$qb)
        {
            $qb = $this->createQueryBuilder('entity');
        }
        foreach ($filters as $name => $value)
        {
            if (!is_string($value) || !is_integer($value))
            {
                continue;
            }
            $fields = $this->getClassMetadata()
                ->getFieldNames();
            if (in_array($name, $fields))
            {
                $this->createGuessExprFilter($qb, "LOWER({$qb->getRootAliases()[0]}.{$name})", $name, $value);
            }
        }

        return $qb;
    }

    /**
     * @param QueryBuilder $qbtokenStorage
     * @param string       $path
     * @param string       $name
     * @param string       $value
     */
    public function createGuessExprFilter(QueryBuilder &$qb, string $path, string $name, string $value)
    {
        list($expr, $value) = $this->guessExpr($value);
        if ($value !== '')
        {
            $qb->andWhere($qb->expr()
                ->$expr($path, ":$name"))
                ->setParameter(":$name", $value);
        }
        else
        {
            $qb->andWhere($qb->expr()
                ->$expr($path));
        }
    }

    /**
     * Guess which expression to use from sent value ex : /api/users?name=likemarie
     *
     * @param $value
     *
     * @return array
     */
    public function guessExpr($value)
    {
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
        foreach ($operators as $operator)
        {
            $_operator = substr($value, 0, strlen($operator));
            if ($_operator === $operator)
            {
                $expr = $operator;

                $_value = substr($value, strlen($operator), strlen($value));
                $value = ($operator === 'like')
                    ? strtolower($_value) . "%"
                    : strtolower($_value);;
                break;
            }
        }

        return [
            $expr,
            $value
        ];
    }
}
