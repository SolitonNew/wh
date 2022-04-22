<div class="card">
    <div id="timezone" class="card-body">
        <h5 class="card-title">@lang('admin/settings.timezone_title')</h5>
        <div class="form-group">
            <select id="timezoneSelect" class="custom-select">
                @foreach(\DateTimeZone::listIdentifiers() as $zone)
                <option value="{{ $zone }}" {{ \App\Models\Property::getTimezone() == $zone ? 'selected' : '' }}>{{ $zone }}</option>
                @endforeach
            </select>
        </div>
        <div class="d-flex justify-content-end">
            <button class="btn btn-primary" disabled="">@lang('dialogs.btn_save')</button>
        </div>
    </div>
</div>
<script>
    $(document).ready(function () {
        $('#timezoneSelect').on('change', function () {
            $('#timezone .btn').removeAttr('disabled');
        });
        
        $('#timezone .btn').on('click', function () {
            $.ajax({
                method: 'post',
                url: '{{ route("admin.settings-set-timezone") }}',
                data: {
                    _token: '{{ @csrf_token() }}',
                    timezone: $('#timezoneSelect').val(),
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