<?php

declare(strict_types=1);

use GraphQL\Error\DebugFlag;
use GraphQL\Validator\Rules\DisableIntrospection;
use GraphQL\Validator\Rules\QueryComplexity;
use GraphQL\Validator\Rules\QueryDepth;
use Nuwave\Lighthouse\Execution\AuthenticationErrorHandler;
use Nuwave\Lighthouse\Execution\AuthorizationErrorHandler;
use Nuwave\Lighthouse\Execution\ReportingErrorHandler;
use Nuwave\Lighthouse\Execution\ValidationErrorHandler;
use Nuwave\Lighthouse\Http\Middleware\AcceptJson;
use Nuwave\Lighthouse\Http\Middleware\AttemptAuthentication;
use Nuwave\Lighthouse\Schema\Directives\ConvertEmptyStringsToNullDirective;
use Nuwave\Lighthouse\Schema\Directives\DropArgsDirective;
use Nuwave\Lighthouse\Schema\Directives\RenameArgsDirective;
use Nuwave\Lighthouse\Schema\Directives\SanitizeDirective;
use Nuwave\Lighthouse\Schema\Directives\SpreadDirective;
use Nuwave\Lighthouse\Schema\Directives\TransformArgsDirective;
use Nuwave\Lighthouse\Schema\Directives\TrimDirective;
use Nuwave\Lighthouse\Validation\ValidateDirective;

return [
    'route' => [
        'uri' => '/graphql',
        'name' => 'graphql',
        'middleware' => [
            'web',
            AcceptJson::class,
            AttemptAuthentication::class,
        ],
    ],
    'guards' => null,
    'schema_path' => base_path('graphql/schema.graphql'),
    'schema_cache' => [
        'enable' => env('LIGHTHOUSE_SCHEMA_CACHE_ENABLE', false),
        'path' => env('LIGHTHOUSE_SCHEMA_CACHE_PATH', base_path('bootstrap/cache/lighthouse-schema.php')),
    ],
    'cache_directive_tags' => false,
    'query_cache' => [
        'enable' => false,
        'mode' => 'store',
        'opcache_path' => base_path('bootstrap/cache'),
        'store' => null,
        'ttl' => 24 * 60 * 60,
    ],
    'validation_cache' => [
        'enable' => false,
        'store' => null,
        'ttl' => 24 * 60 * 60,
    ],
    'parse_source_location' => true,
    'namespaces' => [
        'models' => ['App', 'App\\Models'],
        'queries' => 'App\\GraphQL\\Queries',
        'mutations' => 'App\\GraphQL\\Mutations',
        'subscriptions' => 'App\\GraphQL\\Subscriptions',
        'types' => 'App\\GraphQL\\Types',
        'interfaces' => 'App\\GraphQL\\Interfaces',
        'unions' => 'App\\GraphQL\\Unions',
        'scalars' => 'App\\GraphQL\\Scalars',
        'directives' => 'App\\GraphQL\\Directives',
        'validators' => 'App\\GraphQL\\Validators',
    ],
    'security' => [
        'max_query_complexity' => QueryComplexity::DISABLED,
        'max_query_depth' => QueryDepth::DISABLED,
        'disable_introspection' => (bool) env('LIGHTHOUSE_SECURITY_DISABLE_INTROSPECTION', false)
            ? DisableIntrospection::ENABLED
            : DisableIntrospection::DISABLED,
    ],
    'pagination' => [
        'default_count' => null,
        'max_count' => null,
    ],
    'debug' => env('LIGHTHOUSE_DEBUG', DebugFlag::INCLUDE_DEBUG_MESSAGE | DebugFlag::INCLUDE_TRACE),
    'error_handlers' => [
        AuthenticationErrorHandler::class,
        AuthorizationErrorHandler::class,
        ValidationErrorHandler::class,
        ReportingErrorHandler::class,
    ],
    'field_middleware' => [
        TrimDirective::class,
        ConvertEmptyStringsToNullDirective::class,
        SanitizeDirective::class,
        ValidateDirective::class,
        TransformArgsDirective::class,
        SpreadDirective::class,
        RenameArgsDirective::class,
        DropArgsDirective::class,
    ],
    'global_id_field' => 'id',
    'persisted_queries' => true,
    'transactional_mutations' => true,
    'force_fill' => true,
    'batchload_relations' => true,
    'shortcut_foreign_key_selection' => false,
];
