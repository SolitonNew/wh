<?php

namespace App\Library\Script\Translators;

/**
 * Description of Php$prepareData$prepareData$prepareData
 *
 * @author soliton
 */
class Php implements ITranslator
{
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
            '+' => '$this->function_speech',
        ],
        'play' => [
            '+' => '$this->function_play',
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
     * @param object $prepareData
     * @return string
     */
    public function translate(object $prepareData): string
    {
        $parts = $prepareData->parts;
        $variables = $prepareData->variables;

        for ($i = 0; $i < count($parts); $i++) {
            if (is_object($parts[$i])) {
                if (isset($this->functions[$parts[$i]->name])) {
                    if (isset($this->functions[$parts[$i]->name]['+'])) {
                        $parts[$i] = $this->functions[$parts[$i]->name]['+'];
                    } else {
                        $parts[$i] = $this->functions[$parts[$i]->name][$parts[$i]->args];
                    }
                } else {
                    $parts[$i] = $parts[$i]->name;
                }
            } else
            if (isset($variables[$parts[$i]])) {
                $parts[$i] = '$'.$parts[$i];
            }
        }

        return implode('', $parts);
    }
}
