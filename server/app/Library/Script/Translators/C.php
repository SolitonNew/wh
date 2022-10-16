<?php

namespace App\Library\Script\Translators;

use App\Library\Script\Translate;
use Illuminate\Support\Facades\Log;

/**
 * Description of C
 *
 * @author soliton
 */
class C extends TranslatorBase
{
    const TAB_STR = '    ';

    /**
     * @var array
     */
    private array $functions = [
        'get' => [
            1 => 'command_get',
        ],
        'set' => [
            2 => 'command_set',
            3 => 'command_set_later',
        ],
        'on' => [
            1 => 'command_on',
            2 => 'command_on_later',
        ],
        'off' => [
            1 => 'command_off',
            2 => 'command_off_later',
        ],
        'toggle' => [
            1 => 'command_toggle',
            2 => 'command_toggle_later',
        ],
        'speech' => [
            '1+' => 'command_speech',
        ],
        'play' => [
            '1+' => 'command_play',
        ],
        'info' => [
            0 => 'command_info',
        ],
        'print_i' => [
            1 => 'command_print_i',
        ],
        'print_f' => [
            1 => 'command_print_f',
        ],
        'print_s' => [
            1 => 'command_print_s',
        ],
        'abs_i' => [
            1 => 'command_abs_i',
        ],
        'abs_f' => [
            1 => 'command_abs_f',
        ],
        'round' => [
            1 => 'command_round',
        ],
        'ceil' => [
            1 => 'command_ceil',
        ],
        'floor' => [
            1 => 'command_floor',
        ],
    ];

    /**
     *
     * @param type $parts
     */
    /*public function translate(object $prepareData): string
    {
        $parts = $prepareData->parts;

        $variables = [];
        foreach ($prepareData->variables as $var => $v) {
            $variables[] = 'int '.$var.";\n";
        }

        $varIDs = [];
        foreach ($prepareData->strings as $str => $v) {
            $s = substr($str, 1, strlen($str) - 2);
            $i = array_search($s, $this->variableNames);
            if ($i !== false) {
                $varIDs[$str] = $i;
            }
        }

        for ($i = 0; $i < count($parts); $i++) {
            if (is_object($parts[$i])) {
                if (isset($this->functions[$parts[$i]->name])) {
                    if (isset($this->functions[$parts[$i]->name]['+'])) {
                        $args = $parts[$i]->args;
                        $parts[$i] = $this->functions[$parts[$i]->name]['+'];
                        for (; $i < count($parts); $i++) {
                            if ($parts[$i] == '(') {
                                $parts[$i] = '('.$args.', ';
                                break;
                            }
                        }
                    } else {
                        $parts[$i] = $this->functions[$parts[$i]->name][$parts[$i]->args];
                    }
                } else {
                    $parts[$i] = $parts[$i]->name;
                }
            } else
            if (isset($varIDs[$parts[$i]])) {
                $parts[$i] = $varIDs[$parts[$i]];
            }
        }

        return implode('', $variables)."\n".implode('', $parts);
    } */

    /**
     * @var int
     */
    private int $tabs = 0;

    /**
     * @param object $data
     * @return string
     */
    public function translate(object $data): string
    {
        $result = [];

        $this->tabs = 0;
        $source = $this->makeLevel($data->tree);

        $variables = [];
        foreach ($data->variables as $var => $v) {
            $variables[] = 'int '.$var.";\n";
        }

        $variablesSource = implode("\n", $variables);
        if ($variablesSource) {
            $result[] = $variablesSource;
        }

        if ($source) {
            $result[] = $source;
        }

        return implode("\n", $result);
    }

