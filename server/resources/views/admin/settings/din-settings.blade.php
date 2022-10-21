<div class="card">
    <div id="dinSettings" class="card-body">
        <h5 class="card-title">@lang('admin/settings.din_settings_title')</h5>
        <div class="row mb-3">
            <div class="col-lg-6 mb-3">
                <div class="row">
                    <div class="col-sm-4">
                        <label class="form-label">@lang('admin/settings.din_settings_port')</label>
                    </div>
                    <div class=" col-sm-8">
                        <input id="dinSettingsPort" class="form-control" value="{{ App\Library\Daemons\DinDaemon::getSettings('PORT', config('din.default_port')) }}">
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="row">
                    <div class="col-sm-4">
                        <label class="form-label">@lang('admin/settings.din_settings_mmcu')</label>
                    </div>
                    <div class="col-sm-8">
                        <select id="dinSettingsMmcu" class="custom-select">
                            @foreach(config('din.mmcu_list') as $key => $val)
                            <option value="{{ $key }}" {{ (App\Library\Daemons\DinDaemon::getSettings('MMCU') ?: config('din.default_mmcu')) == $key ? 'selected' : '' }}>{{ $key }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
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
                    port: $('#dinSettingsPort').val(),
                    mmcu: $('#dinSettingsMmcu').val(),
                },
                success: function (data) {
                    if (data == 'OK') {
                        reloadWithWaiter();
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
