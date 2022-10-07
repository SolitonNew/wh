@extends('dialog')

@section('title')
@lang('admin/hubs.config_wizard_title')
@endsection

@section('content')
<style>
    .configWizardHidePage {
        display: none;
    }
</style>
<div class="content">
    <div id="configWizardPage_begin" class="">
        <div class="form-control mb-4" style="height: auto;">
            @foreach($hubs as $hub)
                <div class="row mt-2 mb-2">
                    <div class="col-sm-5 d-flex full-width justify-content-end">{{ $hub->name }}</div>
                </div>
            @endforeach
        </div>
        <div class="d-flex justify-content-center full-width">
            <a id="configWizardMakeBtn" href="#" class="btn btn-primary">
                @lang('admin/hubs.config_wizard_make_btn')
            </a>
        </div>
    </div>
    <div id="configWizardPage_make" class="configWizardHidePage">
        <div class="form-control mb-4" style="height: auto;">
            @foreach($hubs as $hub)
                <div class="row mt-2 mb-2">
                    <div class="col-sm-5 d-flex full-width justify-content-end">{{ $hub->name }}</div>
                    <div class="col-sm-7 d-flex font-weight-bold align-items-center hub_item" data-typ="{{ $hub->typ }}">
                        <div class="progress" style="width: 100%; height: 0.75rem;">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" style="width: 100%"></div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        <div class="d-flex justify-content-center full-width">
            <a id="configWizardTransmitBtn" href="#" class="btn btn-primary disabled">
                @lang('admin/hubs.config_wizard_transmit_btn')
            </a>
        </div>
    </div>
    <div id="configWizardPage_transmit" class="configWizardHidePage">
        <div class="form-control mb-4" style="height: auto;">
            @foreach($hubs as $hub)
                <div class="row mt-2 mb-2">
                    <div class="col-sm-5 d-flex full-width justify-content-end">{{ $hub->name }}</div>
                    <div class="col-sm-7 d-flex font-weight-bold align-items-center" data-typ="{{ $hub->typ }}" data-id="{{ $hub->id }}">
                        <div class="progress" style="width: 100%; height: 0.75rem;">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" style="width: 0%"></div>
                        </div>
                        <div class="transmit_info"></div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection

@section('buttons')
    <div style="flex-grow: 1"></div>
    <button id="btn-close" type="button" class="btn btn-secondary" data-dismiss="modal">@lang('dialogs.btn_close')</button>
@endsection

@section('script')
<script>
    var transmitStatusInterval = 500;

    $(document).ready(function () {
        $('#configWizardMakeBtn').on('click', function () {
            $('#configWizardPage_begin').hide();
            $('#configWizardPage_make').show();
            configWizardMake();
            return false;
        });

        $('#configWizardTransmitBtn').on('click', function () {
            $('#configWizardPage_make').hide();
            $('#configWizardPage_transmit').show();
            configWizardTransmit();
            return false;
        });
    });

    function configWizardMake() {
        let types = [];

        $('#configWizardPage_make .hub_item').each(function () {
            let typ = $(this).data('typ');
            if (types.indexOf(typ) == -1) {
                types.push(typ);
            }
        });

        let errors = [];
        let requestCounter = types.length;
        types.forEach(function (item) {
            $.ajax({
                url: '{{ route("admin.hubs-config-wizard-make", ["typ" => ""]) }}/' + item,
                success: function (data) {
                    if (data == 'OK') {
                        $('#configWizardPage_make .hub_item[data-typ="' + item + '"]').text('OK');
                    } else {
                        $('#configWizardPage_make .hub_item[data-typ="' + item + '"]').text('ERROR');
                        errors.push(data);
                    }
                    refreshPage();
                },
                error: function (err) {
                    errors.push(err);
                    refreshPage();
                }
            });
        });

        function refreshPage() {
            requestCounter--;
            if (requestCounter == 0) {
                if (errors.length == 0) {
                    $('#configWizardTransmitBtn').removeClass('disabled');
                } else {
                    alert(errors.join('\n'));
                }
            }
        }
    }

    function configWizardTransmit() {
        configWizardBlock(true);

        $.ajax({
            url: '{{ route("admin.hubs-config-wizard-transmit") }}',
            success: function (data) {
                setTimeout(configWizardTransmitStatus, transmitStatusInterval);
            },
            error: function (err) {

            }
        });
    }

    function configWizardTransmitStatus() {
        if ($('#dialog_window').css('display') == 'none') return ;

        $.ajax({
            url: '{{ route("admin.hubs-config-wizard-status") }}',
            data: {
                ids: [{{ implode(', ', $hubIds) }}],
            },
            success: function (data) {
                let finishedCount = 0;
                let hasErrors = false;

                data.forEach(function (item) {
                    let row = $('#configWizardPage_transmit [data-id="' + item.id + '"]');

                    switch (item.status) {
                        case 'PENDING':
                            $('.progress', row).hide();
                            $('.transmit_info', row).show().text('PENDING');
                            break;
                        case 'ERROR':
                            $('.progress', row).hide();
                            $('.transmit_info', row).show().text('ERROR');
                            finishedCount++;
                            hasErrors = true;
                            break;
                        case 'IN PROGRESS':
                            $('.progress', row).show();
                            $('.progress > div', row).width(item.percent + '%');
                            $('.transmit_info', row).hide();
                            break;
                        case 'COMPLETE':
                            $('.progress', row).hide();
                            $('.transmit_info', row).show().text('COMPLETE');
                            finishedCount++;
                            break;
                    }
                });

                if (data.length > finishedCount) {
                    setTimeout(configWizardTransmitStatus, transmitStatusInterval);
                } else {
                    configWizardBlock(false);

                    let message = '';
                    if (hasErrors) {
                        message = "@lang('admin/hubs.config_wizard_complete_error')";
                    } else {
                        message = "@lang('admin/hubs.config_wizard_complete')";
                    }

                    alert(message, () => {
                        dialogHide(() => {
                            reloadWithWaiter();
                        });
                    });
                }
            },
            error: function (err) {
                setTimeout(configWizardTransmitStatus, transmitStatusInterval);
            }
        });
    }

    function configWizardBlock(block) {
        if (block) {
            $('#btn-close').attr('disabled', 'true');
            $('#btn-dialog-close').attr('disabled', 'true');
            $('#dialog_window').data('bs.modal')._config.keyboard = false;
        } else {
            $('#btn-close').removeAttr('disabled');
            $('#btn-dialog-close').removeAttr('disabled');
            $('#dialog_window').data('bs.modal')._config.keyboard = true;
        }
    }
</script>
@endsection
