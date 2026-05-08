<?php

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

$finder = Finder::create()
    ->in([
        'app',
        'config',
        'database',
        'routes',
        'tests',
    ])
    ->exclude(['vendor', 'storage'])
    ->append([
        'bootstrap/app.php',
        'bootstrap/providers.php',
        'public/index.php',
    ])
    ->name('*.php')
    ->notName('*.blade.php');

return (new Config())
    ->setRiskyAllowed(true)
    ->setUsingCache(false)
    ->setIndent('    ')
    ->setLineEnding("\n")
    ->setRules([
        '@PER-CS2x0' => true,
        '@PHP8x3Migration' => true,
        'declare_strict_types' => true,
        'array_syntax' => ['syntax' => 'short'],
        'binary_operator_spaces' => [
            'default' => 'single_space',
            'operators' => [
                '=>' => 'single_space',
            ],
        ],
        'blank_line_before_statement' => [
            'statements' => [
                'break',
                'continue',
                'declare',
                'return',
                'throw',
                'try',
            ],
        ],
        'class_attributes_separation' => [
            'elements' => [
                'const' => 'one',
                'method' => 'one',
                'property' => 'one',
                'trait_import' => 'none',
            ],
        ],
        'concat_space' => ['spacing' => 'one'],
        'nullable_type_declaration_for_default_null_value' => true,
        'no_superfluous_phpdoc_tags' => true,
        'no_unused_imports' => true,
        'ordered_class_elements' => [
            'order' => [
                'use_trait',
                'case',
                'constant_public',
                'constant_protected',
                'constant_private',
                'property_public_readonly',
                'property_protected_readonly',
                'property_private_readonly',
                'property_public_static',
                'property_protected_static',
                'property_private_static',
                'property_public',
                'property_protected',
                'property_private',
                'construct',
                'destruct',
                'magic',
                'phpunit',
                'method_public_static',
                'method_protected_static',
                'method_private_static',
                'method_public',
                'method_protected',
                'method_private',
            ],
            'sort_algorithm' => 'none',
        ],
        'ordered_imports' => [
            'imports_order' => ['class', 'function', 'const'],
            'sort_algorithm' => 'alpha',
        ],
        'method_chaining_indentation' => true,
        'phpdoc_align' => [
            'align' => 'vertical',
            'tags' => [
                'method',
                'param',
                'property',
                'property-read',
                'property-write',
                'return',
                'throws',
                'type',
                'var',
            ],
        ],
        'phpdoc_line_span' => [
            'const' => 'single',
            'property' => 'single',
            'method' => 'multi',
        ],
        'single_line_empty_body' => true,
        'single_trait_insert_per_statement' => true,
        'trailing_comma_in_multiline' => [
            'after_heredoc' => true,
            'elements' => ['arrays', 'arguments', 'parameters', 'match'],
        ],
        'types_spaces' => ['space' => 'single'],
        'global_namespace_import' => [
            'import_classes' => true,
            'import_constants' => true,
            'import_functions' => true,
        ],
    ])
    ->setFinder($finder);
