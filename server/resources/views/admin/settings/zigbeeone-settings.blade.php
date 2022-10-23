<div class="card">
    <div id="zigbeeoneSettings" class="card-body">
        <h5 class="card-title">@lang('admin/settings.zigbeeone_settings_title')</h5>
        <div class="row">
            <div class="col-lg-6 mb-3">
                <div class="row">
                    <div class="col-sm-4">
                        <label class="form-label">@lang('admin/settings.zigbeeone_settings_port')</label>
                    </div>
                    <div class=" col-sm-8">
                        <input id="zigbeeoneSettingsPort" class="form-control" value="{{ App\Library\Daemons\ZigbeeoneDaemon::getSettings('PORT', config('zigbeeone.default_port')) }}">
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
        $('#zigbeeoneSettings input, #zigbeeoneSettings select').on('input', function () {
            $('#zigbeeoneSettings .btn').removeAttr('disabled');
        });

        $('#zigbeeoneSettings .btn').on('click', function () {
            $.ajax({
                method: 'post',
                url: '{{ route("admin.settings-set-zigbeeone-settings") }}',
                data: {
                    port: $('#zigbeeoneSettingsPort').val(),
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
