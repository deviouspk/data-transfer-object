<?php

namespace Larapie\DataTransferObject\Resolvers;

use phpDocumentor\Reflection\Types\Compound;
use ReflectionProperty;
use Larapie\DataTransferObject\PropertyType;

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
    public function resolve(): PropertyType
    {
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


        $types = $this->resolveTypesFromVarDoc($varDocComment);

        $type->setTypes($types);
        $type->setArrayTypes(str_replace('[]', '', $types));
        $type->setHasType(true);
        $type->setNullable(strpos($varDocComment, 'null') !== false);

        return $type;
    }

    protected function resolveTypesFromVarDoc(string $varDocComment)
    {
        if ($this->reflection->getDeclaringClass()->isAnonymous()) {
            $filename = $this->reflection->getDeclaringClass()->getFileName();

            $contextFactory = new \phpDocumentor\Reflection\Types\ContextFactory();
            $context = $contextFactory->createForNamespace($this->getNamespaceFromFilename($filename), file_get_contents($filename));
        } else {
            $contextFactory = new \phpDocumentor\Reflection\Types\ContextFactory();
            $context = $contextFactory->createFromReflector($this->reflection);
        }

        $resolver = new \phpDocumentor\Reflection\TypeResolver();
        $resolvedTypes = $resolver->resolve($varDocComment, $context);
        $types = [];
        if ($resolvedTypes instanceof Compound) {
            foreach ($resolvedTypes as $type) {
                $types[] = $type->__toString();
            }
        } else {
            $types = [$resolvedTypes->__toString()];
        }
        return $types;
    }

    protected function getClassFromFilename($fileName)
    {
        $directoriesAndFilename = explode('/', $fileName);
        $fileName = array_pop($directoriesAndFilename);
        $nameAndExtension = explode('.', $fileName);
        $className = array_shift($nameAndExtension);

        return $className;
    }

    protected function getNamespaceFromFilename($filename)
    {
        $lines = file($filename);
        $namespace = preg_grep('/^namespace /', $lines);
        $namespaceLine = array_shift($namespace);
        $match = array();
        preg_match('/^namespace (.*);$/', $namespaceLine, $match);
        $fullNamespace = array_pop($match);

        return $fullNamespace;
    }

    protected function getFqnFromFileName($fileName)
    {
        return $this->getNamespaceFromFilename($fileName) . '\\' . $this->getClassFromFilename($fileName);
    }


}
