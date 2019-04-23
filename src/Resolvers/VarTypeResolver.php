<?php


namespace Larapie\DataTransferObject\Resolvers;


use Larapie\DataTransferObject\Exceptions\TypeDoesNotExistException;
use phpDocumentor\Reflection\TypeResolver;
use phpDocumentor\Reflection\Types\Compound;
use ReflectionProperty;

class VarTypeResolver
{
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
     * VarTypeResolver constructor.
     * @param $reflection
     */
    public function __construct(ReflectionProperty $reflection)
    {
        $this->reflection = $reflection;
    }

    public function resolve(string $varDocComment) :array
    {
        if ($this->reflection->getDeclaringClass()->isAnonymous()) {
            $filename = $this->reflection->getDeclaringClass()->getFileName();

            $contextFactory = new \phpDocumentor\Reflection\Types\ContextFactory();
            $context = $contextFactory->createForNamespace($this->getNamespaceFromFilename($filename), file_get_contents($filename));
        } else {
            $contextFactory = new \phpDocumentor\Reflection\Types\ContextFactory();
            $context = $contextFactory->createFromReflector($this->reflection);
        }

        $resolver = new TypeResolver();
        $resolvedTypes = $resolver->resolve($varDocComment, $context);
        $types = [];
        if ($resolvedTypes instanceof Compound) {
            foreach ($resolvedTypes as $type) {
                $types[] = $type->__toString();
            }
        } else {
            $types = [$resolvedTypes->__toString()];
        }
        $this->checkTypeExistence($types);
        return $types;
    }

    protected function getClassFromFilename($fileName) :string
    {
        $directoriesAndFilename = explode('/', $fileName);
        $fileName = array_pop($directoriesAndFilename);
        $nameAndExtension = explode('.', $fileName);
        return array_shift($nameAndExtension);
    }

    protected function getNamespaceFromFilename($filename) :string
    {
        $lines = file($filename);
        $namespace = preg_grep('/^namespace /', $lines);
        $namespaceLine = array_shift($namespace);
        $match = array();
        preg_match('/^namespace (.*);$/', $namespaceLine, $match);
        return $fullNamespace = array_pop($match);
    }

    protected function getFqnFromFileName($fileName) :string
    {
        return $this->getNamespaceFromFilename($fileName) . '\\' . $this->getClassFromFilename($fileName);
    }

    protected function checkTypeExistence(array $types)
    {
        foreach ($types as $type) {
            $type = str_replace("[]", "", $type);
            if (!in_array($type, self::$typeKeywords)) {
                if (!$this->classExists($type))
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