    /**
     * @param array $level
     * @param $delimiter
     * @return string
     */
    private function makeLevel(array &$level, $delimiter = ''): string
    {
        $result = [];

        foreach ($level as $item) {
            switch ($item->typ) {
                case Translate::BLOCK_IF:
                    $result[] = $this->blockIf($item);
                    break;
                case Translate::BLOCK_SWITCH:
                    $result[] = $this->blockSwitch($item);
                    break;
                case Translate::BLOCK_CASE:
                    $result[] = $this->blockCase($item);
                    break;
                case Translate::BLOCK_DEFAULT:
                    $result[] = $this->blockDefault($item);
                    break;
                case Translate::BLOCK_BREAK:
                    $result[] = 'break;'."\n";
                    break;
                case Translate::BLOCK_FUNC:
                    $result[] = $this->blockFunc($item);
                    break;
                case Translate::BLOCK_BRACKETS:
                    $result[] = $this->blockBrackets($item);
                    break;
                case Translate::BLOCK_SUB:
                    $result[] = $this->blockSub($item);
                    break;
                case Translate::BLOCK_STRING:
                    if ($this->stringManager) {
                        $key = $this->stringManager->getKeyByString($item->value);
                        if ($key !== false) {
                            $result[] = $key;
                        } else {
                            $result[] = "'".$item->value."'";
                        }
                    } else {
                        $result[] = "'".$item->value."'";
                    }
                    break;
                case Translate::BLOCK_VAR:
                    $result[] = $item->value;
                    break;
                case Translate::BLOCK_NUMBER:
                    $result[] = $item->value;
                    break;
                case Translate::BLOCK_SYM:
                    if ($item->value == ';') {
                        $result[] = ";\n";
                    } else {
                        $result[] = ' '.$item->value.' ';
                    }
                    break;
            }
        }

        $resultText = implode($delimiter, $result);
        if (!str_contains($resultText, "\n")) {
            return $resultText;
        }
        $resultList = [];
        foreach (explode("\n", $resultText) as $line) {
            if (trim($line)) {
                $resultList[] = ($this->tabs ? self::TAB_STR : '').$line;
            }
        }
        return implode("\n", $resultList);
    }

    /**
     * @param $item
     * @return string
     */
    private function blockIf(&$item): string
    {
        $block = 'if '.$this->blockBrackets($item->condition, '').' '
            .$this->blockSub($item->true);
        if ($item->false) {
            $block .= ' else '.
                $this->blockSub($item->false);
        }
        return $block."\n";
    }

    /**
     * @param $item
     * @return string
     */
    private function blockSwitch(&$item): string
    {
        $result = [];
        $result[] = 'switch '.$this->blockBrackets($item->condition).' '
            .$this->blockSub($item->children);
        return implode("\n", $result);
    }

    /**
     * @param $item
     * @return string
     */
    private function blockCase(&$item): string
    {
        $caseValue = [$item->value];
        $result = [];
        $result[] = "\n".'case '.$this->makeLevel($caseValue).':';
        $this->tabs++;
        $result[] = $this->makeLevel($item->children);
        $this->tabs--;
        return implode("\n", $result);
    }

    /**
     * @param $item
     * @return string
     */
    private function blockDefault(&$item): string
    {
        $result = [];
        $result[] = "\n".'default:';
        $this->tabs++;
        $result[] = $this->makeLevel($item->children);
        $this->tabs--;
        return implode("\n", $result);
    }

    /**
     * @param $item
     * @return string
     */
    private function blockFunc(&$item): string
    {
        $name = $item->name;
        $argsCount = count($item->args);

        $result = [];
        if (isset($this->functions[$name])) {
            foreach ($this->functions[$name] as $num => $func) {
                if (is_numeric($num)) {
                    if ($num == $argsCount) {
                        $result[] = $func;
                        break;
                    }
                } else {
                    $c = substr($num, 0, strlen($num) - 1);
                    if ($c == '') $c = 0;
                    if ($argsCount >= $c) {
                        $result[] = $func;
                        array_unshift($item->args, (object)[
                            'typ' => Translate::BLOCK_NUMBER,
                            'value' => $argsCount,
                        ]);
                        break;
                    }
                }
            }
        } else {
            $result[] = $name;
        }

        $result[] = $this->blockBrackets($item->args, ', ');

        return implode('', $result);
    }

    /**
     * @param $list
     * @param $delimiter
     * @return string
     */
    private function blockBrackets(&$list, $delimiter = ''): string
    {
        $result = [];
        $result[] = '(';
        $result[] = $this->makeLevel($list, $delimiter);
        $result[] = ')';
        return implode('', $result);
    }

    /**
     * @param $list
     * @return string
     */
    private function blockSub(&$list): string
    {
        $result = [];
        $result[] = '{';
        $this->tabs++;
        $result[] = $this->makeLevel($list);
        $result[] = '}';
        $this->tabs--;
        return implode("\n", $result);
    }
}
