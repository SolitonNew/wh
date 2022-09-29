<?php

namespace App\Library\Script;

use App\Library\Script\Translators\ITranslator;
use Illuminate\Support\Facades\Log;

/**
 * Description of Translate
 *
 * @author soliton
 */
class Translate
{
    /**
     * Dictionary of syntactic constructions.
     *
     * @var array
     */
    private array $keywords = [
        'if',
        'else',
        'break',
        'switch',
        'case',
        'default',
        'for',
    ];

    /**
     * Dictionary of syntactic constructions.
     *
     * @return array
     */
    public function getKeywords(): array
    {
        return $this->keywords;
    }

    /**
     * Dictionary of functions
     *
     * $key - Function name
     * helper - Description of the function for the script editor
     * args - Possible number of parameters
     *
     * @var array
     */
    private array $functions = [
        'get' => [
            'helper' => 'function (name)',
            'args' => [1],
        ],
        'set' => [
            'helper' => 'function (name, value, later = 0)',
            'args' => [2, 3],
        ],
        'on' => [
            'helper' => 'function (name, later = 0)',
            'args' => [1, 2],
        ],
        'off' => [
            'helper' => 'function (name, later = 0)',
            'args' => [1, 2],
        ],
        'toggle' => [
            'helper' => 'function (name, later = 0)',
            'args' => [1, 2],
        ],
        'speech' => [
            'helper' => 'function (phrase, args)',
            'args' => ['1+'],
        ],
        'play' => [
            'helper' => 'function (media, args)',
            'args' => ['1+'],
        ],
        'info' => [
            'helper' => 'function ()',
            'args' => [0],
        ],
        'print_i' => [
            'helper' => 'function (int)',
            'args' => [1],
        ],
        'print_f' => [
            'helper' => 'function (float)',
            'args' => [1],
        ],
        'print_s' => [
            'helper' => 'function (text)',
            'args' => [1],
        ],
        'abs_i' => [
            'helper' => 'function (int)',
            'args' => [1],
        ],
        'abs_f' => [
            'helper' => 'function (float)',
            'args' => [1],
        ],
        'round' => [
            'helper' => 'function (float)',
            'args' => [1],
        ],
        'ceil' => [
            'helper' => 'function (float)',
            'args' => [1],
        ],
        'floor' => [
            'helper' => 'function (float)',
            'args' => [1],
        ],
    ];

    /**
     * Dictionary of functions
     *
     * $key - Function name
     * helper - Description of the function for the script editor
     * args - Possible number of parameters
     *
     * @var array
     */
    public function getFunctions(): array
    {
        return $this->functions;
    }

    /**
     * @var string
     */
    protected string $source = '';

    /**
     * @var array
     */
    protected array $parts = [];

    /**
     * @param string $source
     */
    public function __construct(string $source)
    {
        $this->source = $source;
        $this->split();
        $this->prepare();
    }

    /**
     * Breaks the source code into pieces.
     *
     * @return void
     */
    protected function split(): void
    {
        // Separator for fragmenting source code.
        $delimeters = [
            ' ' => [],
            ';' => [],
            ',' => [],
            '"' => [],
            "'" => [],
            '+' => ['+', '='],
            '-' => ['-', '='],
            '*' => ['=', '/'],
            '/' => ['=', '/', '*'],
            '=' => ['='],
            '(' => [],
            ')' => [],
            '{' => [],
            '}' => [],
            '[' => [],
            ']' => [],
            ':' => [],
            ';' => [],
            '?' => [],
            '&' => ['&'],
            '|' => ['|'],
            '!' => [],
            '$' => [],
            chr(10) => [],
            chr(13) => [],
            chr(9) => [],  // tab
        ];

        $this->parts = [];

        $s = '';
        for ($i = 0; $i < strlen($this->source); $i++) {
            $c = $this->source[$i];
            if (isset($delimeters[$c])) {
                if ($s !== '') {
                    $this->parts[] = $s;
                }
                $s = '';
                if (count($delimeters[$c]) && ($i < strlen($this->source) - 1)) {
                    $cn = $this->source[$i + 1];
                    if (in_array($cn, $delimeters[$c])) {
                        $s = $c.$cn;
                        $i++;
                    }
                }
                if ($s === '') {
                    $this->parts[] = $c;
                } else {
                    $this->parts[] = $s;
                }
                $s = '';
            } else {
                $s .= $c;
            }
        }

        if ($s !== '') {
            $this->parts[] = $s;
        }
    }

    private array $prepared_functions = [];
    private array $prepared_variables = [];
    private array $prepared_numbers = [];
    private array $prepared_strings = [];

