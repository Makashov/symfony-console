<?php


namespace App\NamingStrategies;


class CamelCaseNamingStrategy implements  \Doctrine\ORM\Mapping\NamingStrategy
{
    private $prefix = 'tbl';

    /**
     * Returns a table name for an entity class.
     *
     * @param string $className The fully-qualified class name.
     *
     * @return string A table name.
     */
    function classToTableName($className)
    {
        if (strpos($className, '\\') !== false) {
            $className = substr($className, strrpos($className, '\\') + 1);
        }

        return $this->prefix . $this->camelCase($className);
    }

    /**
     * Returns a column name for a property.
     *
     * @param string $propertyName A property name.
     * @param string|null $className The fully-qualified class name.
     *
     * @return string A column name.
     */
    function propertyToColumnName($propertyName, $className = null)
    {
        return $this->camelCase($propertyName, false);
    }

    /**
     * Returns a column name for an embedded property.
     *
     * @param string $propertyName
     * @param string $embeddedColumnName
     * @param string $className
     * @param string $embeddedClassName
     *
     * @return string
     */
    function embeddedFieldToColumnName($propertyName, $embeddedColumnName, $className = null, $embeddedClassName = null)
    {
        return $this->camelCase($propertyName . ' ' . $embeddedColumnName);
    }

    /**
     * Returns the default reference column name.
     *
     * @return string A column name.
     */
    function referenceColumnName()
    {
        return 'id';
    }

    /**
     * Returns a join column name for a property.
     *
     * @param string $propertyName A property name.
     *
     * @return string A join column name.
     */
    function joinColumnName($propertyName)
    {
        return $this->camelCase($propertyName) . $this->referenceColumnName();
    }

    /**
     * Returns a join table name.
     *
     * @param string $sourceEntity The source entity.
     * @param string $targetEntity The target entity.
     * @param string|null $propertyName A property name.
     *
     * @return string A join table name.
     */
    function joinTableName($sourceEntity, $targetEntity, $propertyName = null)
    {
        return $this->classToTableName($sourceEntity) . $this->classToTableName($targetEntity);
    }

    /**
     * Returns the foreign key column name for the given parameters.
     *
     * @param string $entityName An entity.
     * @param string|null $referencedColumnName A property.
     *
     * @return string A join column name.
     */
    function joinKeyColumnName($entityName, $referencedColumnName = null)
    {
        return $this->classToTableName($entityName) . '_' .
            ($referencedColumnName ?: $this->referenceColumnName());
    }

    /**
     * @param string $string
     * @param bool $upperCase
     *
     * @return string
     */
    private function camelCase(string $string, bool $upperCase = true)
    {
        $str = str_replace(' ', '', ucwords(str_replace('-', ' ', $string)));

        if (!$upperCase) {
            $str = lcfirst($str);
        }

        return $str;
    }
}
