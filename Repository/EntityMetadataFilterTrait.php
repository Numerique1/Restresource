<?php
namespace Numerique1\Components\Restresources\Repository;

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
    public function filterByMetadataFieldNames(array $filters, Doctrine\ORM\QueryBuilder $qb = null)
    {
        if(!$qb){
            $qb = $this->createQueryBuilder('entity');
        }
        foreach($filters as $name => $value){
            $value = strtolower($value);
            $fields = $this->getClassMetadata()->getFieldNames();
            if(in_array($name, $fields)){
                $qb->andWhere(
                    $qb->expr()->like("LOWER({$qb->getRootAliases()[0]}.{$name})", ":value")
                )
                    ->setParameter(':value', "$value%");;
                ;
            }
        }
        return $qb;
    }
}