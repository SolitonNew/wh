<div class="card">
    <div id="pyhomeSettings" class="card-body">
        <h5 class="card-title">@lang('admin/settings.pyhome_settings_title')</h5>
        <div class="row mb-3">
            <div class="col-lg-6 mb-3">
                <div class="row">
                    <div class="col-sm-4">
                        <label class="form-label">@lang('admin/settings.pyhome_settings_port')</label>
                    </div>
                    <div class=" col-sm-8">
                        <input id="pyhomeSettingsPort" class="form-control" value="{{ App\Models\Property::getPyhomeSettings()->port }}">
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
        $('#pyhomeSettings input, #pyhomeSettings select').on('input', function () {
            $('#pyhomeSettings .btn').removeAttr('disabled');
        });

        $('#pyhomeSettings .btn').on('click', function () {
            $.ajax({
                method: 'post',
                url: '{{ route("admin.settings-set-pyhome-settings") }}',
                data: {
                    port: $('#pyhomeSettingsPort').val(),
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
