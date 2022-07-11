@foreach($template['params'] as $key => $val)
<div class="row" data-param="{{ $key }}" {{ isset($val['event']) && $val['event'] ? 'data-event=""' : '' }}>
    <div class="col-sm-3">
        <label class="form-label strong">{{ $val['title'] }}</label>
    </div>
    <div class="col-sm-9">
        @if($val['typ'] == 'device')
        <select class="custom-select" name="param_{{ $key }}">
            <option value=""></option>
            @foreach(App\Models\Device::getDeviceListByAppControl($val['app_control']) as $device)
            <option value="{{ $device->name }}" data-room="{{ $device->room ? $device->room->name : '' }}" data-id="{{ $device->id }}">{{ $device->name }}</option>
            @endforeach
        </select>
        @elseif($val['typ'] == 'variable')
        <select class="custom-select" name="param_{{ $key }}">
            <option value=""></option>
            @foreach(App\Models\Device::getDeviceListByAppControl($val['app_control']) as $device)
            <option value="{{ $device->name }}" data-room="{{ $device->room ? $device->room->name : '' }}" data-id="{{ $device->id }}">{{ $device->name }}</option>
            @endforeach
        </select>
        @elseif($val['typ'] == 'const')
        <input class="form-control" type="text" name="param_{{ $key }}" value="" required="" data-room="">
        @endif
        <div class="invalid-feedback"></div>
    </div>
</div>
@endforeach
<input id="scriptTemplateName" data-param="" value="{{ $template['name'] }}" style="display: none;">
<textarea id="scriptTemplateSource" data-param="" style="display: none;">{!! $template['template'] !!}</textarea>