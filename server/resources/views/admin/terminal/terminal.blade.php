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
            <div class="card">
                <div id="terminalMaxLevel" class="card-body">
                    <h5 class="card-title">@lang('admin/terminal.max_level_title')</h5>
                    @if($levels[1])
                    <div class="custom-control custom-radio">
                        <input type="radio" name="radioDisabled" id="planMaxLevel1" data-value="1" class="custom-control-input" {{ $maxLevel == 1 ? 'checked' : '' }}>
                        <label class="custom-control-label" for="planMaxLevel1">{{ $levels[1] }}</label>
                    </div>
                    @if($levels[2])
                    <div class="custom-control custom-radio">
                        <input type="radio" name="radioDisabled" id="planMaxLevel2" data-value="2" class="custom-control-input" {{ $maxLevel == 2 ? 'checked' : '' }}>
                        <label class="custom-control-label" for="planMaxLevel2">{{ $levels[2] }}</label>
                    </div>
                    @endif
                    @if($levels[3])
                    <div class="custom-control custom-radio">
                        <input type="radio" name="radioDisabled" id="planMaxLevel3" data-value="3" class="custom-control-input" {{ $maxLevel == 3 ? 'checked' : '' }}>
                        <label class="custom-control-label" for="planMaxLevel3">{{ $levels[3] }}</label>
                    </div>
                    @endif
                    @else
                    <b class="text-muted">&lt; @lang('admin/terminal.plan_rooms_empty') &gt;</b>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(() => {
        $('#terminalMaxLevel input').on('input', function () {
            $.ajax({
                method: 'post',
                url: '{{ route("admin.terminal-set-max-level", "") }}/' + $(this).data('value'),
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