<?php

namespace App\Library\Script\Translators;

use App\Library\Script\ScriptStringManager;
use App\Library\Script\Translate;
use Illuminate\Support\Facades\Log;

/**
 * Description of Php$prepareData$prepareData$prepareData
 *
 * @author soliton
 */
class Php extends TranslatorBase
{
    const TAB_STR = '    ';

    /**
     * @var array
     */
    private array $functions = [
        'get' => [
            1 => '$this->function_get',
        ],
        'set' => [
            2 => '$this->function_set',
            3 => '$this->function_set',
        ],
        'on' => [
            1 => '$this->function_on',
            2 => '$this->function_on',
        ],
        'off' => [
            1 => '$this->function_off',
            2 => '$this->function_off',
        ],
        'toggle' => [
            1 => '$this->function_toggle',
            2 => '$this->function_toggle',
        ],
        'speech' => [
            '1+' => '$this->function_speech',
        ],
        'play' => [
            '1+' => '$this->function_play',
        ],
        'info' => [
            0 => '$this->function_info',
        ],
        'print_i' => [
            1 => '$this->function_print',
        ],
        'print_f' => [
            1 => '$this->function_print',
        ],
        'print_s' => [
            1 => '$this->function_print',
        ],
        'abs_i' => [
            1 => '$this->function_abs_i',
        ],
        'abs_f' => [
            1 => '$this->function_abs_f',
        ],
        'round' => [
            1 => '$this->function_round',
        ],
        'ceil' => [
            1 => '$this->function_ceil',
        ],
        'floor' => [
            1 => '$this->function_floor',
        ],
    ];

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
        $this->tabs = 0;
        return $this->makeLevel($data->tree);
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
                    $result[] = "$".$item->value;
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
