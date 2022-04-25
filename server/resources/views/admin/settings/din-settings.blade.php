<div class="card">
    <div id="dinSettings" class="card-body">
        <h5 class="card-title">@lang('admin/settings.din_settings_title')</h5>
        <div class="row mb-3">
            <div class="col-sm-2">
                <label class="form-label">@lang('admin/settings.din_settings_port')</label>
            </div>
            <div class=" col-sm-5">
                <input id="dinSettingsPort" class="form-control" value="{{ App\Models\Property::getDinSettings()->port }}">
            </div>
            <div class="col-sm-2">
                <label class="form-label">@lang('admin/settings.din_settings_baud')</label>
            </div>
            <div class=" col-sm-3">
                <select id="dinSettingsBaud" class="custom-select">
                    @foreach(App\Library\Serial::BAUDS as $baud)
                    <option value="{{ $baud }}" {{ App\Models\Property::getDinSettings()->baud == $baud ? 'selected' : '' }}>{{ $baud }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-sm-2">
                <label class="form-label">@lang('admin/settings.din_settings_mmcu')</label>
            </div>
            <div class="col-sm-5">
                <select id="dinSettingsMmcu" class="custom-select">
                    @foreach(config('din') as $key => $val)
                    <option value="{{ $key }}" {{ App\Models\Property::getDinSettings()->mmcu == $key ? 'selected' : '' }}>{{ $key }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="d-flex justify-content-end">
            <button class="btn btn-primary" disabled="">@lang('dialogs.btn_save')</button>
        </div>
    </div>
</div>
<script>
    $(document).ready(function () {
        $('#dinSettings input, #dinSettings select').on('input', function () {
            $('#dinSettings .btn').removeAttr('disabled');
        });
        
        $('#dinSettings .btn').on('click', function () {
            $.ajax({
                method: 'post',
                url: '{{ route("admin.settings-set-din-settings") }}',
                data: {
                    _token: '{{ @csrf_token() }}',
                    port: $('#dinSettingsPort').val(),
                    baud: $('#dinSettingsBaud').val(),
                    mmcu: $('#dinSettingsMmcu').val(),
                },
                success: function (data) {
                    if (data == 'OK') {
                        window.location.reload();
                    } else {
                        alert(data);
                    }
                },
                error: function (err) {
                    alert(err);
                }
            });
        });
    });
</script>