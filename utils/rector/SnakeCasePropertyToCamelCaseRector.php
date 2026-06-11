<?php

declare(strict_types=1);

namespace OpenErpByJsonRpc\Rector;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\VarLikeIdentifier;
use Rector\PhpDocParser\NodeTraverser\SimpleCallableNodeTraverser;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * Convertit les identifiants snake_case en camelCase : propriétés (classiques
 * et promues) et leurs accès via $this->, paramètres de méthode/fonction,
 * variables locales et tous leurs usages.
 */
final class SnakeCasePropertyToCamelCaseRector extends AbstractRector
{
    public function __construct(
        private readonly SimpleCallableNodeTraverser $simpleCallableNodeTraverser,
    ) {
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Renomme les identifiants snake_case (propriétés, paramètres, variables) en camelCase',
            [
                new CodeSample(
                    <<<'CODE'
class SomeClass
{
    private string $base_uri;

    public function getUri(string $default_uri): string
    {
        return $this->base_uri ?? $default_uri;
    }
}
CODE,
                    <<<'CODE'
class SomeClass
{
    private string $baseUri;

    public function getUri(string $defaultUri): string
    {
        return $this->baseUri ?? $defaultUri;
    }
}
CODE
                ),
            ]
        );
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [
            Property::class,
            PropertyFetch::class,
            ClassMethod::class,
            Function_::class,
            Closure::class,
            ArrowFunction::class,
        ];
    }

    public function refactor(Node $node): ?Node
    {
        if ($node instanceof Property) {
            return $this->refactorProperty($node);
        }

        if ($node instanceof PropertyFetch) {
            return $this->refactorPropertyFetch($node);
        }

        return $this->refactorFunctionLike($node);
    }

    private function refactorProperty(Property $property): ?Property
    {
        $changed = false;

        foreach ($property->props as $propertyProperty) {
            $oldName = $propertyProperty->name->toString();
            $newName = $this->toCamelCase($oldName);

            if ($newName !== $oldName) {
                $propertyProperty->name = new VarLikeIdentifier($newName);
                $changed = true;
            }
        }

        return $changed ? $property : null;
    }

    private function refactorPropertyFetch(PropertyFetch $propertyFetch): ?PropertyFetch
    {
        if (!$propertyFetch->name instanceof Identifier) {
            return null;
        }

        $oldName = $propertyFetch->name->toString();
        $newName = $this->toCamelCase($oldName);

        if ($newName === $oldName) {
            return null;
        }

        $propertyFetch->name = new Identifier($newName);

        return $propertyFetch;
    }

    /**
     * Renomme les variables (paramètres et variables locales) au sein d'un
     * scope. On opère sur le scope plutôt que sur chaque Variable directement,
     * sinon Rector échoue au « scope refresh ».
     */
    private function refactorFunctionLike(FunctionLike $functionLike): ?FunctionLike
    {
        $changed = false;

        $this->simpleCallableNodeTraverser->traverseNodesWithCallable(
            $functionLike,
            function (Node $node) use (&$changed): ?Node {
                if (!$node instanceof Variable || !\is_string($node->name)) {
                    return null;
                }

                // $this et les superglobales ($_GET, $_SERVER, ...) restent intacts.
                if ($node->name === 'this' || str_starts_with($node->name, '_')) {
                    return null;
                }

                $newName = $this->toCamelCase($node->name);
                if ($newName === $node->name) {
                    return null;
                }

                $node->name = $newName;
                $changed = true;

                return $node;
            }
        );

        return $changed ? $functionLike : null;
    }

    private function toCamelCase(string $name): string
    {
        if (!str_contains($name, '_')) {
            return $name;
        }

        return preg_replace_callback(
            '/_([a-z0-9])/',
            static fn (array $matches): string => strtoupper($matches[1]),
            $name
        );
    }
}
