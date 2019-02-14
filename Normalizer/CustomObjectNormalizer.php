<?php
namespace Numerique1\Components\Restresources\Normalizer;

use Doctrine\Common\Persistence\Mapping\MappingException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class CustomObjectNormalizer implements NormalizerInterface
{
    private $em;

    public function __construct(EntityManagerInterface $em, ObjectNormalizer $objectNormalizer, DateTimeNormalizer $dateTimeNormalizer)
    {
        $this->em = $em;
        $objectNormalizer->setCircularReferenceHandler(function ($object) {
            return $object->getId();
        });
        $this->objectNormalizer = $objectNormalizer;
        $this->dateTimeNormalizer = $dateTimeNormalizer;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = array())
    {
        if ($object instanceof \DateTime) {
            return $this->dateTimeNormalizer->normalize($object, $format, $context);
        }

        $normalizedData = $this->objectNormalizer->normalize($object, $format, $context);

        if (!is_array($normalizedData))
        {
            return $normalizedData;
        }

        try
        {
            $metadata = $this->em->getClassMetadata(get_class($object));

            foreach ($metadata->associationMappings as $field => $mapping)
            {
                if (isset($normalizedData[$field]))
                {
                    if (in_array($mapping['type'], array(
                        ClassMetadata::ONE_TO_MANY,
                        ClassMetadata::MANY_TO_MANY
                    )))
                    {
                        $normalizedData["_$field"] = $normalizedData["$field"];
                        $data = $normalizedData["$field"];
                        $normalizedData["$field"] = array();
                        foreach ($data as $child)
                        {
                            $normalizedData["$field"][] = $child['id'];
                        }
                    }
                    elseif (in_array($mapping['type'], array(
                        ClassMetadata::ONE_TO_ONE,
                        ClassMetadata::MANY_TO_ONE
                    )))
                    {
                        $normalizedData["_$field"] = $normalizedData["$field"];
                        $normalizedData["$field"] = $normalizedData["$field"]['id'];
                    }
                }
            }

            return $normalizedData;
        }
        catch (MappingException $e)
        {
            return $normalizedData;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return \is_object($data) && !$data instanceof \Traversable;
    }
}
