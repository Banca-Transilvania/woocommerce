<?php

$finder = Symfony\Component\Finder\Finder::create()
    ->in([
        __DIR__ . '/src',
        __DIR__ . '/example',
    ])
    ->name('*.php')
    ->notPath('bootstrap/*')
    ->notPath('storage/*')
    ->notPath('vendor/*')
    ->ignoreDotFiles(true)
    ->ignoreVCS(true);

$config = new PhpCsFixer\Config();
return $config->setRules([
    '@PSR12' => true,
    'array_syntax' => ['syntax' => 'short'],
    'ordered_imports' => ['sort_algorithm' => 'alpha'],
    'no_unused_imports' => true,
    'not_operator_with_successor_space' => false,
    'trailing_comma_in_multiline' => ['elements' => ['arrays', 'arguments', 'parameters']],
    'phpdoc_scalar' => true,
    'unary_operator_spaces' => true,
    'binary_operator_spaces' => [
        'default' => 'single_space',
    ],
    'blank_line_before_statement' => [
        'statements' => ['break', 'continue', 'declare', 'return', 'throw', 'try'],
    ],
    'braces' => [
        'allow_single_line_closure' => false,
        'position_after_functions_and_oop_constructs' => 'next',
        'position_after_anonymous_constructs' => 'same',
        'position_after_control_structures' => 'same',
    ],
    'phpdoc_single_line_var_spacing' => true,
    'phpdoc_var_without_name' => true,
    'method_argument_space' => [
        'on_multiline' => 'ensure_fully_multiline',
        'keep_multiple_spaces_after_comma' => false,
    ],
    'single_trait_insert_per_statement' => true,
])
    ->setFinder($finder);
