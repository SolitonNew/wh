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
    const BLOCK_IF = 'IF';
    const BLOCK_SWITCH = 'SWITCH';
    const BLOCK_CASE = 'CASE';
    const BLOCK_BREAK = 'BREAK';
    const BLOCK_DEFAULT = 'DEFAULT';
    const BLOCK_BRACKETS = 'BRACKETS';
    const BLOCK_SUB = 'BLOCK SUB';
    const BLOCK_STRING = 'STRING';
    const BLOCK_VAR = 'VAR';
    const BLOCK_NUMBER = 'NUMBER';
    const BLOCK_FUNC = 'FUNC';
    const BLOCK_SYM = 'SYM';

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
     * @var array
     */
    protected array $parsedTree = [];

    protected array $parsedFunctions = [];
    protected array $parsedVariables = [];
    protected array $parsedNumbers = [];
    protected array $parsedStrings = [];

    /**
     * @param string $source
     */
    public function __construct(string $source)
    {
        $this->source = $source;
        $this->split();
        $this->prepareParts();
        $this->parseParts();
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

    /**
     * @return void
     */
    private function prepareParts(): void
    {
        $result = [];
        for ($i = 0; $i < count($this->parts); $i++) {
            $p = $this->parts[$i];
            switch ($p) {
                case '/*':
                    $i++;
                    for (; $i < count($this->parts); $i++) {
                        if ($this->parts[$i] == '*/') {
                            break;
                        }
                    }
                    break;
                case '//':
                    $i++;
                    for (; $i < count($this->parts); $i++) {
                        if ($this->parts[$i] == "\n") {
                            break;
                        }
                    }
                    break;
                case "'":
                    $s = [$p];
                    $i++;
                    for (; $i < count($this->parts); $i++) {
                        $s[] = $this->parts[$i];
                        if ($this->parts[$i] == "'") {
                            break;
                        }
                    }
                    $result[] = implode('', $s);
                    break;
                case ' ':
                case "\n":
                    break;
                default:
                    $result[] = $p;
            }
        }

        $this->parts = $result;
    }

    /**
     * @return void
     */
    private function parseParts(): void
    {
        $this->parsedTree = [];
        $this->parsedFunctions = [];
        $this->parsedVariables = [];
        $this->parsedStrings = [];
        $this->parsedNumbers = [];

        for ($index = 0; $index < count($this->parts); $index++) {
            if ($block = $this->parseBlock($index)) {
                $this->parsedTree[] = $block;
            }
        }

        //Log::info(print_r($this->partsTree, true));
    }

    /**
     * @param int $index
     * @param array $ignoreChars
     * @return false|object
     */
    private function parseBlock(int &$index, array $ignoreChars = []): object|bool
    {
        if ($index < count($this->parts)) {
            if (in_array($this->parts[$index], $ignoreChars)) {
                return false;
            }

            switch ($this->parts[$index]) {
                case 'if':
                    return $this->parseBlockIf($index);
                case 'switch':
                    return $this->parseBlockSwitch($index);
                case 'case':
                    return $this->parseBlockCase($index);
                case 'default':
                    return $this->parseBlockDefault($index);
                case 'break':
                    return $this->parseBlockBreake($index);
                case '(':
                    return $this->parseBlockBrackets($index);
                case '{':
                    return $this->parseBlockSub($index);
                default:
                    $p = $this->parts[$index];
                    if (isset($this->functions[$p])) { // Function
                        return $this->parseBlockFunc($index);
                    } else
                    if ($p != '' && $p[0] == "'") {  // String or Device Name
                        return $this->parseBlockString($index);
                    } else
                    if (preg_match('/^[A-Za-z]/', $p)) {  // Variable
                        return $this->parseBlockVariable($index);
                    } else
                    if (preg_match('/[0-9]/', $p)) {  // Number constant
                        return $this->parseBlockNumber($index);
                    } else {
                        return (object)[
                            'typ' => self::BLOCK_SYM,
                            'value' => $p,
                        ];
                    }
            }
        }

        return false;
    }

    /**
     * @param int $index
     * @return object
     */
    private function parseBlockIf(int &$index): object
    {
        $condition = false;
        $trueBlock = false;
        $falseBlock = false;

        $index++;
        if ($index < count($this->parts) && $this->parts[$index] == '(') {
            $condition = $this->parseBlockBrackets($index)->children;
        }

        $index++;
        if ($index < count($this->parts) && $this->parts[$index] == '{') {
            $trueBlock = $this->parseBlockSub($index)->children;
        }

        $index++;
        if ($index < count($this->parts) && $this->parts[$index] == 'else') {
            $index++;
            if ($index < count($this->parts) && $this->parts[$index] == '{') {
                $falseBlock = $this->parseBlockSub($index)->children;
            }
        } else {
            $index--;
        }

        return (object)[
            'typ' => self::BLOCK_IF,
            'condition' => $condition,
            'true' => $trueBlock,
            'false' => $falseBlock,
        ];
    }

    /**
     * @param int $index
     * @return object
     */
    private function parseBlockSwitch(int &$index): object
    {
        $index++;
        $condition = $this->parseBlockBrackets($index)->children;
        $children = [];

        $index++;
        if ($index < count($this->parts) && $this->parts[$index] == '{') {
            $children = $this->parseBlockSub($index)->children;
        }

        return (object)[
            'typ' => self::BLOCK_SWITCH,
            'condition' => $condition,
            'children' => $children,
        ];
    }

    /**
     * @param int $index
     * @return object
     */
    private function parseBlockCase(int &$index): object
    {
        $index++;
        $value = false;
        $children = [];
        $next = false;

        if ($block = $this->parseBlock($index)) {
            $value = $block;
            $index++;
            if ($index < count($this->parts) && $this->parts[$index] == ':') {
                $next = true;
            } else {
                $index--;
            }
        }

        if ($next) {
            $index++;
            for (; $index < count($this->parts); $index++) {
                $block = $this->parseBlock($index, ['case', 'default']);

                if ($block) {
                    if ($block->typ == self::BLOCK_SYM && $block->value == '}') {
                        $index--;
                        break;
                    } else
                    if ($block->typ == self::BLOCK_BREAK) {
                        $children[] = $block;
                        break;
                    } else {
                        $children[] = $block;
                    }
                } else
                if ($index < count($this->parts) && ($this->parts[$index] == 'case' || $this->parts[$index] == 'default')) {
                    $index--;
                    break;
                }
            }
        }

        return (object)[
            'typ' => self::BLOCK_CASE,
            'value' => $value,
            'children' => $children,
        ];
    }

    /**
     * @param int $index
     * @return object
     */
    private function parseBlockDefault(int &$index): object
    {
        $index++;
        $children = [];
        $next = false;

        if ($index < count($this->parts) && $this->parts[$index] == ':') {
            $next = true;
        } else {
            $index--;
        }

        if ($next) {
            $index++;
            for (; $index < count($this->parts); $index++) {
                $block = $this->parseBlock($index, ['case', 'default']);
                if ($block) {
                    if ($block->typ == self::BLOCK_SYM && $block->value == '}') {
                        $index--;
                        break;
                    } else
                    if ($block->typ == self::BLOCK_BREAK) {
                        $children[] = $block;
                        break;
                    } else {
                        $children[] = $block;
                    }
                } else
                if ($index < count($this->parts) && ($this->parts[$index] == 'case' || $this->parts[$index] == 'default')) {
                    $index--;
                    break;
                }
            }
        }

        return (object)[
            'typ' => self::BLOCK_DEFAULT,
            'children' => $children,
        ];
    }

    /**
     * @param $index
     * @return object
     */
    private function parseBlockBreake(&$index): object
    {
        $index++;
        if ($index < count($this->parts) && $this->parts[$index] != ';') {
            $index--;
        }

        return (object)[
            'typ' => self::BLOCK_BREAK,
        ];
    }

    /**
     * @param int $index
     * @return object
     */
    private function parseBlockString(int &$index): object
    {
        $value = $this->parts[$index];

        // For reports
        $this->parsedStrings[$value] = ($this->parsedStrings[$value] ?? 0) + 1;
        // -------------------

        return (object)[
            'typ' => self::BLOCK_STRING,
            'value' => substr($value, 1, strlen($value) - 2),
        ];
    }

    /**
     * @param int $index
     * @return object
     */
    private function parseBlockVariable(int &$index): object
    {
        $value = $this->parts[$index];

        // For reports
        $this->parsedVariables[$value] = ($this->parsedVariables[$value] ?? 0) + 1;
        // -------------------

        return (object)[
            'typ' => self::BLOCK_VAR,
            'value' => $value,
        ];
    }

    /**
     * @param int $index
     * @return object
     */
    private function parseBlockNumber(int &$index): object
    {
        $value = $this->parts[$index];

        // For reports
        $this->parsedNumbers[$value] = ($this->parsedNumbers[$value] ?? 0) + 1;
        // -------------------

        return (object)[
            'typ' => self::BLOCK_NUMBER,
            'value' => $value,
        ];
    }

    /**
     * @param int $index
     * @return object
     */
    private function parseBlockFunc(int &$index): object
    {
        $name = $this->parts[$index];

        $index++;

        $args = [];
        if ($index < count($this->parts) && $this->parts[$index] == '(') {
            $args = $this->parseBlockBrackets($index, [','])->children;
        }

        if (!in_array(count($args), $this->functions[$name]['args'])) {
            throw new \Exception('Invalid number of arguments "'.count($args).'" for "'.$name.'"');
        }

        // For reports
        $this->parsedFunctions[$name] = ($this->parsedFunctions[$name] ?? 0) + 1;
        // -------------------
        return (object)[
            'typ' => self::BLOCK_FUNC,
            'name' => $name,
            'args' => $args,
        ];
    }

    /**
     * @param int $index
     * @param array $ignoreChars
     * @return object
     */
    private function parseBlockBrackets(int &$index, array $ignoreChars = []): object
    {
        $index++;

        $children = [];
        for (; $index < count($this->parts); $index++) {
            if ($this->parts[$index] == ')') {
                break;
            }

            if ($block = $this->parseBlock($index, $ignoreChars)) {
                $children[] = $block;
            }
        }

        return (object)[
            'typ' => self::BLOCK_BRACKETS,
            'children' => $children,
        ];
    }

    /**
     * @param int $index
     * @return object
     */
    private function parseBlockSub(int &$index): object
    {
        $index++;

        $children = [];
        for (; $index < count($this->parts); $index++) {
            if ($this->parts[$index] == '}') {
                break;
            }

            if ($block = $this->parseBlock($index)) {
                $children[] = $block;
            }
        }

        return (object)[
            'typ' => self::BLOCK_SUB,
            'children' => $children,
        ];
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
        $data = (object)[
            'tree' =>  $this->parsedTree,
            'functions' => $this->parsedFunctions,
            'variables' => $this->parsedVariables,
            'strings' => $this->parsedStrings,
            'numbers' => $this->parsedNumbers,
        ];

        if (is_array($report)) {
            $report['functions'] = $this->parsedFunctions;
            $report['variables'] = $this->parsedVariables;
            $report['strings'] = $this->parsedStrings;
            $report['numbers'] = $this->parsedNumbers;
        }

        return $translator->translate($data);
    }
}
