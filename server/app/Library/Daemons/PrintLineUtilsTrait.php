<?php

namespace App\Library\Daemons;

trait PrintLineUtilsTrait
{
    /**
     * @param array $out
     * @param array|null $in
     * @return void
     */
    public function printSyncDevices(array $out, array|null $in)
    {
        foreach (array_chunk($out, 15) as $key => $chunk) {
            if ($key == 0) {
                $s = '   >>   ';
            } else {
                $s = '        ';
            }
            $this->printLine($s.'['.implode(', ', $chunk).']');
        }
        if (!count($out)) {
            $this->printLine('   >>   []');
        }

        if ($in !== null) {
            $this->printLine("   <<   [".implode(', ', $in)."]");
        }
    }
}
