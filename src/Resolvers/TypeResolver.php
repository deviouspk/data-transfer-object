<?php


namespace Larapie\DataTransferObject\Resolvers;


use Larapie\DataTransferObject\PropertyType;
use ReflectionProperty;

class TypeResolver
{

    /**
     * @var ReflectionProperty
     */
    protected $reflection;

    /**
     * TypeResolver constructor.
     * @param ReflectionProperty $reflection
     */
    public function __construct(ReflectionProperty $reflection)
    {
        $this->reflection = $reflection;
    }


    /**
     * @return PropertyType
     */
    public function resolve() :PropertyType{
        $type = new PropertyType();

        $docComment = $this->reflection->getDocComment();

        if (!$docComment) {
            $type->setNullable(true);

            return $type;
        }

        preg_match('/\@var ((?:(?:[\w|\\\\])+(?:\[\])?)+)/', $docComment, $matches);

        if (!count($matches)) {
            $type->setNullable(true);

            return $type;
        }

        $varDocComment = end($matches);

        $type->setTypes($types = explode('|', $varDocComment));
        $type->setArrayTypes(str_replace('[]', '', $types));
        $type->setHasType(true);
        $type->setNullable(strpos($varDocComment, 'null') !== false);

        return $type;
    }


}