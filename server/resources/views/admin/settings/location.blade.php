<style>
    #locationMapPreview {
        width: 100%;
        height: 350px;
        border: 1px solid #ced4da;
        border-radius: 0.25rem;
    }
</style>
<div class="card">
    <div id="location" class="card-body">
        <h5 class="card-title">@lang('admin/settings.location_title')</h5>
        <div class="form-group">
            <div class="row mb-3">
                <div class="col-sm-12">
                    <iframe id="locationMapPreview" frameborder="0"></iframe>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-sm-4">
                    <label class="form-label">@lang('admin/settings.location_latitude')</label>
                </div>
                <div class="col-sm-8">
                    <input id="locationLatitude" class="form-control" value="{{ App\Models\Property::getLocation()->latitude }}">
                </div>
            </div>
            <div class="row">
                <div class="col-sm-4">
                    <label class="form-label">@lang('admin/settings.location_longitude')</label>
                </div>
                <div class="col-sm-8">
                    <input id="locationLongitude" class="form-control" value="{{ App\Models\Property::getLocation()->longitude }}">
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
        $('#locationLatitude, #locationLongitude').on('input', function () {
            $('#location .btn').removeAttr('disabled');
            updateMap();
        });
        
        $('#location .btn').on('click', function () {
            $.ajax({
                method: 'post',
                url: '{{ route("admin.settings-set-location") }}',
                data: {
                    latitude: $('#locationLatitude').val(),
                    longitude: $('#locationLongitude').val(),
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
        
        let updateMapTimeout = false;
        
        function updateMap()
        {
            clearTimeout(updateMapTimeout);
            updateMapTimeout = setTimeout(function () {
                let coords = $('#locationLatitude').val() + ',' + $('#locationLongitude').val();
                let url = 'https://maps.google.com/maps?q=' + encodeURI(coords) + '&t=&z=16&ie=UTF8&iwloc=&output=embed';
                $('#locationMapPreview').attr('src', url);
            }, 500);
        }
        
        updateMap();
    });
</script>