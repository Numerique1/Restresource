<?php
namespace Numerique1\Components\Restresources\Normalizer;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\PersistentCollection;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactoryInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\PropertyInfo\PropertyTypeExtractorInterface;
use Symfony\Component\Serializer\Exception\LogicException;
use Symfony\Component\Serializer\Mapping\ClassDiscriminatorResolverInterface;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;

class CustomObjectNormalizer extends ObjectNormalizer
{
    private $em;

    public function __construct(EntityManagerInterface $em, ClassMetadataFactoryInterface $classMetadataFactory = null, NameConverterInterface $nameConverter = null, PropertyAccessorInterface $propertyAccessor = null, PropertyTypeExtractorInterface $propertyTypeExtractor = null, ClassDiscriminatorResolverInterface $classDiscriminatorResolver = null, callable $objectClassResolver = null, array $defaultContext = [])
    {
        if (!\class_exists(PropertyAccess::class)) {
            throw new LogicException('The ObjectNormalizer class requires the "PropertyAccess" component. Install "symfony/property-access" to use it.');
        }

        parent::__construct($classMetadataFactory, $nameConverter, $propertyAccessor, $propertyTypeExtractor, $classDiscriminatorResolver, $objectClassResolver, $defaultContext);

        $this->em = $em;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = array())
    {
        $normalizedData = parent::normalize($object, $format, $context);
        $metadata = $this->em->getClassMetadata(get_class($object));

        foreach ($metadata->associationMappings as $field => $mapping) {
            if (isset($normalizedData[$field])) {
                if ($mapping['isCascadeDetach'] || $mapping['type'] == ClassMetadata::MANY_TO_MANY) {
                    $normalizedData["_$field"] = $normalizedData["$field"];
                    $data = $normalizedData["$field"];
                    $normalizedData["$field"] = array();
                    foreach ($data as $child) {
                        $normalizedData["$field"][] = $child['id'];
                    }
                } elseif (in_array($mapping['type'], array(ClassMetadata::TO_ONE, ClassMetadata::MANY_TO_ONE))) {
                    $normalizedData["_$field"] = $normalizedData["$field"];
                    $normalizedData["$field"] = $normalizedData["$field"]['id'];
                }
            }
        }

        return $normalizedData;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return \is_object($data) && !$data instanceof \Traversable && !$data instanceof \DateTime;
    }
}