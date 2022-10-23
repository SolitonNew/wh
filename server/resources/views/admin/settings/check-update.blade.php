<div class="card">
    <div id="checkUpdate" class="card-body">
        <h5 class="card-title">@lang('admin/settings.check_update_title')</h5>
        <div class="row">
            <div class="col-sm-12">
                @lang('admin/settings.current_version'): {{ App\Models\Property::VERSION }}
            </div>
        </div>
        <div class="d-flex justify-content-end">
            <button class="btn btn-primary">@lang('admin/settings.check_update_btn')</button>
        </div>
    </div>
</div>
<script>
    $(document).ready(function () {
        $('#checkUpdate button').on('click', function () {
            dialog('{{ route("admin.settings-check-updates") }}');
        });
    });
</script>
