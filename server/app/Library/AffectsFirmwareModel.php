<?php

namespace App\Library;

use \Illuminate\Database\Eloquent\Model;

class AffectsFirmwareModel extends Model
{
    /**
     * @var array
     */
    protected array $affectFirmwareFields = [];

    /**
     * @param array $options
     * @return void
     */
    public function finishSave(array $options = []): void
    {
        $firmwareChanged = count($this->affectFirmwareFields) ? $this->isDirty($this->affectFirmwareFields) : true;
        parent::finishSave($options);
        if ($firmwareChanged && !in_array('withoutevents', $options)) {
            event(new \App\Events\FirmwareChangedEvent());
        }
    }

    /**
     * @return bool|null
     */
    public function delete(): bool|null
    {
        $return = parent::delete();
        event(new \App\Events\FirmwareChangedEvent());
        return $return;
    }
}
