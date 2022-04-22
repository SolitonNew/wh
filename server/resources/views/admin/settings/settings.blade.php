@extends('admin.admin')

@section('down-menu')
@endsection

@section('top-menu')
@endsection

@section('content')
<style>
    .card {
        margin-bottom: 1rem;
    }
</style>
<div class="content-body" style="margin: 1rem;">
    <div class="row">
        <div class="col-sm-6">
            @include('admin.settings.timezone')
            @include('admin.settings.structure-deph')
        </div>
        <div class="col-sm-6">
            @include('admin.settings.location')
        </div>
    </div>
</div>

<script>
    $(document).ready(() => {
        $('#terminalMaxLevel input').on('input', function () {
            $.ajax({
                method: 'post',
                url: '{{ route("admin.settings-set-max-level", "") }}/' + $(this).data('value'),
                data: {_token: '{{ @csrf_token() }}'},
                success: function (data) {
                    if (data == 'OK') {
                        
                    } else {
                        
                    }
                },
            });
        });
    });
</script>
@endsection