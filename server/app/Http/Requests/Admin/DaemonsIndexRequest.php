<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use App\Library\DaemonManager;

class DaemonsIndexRequest extends FormRequest
{
    const LAST_VIEW_ID = 'DAEMON_INDEX_ID';
    
    /**
     * 
     * @return boolean
     */
    public function authorize()
    {
        return true;
    }
    
    /**
     * 
     * @param type $keys
     * @return type
     */
    public function all($keys = null)
    {
        $data = parent::all($keys);
        $data['id'] = $this->route('id');
        return $data;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(DaemonManager $daemonManager)
    {
        $daemonID = $this->route('id');
        if ($daemonID) {
            $this->redirect = route('admin.jurnal-daemons', $daemonManager->daemons()[0]);
            return [
                'id' => 'required',
            ];
        } else {
            $newId = $this->getIdForView();
            if ($newId) {
                $this->redirect = route('admin.jurnal-daemons', [$newId]);
                return [
                    'id' => 'required',
                ];
            } else {
                return [];
            }
        }
    }
    
    /**
     * 
     */
    protected function passedValidation()
    {
        $daemonID = $this->route('id');
        $this->storeLastVisibleId($daemonID);
    }
    
    /**
     * 
     * @param int $id
     */
    public function storeLastVisibleId(string $id = null)
    {
        $this->session()->put(self::LAST_VIEW_ID, $id);
    }
    
    /**
     * 
     * @return type
     */
    public function getIdForView()
    {
        $id = $this->session()->get(self::LAST_VIEW_ID);
        $this->session()->put(self::LAST_VIEW_ID, null);
        if (!$id) {
            $id = (new DaemonManager())->daemons()[0];
        }
        
        return $id;
    }
}
