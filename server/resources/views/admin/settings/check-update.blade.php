<div class="card">
    <div id="checkUpdate" class="card-body">
        <h5 class="card-title">@lang('admin/settings.check_update_title')</h5>
        <button class="btn btn-primary">@lang('admin/settings.check_update_btn')</button>
    </div>
</div>
<script>
    $(document).ready(function () {
        $('#checkUpdate button').on('click', function () {
            dialog('{{ route("admin.settings-check-updates") }}');
        });
    });
</script>
