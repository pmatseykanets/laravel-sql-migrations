<?php

/**
 * Rule set to use.
 *
 * @see https://github.com/FriendsOfPHP/PHP-CS-Fixer
 * @see https://mlocati.github.io/php-cs-fixer-configurator/
 */
$rules = [
    // Use PSR2 preset
    '@PSR2' => true,
    // Each line of multi-line DocComments must have an asterisk [PSR-5] and must be aligned with the first one.
    'align_multiline_comment' => [
        'comment_type' => 'phpdocs_like',
    ],
    // PHP arrays should be declared using the configured syntax.
    'array_syntax' => [
        'syntax' => 'short',
    ],
    // Binary operators should be surrounded by space as configured.
    'binary_operator_spaces' => [
        'default' => 'single_space',
    ],
    // Ensure there is no code on the same line as the PHP open tag and it is followed by a blank line.
    'blank_line_after_opening_tag' => true,
    // An empty line feed must precede any configured statement.
    'blank_line_before_statement' => [
        'statements' => [
            // 'break',
            // 'continue',
            // 'declare',
            'return',
            'throw',
            'try',
        ],
    ],
    // Equal sign in declare statement should be surrounded by spaces or not following configuration.
    'declare_equal_normalize' => [
        'space' => 'single',
    ],
    // Single line comments should use double slashes `//` and not hash `#`.
    'hash_to_slash_comment' => true,
    // Convert `heredoc` to `nowdoc` where possible.
    'heredoc_to_nowdoc' => true,
    // There should not be empty PHPDoc blocks.
    'no_empty_phpdoc' => true,
    // There should not be blank lines between docblock and the documented element.
    'no_blank_lines_after_phpdoc' => true,
    // Multi-line whitespace before closing semicolon are prohibited.
    'no_multiline_whitespace_before_semicolons' => true,
    // Short cast `bool` using double exclamation mark should not be used.
    'no_short_bool_cast' => true,
    // Replace short-echo `<?=` with long format `<?php echo` syntax.
    'no_short_echo_tag' => true,
    // PHP single-line arrays should not have trailing comma.
    'no_trailing_comma_in_singleline_array' => true,
    'no_unused_imports' => true,
    // Logical NOT operators (`!`) should have one trailing whitespace.
    'not_operator_with_successor_space' => true,
    // Phpdoc should contain @param for all params.
    'phpdoc_add_missing_param_annotation' => true,
    // Docblocks should have the same indentation as the documented subject.
    'phpdoc_indent' => true,
    // Annotations in phpdocs should be ordered so that param annotations come first, then throws annotations, then return annotations.
    'phpdoc_order' => true,
    // Phpdocs summary should end in either a full stop, exclamation mark, or question mark.
    'phpdoc_summary' => true,
    // Phpdocs should start and end with content, excluding the very first and last line of the docblocks.
    'phpdoc_trim' => true,
    // @var and @type annotations should not contain the variable name.
    'phpdoc_var_without_name' => true,
    // PHP multi-line arrays should have a trailing comma.
    'trailing_comma_in_multiline_array' => true,
    // Replace all `<>` with `!=`.
    'standardize_not_equals' => true,
    // Unary operators should be placed adjacent to their operands.
    'unary_operator_spaces' => true,
];

$excludes = [
    'bootstrap',
    'devtools',
    'node_modules',
    'public',
    'resources',
    'storage',
    'vendor',
];

return PhpCsFixer\Config::create()
    ->setRules($rules)
    ->setFinder(
        PhpCsFixer\Finder::create()
            ->exclude($excludes)
            ->in(__DIR__)
    );
