@extends('dialog')

@section('title')
@lang('admin/configuration.firmware_title')
@endsection

@section('content')
<div class="content">
    <div class="strong">@lang('admin/configuration.firmware-make-title'):</div>
    <div class="row" style="padding-bottom: 1rem;">
        <div class="col-sm-12">
            <div class="form-control" style="height: auto;overflow-x: auto;">
                <div style="white-space: pre;padding: 0.5rem;">{!! $data !!}</div>
            </div>
        </div>
    </div>
    @if(!$makeError)
    <div class="row" id="firmware-start">
        <div class="offset-sm-3 col-sm-6" style="display: flex;">
            <button class="btn btn-primary flex-grow-1" onclick="firmwareStart()">@lang('admin/configuration.firmware-start')</button>
        </div>
    </div>
    <div style="display: none;" id="progress-firmware">
        <div class="">@lang('admin/configuration.firmware-start-progress') <span id="firmwareController"></span></div>
        <div class="row">
            <div class="col-sm-12">
                <div class="progress">
                    <div class="progress-bar progress-bar-striped progress-bar-animated" style="width: 0%"></div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection

@section('buttons')
    <div style="flex-grow: 1"></div>
    <button id="btn-close" type="button" class="btn btn-secondary" data-dismiss="modal">@lang('dialogs.btn_close')</button>
@endsection

@section('script')
<script>
    $(document).ready(function () {
        
    });
    
    function firmwareStart() {
        $('#firmware-start').hide(250);
        
        $.ajax({
            url: "{{ route('configuration-firmware-start') }}",
            success: function (data) {
                $('#progress-firmware').show(250);
                firmwareButtons(0);
                firmwareStatus();
            }
        });
    }
    
    function firmwareStatus() {
        $.ajax({
            url: "{{ route('configuration-firmware-status') }}",
            success: function (data) {
                console.log(data);
                if (data.error) {
                    alert(data.error);
                    firmwareButtons(1);
                    return;
                } else
                if (data.firmware) {
                    if (data.firmware == 'COMPLETE') {
                        $('#firmwareController').text('');
                        $('#progress-firmware .progress-bar').css({
                            width: '100%',
                        });
                        
                        alert("@lang('admin/configuration.firmware-complete')", () => {
                            dialogHide(() => {
                                window.location.reload();
                            });                            
                        });
                        
                        firmwareButtons(1);
                        return ;
                    }
                } else {
                    $('#firmwareController').text(data.controller);
                    $('#progress-firmware .progress-bar').css({
                        width: data.percent + '%',
                    });
                }
                
                setTimeout(firmwareStatus, 500);
            },
        });
    }
    
    function firmwareButtons(show) {
        if (show) {
            $('#btn-close').removeAttr('disabled');
            $('#btn-dialog-close').removeAttr('disabled');
            $('#dialog_window').modal({keyboard: true});
        } else {
            $('#btn-close').attr('disabled', 'true');
            $('#btn-dialog-close').attr('disabled', 'true');
            $('#dialog_window').modal({keyboard: false});
        }
    }

</script>
@endsection
