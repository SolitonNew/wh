@extends('dialog')

@section('title')
    @lang('admin/settings.backup_meta_title')
@endsection

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-sm-8">
                <form id="settings_backup_meta_import_form" method="POST" action="{{ route('admin.settings-backup-meta-import') }}">
                    <div class="form-group">
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" id="file" name="file">
                            <label class="custom-file-label" for="file" data-browse="@lang('admin/plan.import_btn')"></label>
                        </div>
                        <div class="invalid-feedback" data-formfield="file"></div>
                    </div>
                </form>
            </div>
            <div class="col-sm-4">
                <a href="#" id="settingsBackupMetaImport"
                   class="btn btn-primary d-flex justify-content-center">@lang('admin/settings.backup_meta_import')</a>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-8">

            </div>
            <div class="col-sm-4">
                <a href="{{ route('admin.settings-backup-meta-export') }}"
                   class="btn btn-primary d-flex justify-content-center"
                   target="_blank">@lang('admin/settings.backup_meta_export')</a>
            </div>
        </div>
    </div>
@endsection

@section('buttons')
    <div style="flex-grow: 1"></div>
    <button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('dialogs.btn_close')</button>
@endsection

@section('script')
    <script>
        $(document).ready(() => {
            //
        });
    </script>
@endsection
