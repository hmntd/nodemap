<?php

namespace App\GraphQL\Scalars;

use GraphQL\Language\AST\BooleanValueNode;
use GraphQL\Language\AST\FloatValueNode;
use GraphQL\Language\AST\IntValueNode;
use GraphQL\Language\AST\ListValueNode;
use GraphQL\Language\AST\Node as ASTNode;
use GraphQL\Language\AST\NullValueNode;
use GraphQL\Language\AST\ObjectValueNode;
use GraphQL\Language\AST\StringValueNode;
use GraphQL\Type\Definition\ScalarType;

class JSON extends ScalarType
{
    public string $name = 'JSON';

    public ?string $description = 'The JSON scalar type represents arbitrary JSON data.';

    public function serialize($value): mixed
    {
        if (is_string($value)) {
            $decoded = json_decode($value, true);

            return json_last_error() === JSON_ERROR_NONE ? $decoded : $value;
        }

        return $value;
    }

    public function parseValue($value): mixed
    {
        if (is_string($value)) {
            $decoded = json_decode($value, true);

            return json_last_error() === JSON_ERROR_NONE ? $decoded : $value;
        }

        return $value;
    }

    public function parseLiteral(ASTNode $valueNode, ?array $variables = null): mixed
    {
        if ($valueNode instanceof StringValueNode) {
            $decoded = json_decode($valueNode->value, true);

            return json_last_error() === JSON_ERROR_NONE ? $decoded : $valueNode->value;
        }

        if ($valueNode instanceof IntValueNode || $valueNode instanceof FloatValueNode) {
            return $valueNode->value;
        }

        if ($valueNode instanceof BooleanValueNode) {
            return $valueNode->value;
        }

        if ($valueNode instanceof NullValueNode) {
            return null;
        }

        if ($valueNode instanceof ObjectValueNode) {
            $object = [];
            foreach ($valueNode->fields as $field) {
                $object[$field->name->value] = $this->parseLiteral($field->value, $variables);
            }

            return $object;
        }

        if ($valueNode instanceof ListValueNode) {
            $list = [];
            foreach ($valueNode->values as $item) {
                $list[] = $this->parseLiteral($item, $variables);
            }

            return $list;
        }

        if (property_exists($valueNode, 'value')) {
            return $valueNode->value;
        }

        return null;
    }
}
