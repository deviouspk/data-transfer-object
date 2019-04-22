<?php

namespace Larapie\DataTransferObject\Resolvers;

use Larapie\DataTransferObject\Exceptions\TypeDoesNotExistException;
use phpDocumentor\Reflection\Types\Array_;
use phpDocumentor\Reflection\Types\Boolean;
use phpDocumentor\Reflection\Types\Callable_;
use phpDocumentor\Reflection\Types\Compound;
use phpDocumentor\Reflection\Types\Float_;
use phpDocumentor\Reflection\Types\Integer;
use phpDocumentor\Reflection\Types\Iterable_;
use phpDocumentor\Reflection\Types\Mixed_;
use phpDocumentor\Reflection\Types\Null_;
use phpDocumentor\Reflection\Types\Object_;
use phpDocumentor\Reflection\Types\Parent_;
use phpDocumentor\Reflection\Types\Resource_;
use phpDocumentor\Reflection\Types\Scalar;
use phpDocumentor\Reflection\Types\Self_;
use phpDocumentor\Reflection\Types\Static_;
use phpDocumentor\Reflection\Types\String_;
use phpDocumentor\Reflection\Types\Void_;
use ReflectionProperty;
use Larapie\DataTransferObject\PropertyType;

class TypeResolver
{
    /**
     * @var ReflectionProperty
     */
    protected $reflection;

    /** @var string[] List of recognized keywords and unto which Value Object they map */
    private static $typeKeywords = array(
        'string',
        'int',
        'integer',
        'bool',
        'boolean',
        'float',
        'double',
        'object',
        'mixed',
        'array',
        'resource',
        'void',
        'null',
        'scalar',
        'callback',
        'callable',
        'false',
        'true',
        'self',
        '$this',
        'static',
        'parent',
        'iterable'
    );

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
        $this->checkTypeExistance($types);
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

    protected function checkTypeExistance(array $types)
    {
        foreach ($types as $type) {
            $type = str_replace("[]","",$type);
            if (!in_array($type, self::$typeKeywords)){
                if(!$this->classExists($type))
                    throw new TypeDoesNotExistException(sprintf(
                        'The @var annotation on %s::%s contains a non existent class "%s". '
                        . 'Did you maybe forget to add a "use" statement for this annotation?',
                        $this->reflection->getDeclaringClass()->getName(),
                        $this->reflection->getName(),
                        $type
                    ));
            }

        }
    }

    /**
     * @param string $class
     * @return bool
     */
    private function classExists($class)
    {
        return class_exists($class) || interface_exists($class);
    }

}
