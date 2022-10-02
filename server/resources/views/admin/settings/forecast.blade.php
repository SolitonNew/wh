<div class="card">
    <div id="forecast" class="card-body">
        <h5 class="card-title">@lang('admin/settings.forecast_title')</h5>
        <div class="form-group">
            <div class="row mb-3">
                <div class="col-sm-4">
                    <label class="form-label">@lang('admin/settings.forecast_TEMP')</label>
                </div>
                <div class="col-sm-8">
                    <select id="forecastTEMP" class="custom-select">
                        <option value="">-//-</option>
                        @foreach(App\Models\Device::getForecastSortList() as $row)
                        <option value="{{ $row->id }}" {{ App\Models\Property::getForecastSettings()->TEMP == $row->id ? 'selected' : '' }}>{{ $row->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-sm-4">
                    <label class="form-label">@lang('admin/settings.forecast_P')</label>
                </div>
                <div class="col-sm-8">
                    <select id="forecastP" class="custom-select">
                        <option value="">-//-</option>
                        @foreach(App\Models\Device::getForecastSortList() as $row)
                        <option value="{{ $row->id }}" {{ App\Models\Property::getForecastSettings()->P == $row->id ? 'selected' : '' }}>{{ $row->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-sm-4">
                    <label class="form-label">@lang('admin/settings.forecast_CC')</label>
                </div>
                <div class="col-sm-8">
                    <select id="forecastCC" class="custom-select">
                        <option value="">-//-</option>
                        @foreach(App\Models\Device::getForecastSortList() as $row)
                        <option value="{{ $row->id }}" {{ App\Models\Property::getForecastSettings()->CC == $row->id ? 'selected' : '' }}>{{ $row->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-sm-4">
                    <label class="form-label">@lang('admin/settings.forecast_G')</label>
                </div>
                <div class="col-sm-8">
                    <select id="forecastG" class="custom-select">
                        <option value="">-//-</option>
                        @foreach(App\Models\Device::getForecastSortList() as $row)
                        <option value="{{ $row->id }}" {{ App\Models\Property::getForecastSettings()->G == $row->id ? 'selected' : '' }}>{{ $row->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-sm-4">
                    <label class="form-label">@lang('admin/settings.forecast_H')</label>
                </div>
                <div class="col-sm-8">
                    <select id="forecastH" class="custom-select">
                        <option value="">-//-</option>
                        @foreach(App\Models\Device::getForecastSortList() as $row)
                        <option value="{{ $row->id }}" {{ App\Models\Property::getForecastSettings()->H == $row->id ? 'selected' : '' }}>{{ $row->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-sm-4">
                    <label class="form-label">@lang('admin/settings.forecast_V')</label>
                </div>
                <div class="col-sm-8">
                    <select id="forecastV" class="custom-select">
                        <option value="">-//-</option>
                        @foreach(App\Models\Device::getForecastSortList() as $row)
                        <option value="{{ $row->id }}" {{ App\Models\Property::getForecastSettings()->V == $row->id ? 'selected' : '' }}>{{ $row->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-sm-4">
                    <label class="form-label">@lang('admin/settings.forecast_WD')</label>
                </div>
                <div class="col-sm-8">
                    <select id="forecastWD" class="custom-select">
                        <option value="">-//-</option>
                        @foreach(App\Models\Device::getForecastSortList() as $row)
                        <option value="{{ $row->id }}" {{ App\Models\Property::getForecastSettings()->WD == $row->id ? 'selected' : '' }}>{{ $row->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-sm-4">
                    <label class="form-label">@lang('admin/settings.forecast_WS')</label>
                </div>
                <div class="col-sm-8">
                    <select id="forecastWS" class="custom-select">
                        <option value="">-//-</option>
                        @foreach(App\Models\Device::getForecastSortList() as $row)
                        <option value="{{ $row->id }}" {{ App\Models\Property::getForecastSettings()->WS == $row->id ? 'selected' : '' }}>{{ $row->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-sm-4">
                    <label class="form-label">@lang('admin/settings.forecast_MP')</label>
                </div>
                <div class="col-sm-8">
                    <select id="forecastMP" class="custom-select">
                        <option value="">-//-</option>
                        @foreach(App\Models\Device::getForecastSortList() as $row)
                        <option value="{{ $row->id }}" {{ App\Models\Property::getForecastSettings()->MP == $row->id ? 'selected' : '' }}>{{ $row->name }}</option>
                        @endforeach
                    </select>
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
        $('#forecast select').on('change', function () {
            $('#forecast .btn').removeAttr('disabled');
        });

        $('#forecast .btn').on('click', function () {
            $.ajax({
                method: 'post',
                url: '{{ route("admin.settings-set-forecast") }}',
                data: {
                    TEMP: $('#forecastTEMP').val(),
                    P: $('#forecastP').val(),
                    CC: $('#forecastCC').val(),
                    G: $('#forecastG').val(),
                    H: $('#forecastH').val(),
                    V: $('#forecastV').val(),
                    WD: $('#forecastWD').val(),
                    WS: $('#forecastWS').val(),
                    MP: $('#forecastMP').val(),
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
