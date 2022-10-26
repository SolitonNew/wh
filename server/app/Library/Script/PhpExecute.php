<?php

namespace App\Library\Script;

use App\Models\Device;
use Illuminate\Support\Facades\Log;

/**
 * Description of PhpExecute
 *
 * @author soliton
 */
class PhpExecute
{
    use PhpFunctions\FunctionGet,
        PhpFunctions\FunctionOff,
        PhpFunctions\FunctionOn,
        PhpFunctions\FunctionPlay,
        PhpFunctions\FunctionPrint,
        PhpFunctions\FunctionSet,
        PhpFunctions\FunctionSpeech,
        PhpFunctions\FunctionToggle,
        PhpFunctions\FunctionAbs,
        PhpFunctions\FunctionRound,
        PhpFunctions\FunctionCeil,
        PhpFunctions\FunctionFloor;

    /**
     * @var bool
     */
    protected bool $fake = false;

    /**
     * @var Translate
     */
    protected Translate $translator;

    /**
     * @param string $source
     */
    public function __construct(string $source)
    {
        $this->translator = new Translate($source);
    }

    /**
     * @var array
     */
    protected array $outLines = [];

    /**
     * @param bool $fake
     * @param array|null $report
     * @return string
     */
    public function run(bool $fake = false, array &$report = null): string
    {
        try {
            $this->fake = $fake;
            $specialList = [];
            foreach (Device::get() as $dev) {
                $specialList[$dev->name] = false;
            }
            $code = $this->translator->run(new Translators\Php(new ScriptStringManager($specialList)), $report);
            eval($code);
        } catch (\ParseError $ex) {
            $report['error'] = str_replace('$', '', $ex->getMessage());
            $this->printLine($ex->getMessage());
        } catch (\Throwable $ex) {
            $report['error'] = str_replace('$', '', $ex->getMessage());
            $this->printLine($ex->getMessage());
        }

        return implode("\n", $this->outLines);
    }

    /**
     * @param string $text
     * @return void
     */
    public function printLine(string $text): void
    {
        $this->outLines[] = $text;
    }
}
