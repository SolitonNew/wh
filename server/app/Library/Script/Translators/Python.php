<?php

namespace App\Library\Script\Translators;

use App\Library\Script\Translate;
use Illuminate\Support\Facades\Log;

class Python extends TranslatorBase
{
    const TAB_STR = '    ';

    /**
     * @var array
     */
    private array $functions = [
        'get' => [
            1 => 'commands.command_get',
        ],
        'set' => [
            2 => 'commands.command_set',
            3 => 'commands.command_set',
        ],
        'on' => [
            1 => 'commands.command_on',
            2 => 'commands.command_on',
        ],
        'off' => [
            1 => 'commands.command_off',
            2 => 'commands.command_off',
        ],
        'toggle' => [
            1 => 'commands.command_toggle',
            2 => 'commands.command_toggle',
        ],
        'speech' => [
            '1+' => 'commands.command_speech',
        ],
        'play' => [
            '1+' => 'commands.command_play',
        ],
        'info' => [
            0 => 'commands.command_info',
        ],
        'print_i' => [
            1 => 'commands.command_print',
        ],
        'print_f' => [
            1 => 'commands.command_print',
        ],
        'print_s' => [
            1 => 'commands.command_print',
        ],
        'abs_i' => [
            1 => 'commands.command_abs',
        ],
        'abs_f' => [
            1 => 'commands.command_abs',
        ],
        'round' => [
            1 => 'commands.command_round',
        ],
        'ceil' => [
            1 => 'commands.command_ceil',
        ],
        'floor' => [
            1 => 'commands.command_floor',
        ],
    ];

    /**
     * @var int
     */
    private int $tabs = 0;

    private int $labelSiquence = 1;
    private array $breakesStack = [];

    //private array $

    /**
     * @param object $data
     * @return string
     */
    public function translate(object $data): string
    {
        $this->tabs = 0;
        $this->labelSiquence = 1;
        $this->breakesStack = [];
        return $this->makeLevel($data->tree) ?: 'pass';
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
                    $result[] = $this->blockBreake($item);
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
                        $result[] = "\n";
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
        $block = 'if '.$this->blockBrackets($item->condition, '');
        if (count($item->true)) {
            $block .= $this->blockSub($item->true);
        } else {
            $block .= ":\n".self::TAB_STR."pass";
        }

        if ($item->false) {
            $block .= "\n".'else'.
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
        $switchID = $this->labelSiquence++;

        $result = [];
        $result[] = '# Start Switch Block';
        $result[] = 'switch_condition_'.$switchID.' = '.$this->blockBrackets($item->condition, '', false);

        $labels = [];
        $this->breakesStack[] = 'breake_'.$switchID;
        foreach ($item->children as $child) {
            $labelName = 'label_'.($this->labelSiquence++);
            $labels[] = $labelName;
            switch ($child->typ) {
                case Translate::BLOCK_CASE:
                    $caseValue = [$child->value];
                    $result[] = 'if (switch_condition_'.$switchID.' == '.$this->makeLevel($caseValue).'):';
                    $result[] = self::TAB_STR.'goto '.$labelName;
                    break;
                case Translate::BLOCK_DEFAULT:
                    break;
            }
        }

        $i = 0;
        foreach ($item->children as $child) {
            $result[] = $labels[$i].':';
            $result[] = $this->makeLevel($child->children);
            $i++;
        }

        if (count($this->breakesStack)) {
            $result[] = array_pop($this->breakesStack).':';
        }

        $result[] = '# End Switch Block';

        return implode("\n", $result);
    }

    /**
     * @param $item
     * @return string
     */
    private function blockCase(&$item): string
    {
        $result = [];
        $result[] = $this->makeLevel($item->children);
        return implode("\n", $result);
    }

    /**
     * @param $item
     * @return string
     */
    private function blockDefault(&$item): string
    {
        $result = [];
        $result[] = $this->makeLevel($item->children);
        return implode("\n", $result);
    }

    /**
     * @param $tem
     * @return string
     */
    private function blockBreake(&$tem): string
    {
        if (count($this->breakesStack) == 0) return '';
        return 'goto '.$this->breakesStack[count($this->breakesStack) - 1].':';
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
     * @param $withBrackets
     * @return string
     */
    private function blockBrackets(&$list, $delimiter = '', $withBrackets = true): string
    {
        $result = [];
        if ($withBrackets) {
            $result[] = '(';
        }
        $result[] = $this->makeLevel($list, $delimiter);
        if ($withBrackets) {
            $result[] = ')';
        }
        return implode('', $result);
    }

    /**
     * @param $list
     * @param $withTabs
     * @return string
     */
    private function blockSub(&$list, $withTabs = true): string
    {
        $result = [];
        if ($withTabs) {
            $result[] = ':';
            $this->tabs++;
        }
        $result[] = $this->makeLevel($list);
        if ($withTabs) {
            $this->tabs--;
        }
        return implode("\n", $result) ?: 'pass';
    }
}