    /**
     * @param int $from_i
     * @param int $func_args
     * @return int
     * @throws \Exception
     */
    private function prepareBlock(int $from_i, int &$func_args): int
    {
        $spaces = ['', ' ', chr(9), chr(10), chr(13)];

        switch ($this->parts[$from_i]) {
            case '(':
                $to_char = ')';
                $from_i++;
                break;
            case '{':
                $to_char = '}';
                $from_i++;
                break;
            default:
                $to_char = chr(0);
        }

        $empty = 0;
        for ($i = $from_i; $i < count($this->parts); $i++) {
            $part = $this->parts[$i];
            if (in_array($part, $spaces)) continue;

            $empty++;

            if ($part == $to_char) { // End block
                if ($empty == 1) $func_args = 0;
                return $i;
            } else
            if ($part == '{') { // New block
                $args = 0;
                $i = $this->prepareBlock($i, $args);
            } else
            if ($part == ',') {
                $func_args++;
            } else
            if (preg_match('/[0-9]/', $part[0])) { // It is a number
                $this->prepared_numbers[$part] = (isset($this->prepared_numbers[$part]) ? $this->prepared_numbers[$part] + 1 : 1);
            } else
            if (preg_match('/[a-zA-Z]/', $part[0])) { // It is a function, phrase or variable
                if ($i < count($this->parts) - 1) {
                    $is_keyword = false;
                    for ($k = $i + 1; $k < count($this->parts); $k++) {
                        if (in_array($this->parts[$k], $spaces)) continue;
                        if ($this->parts[$k] == '(') { // It is a function or construction
                            $args = 1;
                            $new_i = $this->prepareBlock($k, $args);
                            if (isset($this->functions[$part])) { // It is a function
                                // Check the number of arguments
                                if (strpos($this->functions[$part]['args'][0], '+') !== false) {
                                    $minArgs = substr($this->functions[$part]['args'][0], 0, strlen($this->functions[$part]['args'][0]) - 1) ?: 0;
                                    if ($minArgs > $args) {
                                        throw new \Exception('Invalid number of arguments "'.$args.'" for "'.$part.'"');
                                    }
                                } else
                                if (!in_array($args, $this->functions[$part]['args'])) {
                                    throw new \Exception('Invalid number of arguments "'.$args.'" for "'.$part.'"');
                                }

                                // Replace the record string with an object
                                // with extended information.
                                $this->parts[$i] = (object)[
                                    'type' => 'function',
                                    'name' => $part,
                                    'args' => $args,
                                ];

                                if (isset($this->prepared_functions[$part])) {
                                    if (!in_array($args, $this->prepared_functions[$part])) {
                                        $this->prepared_functions[$part][] = $args;
                                    }
                                } else {
                                    $this->prepared_functions[$part][] = $args;
                                }
                            } else
                            if (!in_array($part, $this->keywords)) {
                                throw new \Exception('Unknown function "'.$part.'"');
                            }
                            $is_keyword = true;

                            $i = $new_i;
                        } else
                        if (in_array($part, $this->keywords)) {
                            $is_keyword = true;
                        }
                        break;
                    }
                    if (!$is_keyword) {
                        $this->prepared_variables[$part] = (isset($this->prepared_variables[$part]) ? $this->prepared_variables[$part] + 1 : 1);
                    }
                } else {
                    $this->prepared_variables[$part] = (isset($this->prepared_variables[$part]) ? $this->prepared_variables[$part] + 1 : 1);
                }
            }
        }

        return count($this->parts) - 1;
    }

    /**
     * @return void
     * @throws \Exception
     */
    private function prepare(): void
    {
        $this->prepared_functions = [];
        $this->prepared_variables = [];
        $this->prepared_numbers = [];
        $this->prepared_strings = [];

        if (count($this->parts) == 0) return ;

        // Deleting comments
        for ($i = 0; $i < count($this->parts); $i++) {
            $part = $this->parts[$i];
            if ($part == '/*') { // Start of a multi-line comment
                $this->parts[$i] = '';
                for ($k = $i + 1; $k < count($this->parts); $k++) {
                    if ($this->parts[$k] == '*/') {
                        $this->parts[$k] = '';
                        $i = $k + 1;
                        break;
                    }
                    $this->parts[$k] = '';
                }
            } else
            if ($part == '//') { // Comment in line
                for ($k = $i; $k < count($this->parts); $k++) {
                    if ($this->parts[$k] == chr(10) || $this->parts[$k] == chr(13)) {
                        $i = $k;
                        break;
                    } else {
                        $this->parts[$k] = '';
                    }
                }
            }
        }

        // Make strings
        for ($i = 0; $i < count($this->parts); $i++) {
            $part = $this->parts[$i];
            if ($part == '"' || $part == "'") {
                $from_char = $part;
                $string = [$part];
                for ($k = $i + 1; $k < count($this->parts); $k++) {
                    $string[] = $this->parts[$k];
                    if ($this->parts[$k] == $from_char) {
                        $this->parts[$k] = '';
                        $str = implode('', $string);
                        $this->prepared_strings[$str] = (isset($this->prepared_strings[$str]) ? $this->prepared_strings[$str] + 1 : 1);
                        $this->parts[$i] = $str;
                        $i = $k + 1;
                        break;
                    }
                    $this->parts[$k] = '';
                }
            }
        }

        $args = 0;
        $this->prepareBlock(0, $args);
    }

    /**
     * @param array $parts
     * @param array $strings
     * @return void
     */
    protected function prepareStrings(array &$parts, array &$strings): void
    {
        //
    }

    /**
     * Builds source code from parts using the specified translator.
     *
     * @param ITranslator $translator
     * @param array $report
     * @return string
     */
    public function run(ITranslator $translator, array &$report = null): string
    {
        $parts = [];
        foreach ($this->parts as $part) {
            if ($part !== '') {
                $parts[] = $part;
            }
        }

        $this->prepareStrings($parts, $this->prepared_strings);

        $prepareData = (object)[
            'parts' =>  $parts,
            'functions' => $this->prepared_functions,
            'variables' => $this->prepared_variables,
            'strings' => $this->prepared_strings,
            'numbers' => $this->prepared_numbers,
        ];

        if (is_array($report)) {
            $report['functions'] = $this->prepared_functions;
            $report['variables'] = $this->prepared_variables;
            $report['strings'] = $this->prepared_strings;
            $report['numbers'] = $this->prepared_numbers;
        }

        return $translator->translate($prepareData);
    }
}
