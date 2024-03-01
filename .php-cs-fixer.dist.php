<?php

$finder = PhpCsFixer\Finder::create()
    ->exclude(['bootstrap', 'node_modules', 'public', 'storage', 'tests', 'vendor'])
    ->notPath('*')
    ->in(__DIR__);

$config = new PhpCsFixer\Config();
return $config->setRules([
    '@PSR2' => true,
    '@PSR12' => true,
    'array_syntax' => ['syntax' => 'short'],
    'blank_line_after_namespace' => true,
    'linebreak_after_opening_tag' => true,
    'no_unused_imports' => true,
    'no_useless_else' => true,
    'no_useless_return' => true,
    'single_blank_line_at_eof' => true,
    'single_blank_line_before_namespace' => true,
    'simplified_null_return' => true,
    'encoding' => true,
    'no_trailing_comma_in_singleline' => true,
    'class_reference_name_casing' => true,
    'constant_case' => true,
    'lowercase_keywords' => true,
    'multiline_whitespace_before_semicolons' => true,
    'single_quote' => true,
    'explicit_string_variable' => true,
    'array_indentation' => true,
    'trim_array_spaces' => true,
    'method_chaining_indentation' => true,
    'no_extra_blank_lines' => true,
    'no_spaces_around_offset' => true,
    'types_spaces' => true,
    'concat_space' => true,
    //    'concat_space' => ['spacing' => 'one'],
    //    'binary_operator_spaces'                  => [
    //        'operators' => [
    //            '=>' => 'align_single_space_minimal',
    //            '='  => 'align_single_space_minimal'
    //        ],
    //    ],
    'ordered_imports' => true,
    'trailing_comma_in_multiline' => true,
    'control_structure_braces' => true,
    'control_structure_continuation_position' => true,
    'empty_loop_body' => true,
    'no_unneeded_curly_braces' => true,
    'simplified_if_return' => true,
    'lambda_not_used_import' => true,
    'single_line_throw' => true,
    // 'use_arrow_functions'                      => true,
    'fully_qualified_strict_types' => true,
    'no_unneeded_import_alias' => true,
    'combine_consecutive_issets' => true,
    'combine_consecutive_unsets' => true,
    'declare_equal_normalize' => true,
    'declare_parentheses' => true,
    // 'dir_constant'                             => true,
    'explicit_indirect_variable' => true,
    'single_space_around_construct' => true,
    'clean_namespace' => true,
    'no_leading_namespace_whitespace' => true,
    'assign_null_coalescing_to_coalesce_equal' => true,
    'binary_operator_spaces' => true,
    'no_useless_concat_operator' => true,
    'object_operator_without_whitespace' => true,
    'operator_linebreak' => true,
    'standardize_not_equals' => true,
    'ternary_to_null_coalescing' => true,
    'align_multiline_comment' => true,
    'single_line_comment_spacing' => true,
    'single_line_comment_style' => true,
    // 'strict_param' => true,

])
    ->setFinder($finder);
