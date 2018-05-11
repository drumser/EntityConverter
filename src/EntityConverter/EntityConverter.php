<?php
/**
 * Created by PhpStorm.
 * User: Alexander <kladoas@ite-ng.ru>
 * Date: 09.11.2017
 * Time: 15:13
 */

namespace Quantick\EntityConverter;


use Quantick\EntityConverter\Annotation\ExcludeFieldConvert;
use Doctrine\Common\Annotations\Reader;
use ReflectionProperty;

/**
 * Class EntityConverter
 *
 * @package AppBundle\Service\EntityConverter
 */
class EntityConverter
{
//region SECTION: Fields
    /**
     * @var string
     * Название аннотации для исключения поля
     */
    private $excludeFieldAnnotation = ExcludeFieldConvert::class;
    /**
     * @var array
     * Поля для исключения
     */
    private $excludeFields = [];
    /**
     * @var Reader
     */
    private $annotationReader;
//endregion Fields

//region SECTION: Constructor
    /**
     * EntityConverter constructor.
     *
     * @param Reader $annotationReader
     */
    public function __construct(Reader $annotationReader)
    {
        $this->annotationReader = $annotationReader;
    }
//endregion Constructor

//region SECTION: Public
    /**
     * @param       $source
     * @param       $target
     *
     * @param array $mapArray
     *
     * @return mixed
     */
    public function convert($source, $target, array $mapArray = [])
    {
        $sourceRef = new \ReflectionObject($source);
        $targetRef = new \ReflectionObject($target);

        foreach ($sourceRef->getProperties() as $property) {
            $propertyName = !empty($mapArray) && isset($mapArray[$property->getName()])
                ? $mapArray[$property->getName()]
                : $property->getName();

            if (
                $this->isAnnotationallyExcluded($property)
                || !$targetRef->hasProperty($propertyName)
                || \in_array($property->getName(), $this->excludeFields, true)
                || $this->isAnnotationallyExcluded($targetRef->getProperty($propertyName))
            ) {
                continue;
            }

            $newProperty = $targetRef->getProperty($propertyName);
            $newProperty->setAccessible(true);
            $property->setAccessible(true);
            $newProperty->setValue($target, $property->getValue($source));
        }

        return $target;
    }
//endregion Public

//region SECTION: Private
    /**
     * @param ReflectionProperty $property
     *
     * @return bool
     */
    private function isAnnotationallyExcluded(ReflectionProperty $property)
    {
        $propertyAnnotation = $this->annotationReader->getPropertyAnnotation($property, $this->excludeFieldAnnotation);

        return $propertyAnnotation ? true : false;
    }
//endregion Private

//region SECTION: Getters/Setters
    /**
     * @return array
     */
    public function getExcludeFields()
    {
        return $this->excludeFields;
    }

    /**
     * @param array $excludeFields
     */
    public function setExcludeFields(array $excludeFields)
    {
        $this->excludeFields = $excludeFields;
    }
//endregion Getters/Setters
}