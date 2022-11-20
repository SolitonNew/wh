<div class="card">
    <div id="checkUpdate" class="card-body">
        <h5 class="card-title">@lang('admin/settings.check_update_title')</h5>
        <div class="row">
            <div class="col-lg-6 d-flex align-items-center mb-3">
                @lang('admin/settings.current_version'): {{ App\Models\Property::VERSION }}
            </div>
            <div class="col-lg-6 d-flex justify-content-end mb-3">
                <button class="btn btn-primary">@lang('admin/settings.check_update_btn')</button>
            </div>
        </div>
    </div>
</div>
<script>
    $(document).ready(function () {
        $('#checkUpdate button').on('click', function () {
            dialogLg('{{ route("admin.settings-check-updates") }}');
        });
    });
</script>
