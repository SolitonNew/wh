@extends('dialog')

@section('title')
@lang('admin/plan.plan_order_title')
@endsection

@section('content')
<form id="plan_order_form" method="POST" action="{{ route('admin.plan-order', ['id' => $partID]) }}">
    <button type="submit" style="display: none;"></button>
    <div class="form-control tree" style="height: auto; min-height: 300px;max-height: 300px;">
    @foreach($data as $row)
    <a href="#" class="tree-item" data-id="{{ $row->id }}">{{ $row->name }}</a>
    @endforeach
    </div>
</form>
<div style="padding-top: 1rem;">
    <button id="btnOrderUp" class="btn btn-primary" onclick="planOrderUp()">@lang('dialogs.btn_up')</button>
    <button id="btnOrderDown" class="btn btn-primary" onclick="planOrderDown()">@lang('dialogs.btn_down')</button>
</div>
@endsection

@section('buttons')
    <button type="button" class="btn btn-primary" onclick="planOrderOK()">@lang('dialogs.btn_ok')</button>
    <button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('dialogs.btn_cancel')</button>
@endsection

@section('script')
<script>
    $(document).ready(() => {
        $('#plan_order_form').ajaxForm({
            beforeSubmit: function(arr) {
                let res = new Array();
                $('#plan_order_form .tree-item').each(function() {
                    res.push($(this).data('id'));
                });
                arr.push({
                    name: 'orderIds',
                    value: res.join(',')
                });
            },
            success: function(data) {
                if (data == 'OK') {
                    dialogHide(() => {
                        window.location.reload();
                    });
                } else {
                    dialogShowErrors(data);
                }
            },
        });

        $('#plan_order_form .tree-item').on('click', function (e) {
            $('#plan_order_form .tree-item.active').removeClass('active');
            $(this).addClass('active');
            e.preventDefault();
            planOrderRefresh();
        });

        planOrderRefresh();
    });

    function planOrderOK() {
        $('#plan_order_form').submit();
    }

    function planOrderRefresh() {
        let up = false;
        let down = false;

        let curr = $('#plan_order_form .tree-item.active');
        if (curr.length) {
            up = (curr.prev().length > 0);
            down = (curr.next().length > 0);
        }

        if (up) {
            $('#btnOrderUp').removeAttr('disabled');
        } else {
            $('#btnOrderUp').attr('disabled', 'true');
        }

        if (down) {
            $('#btnOrderDown').removeAttr('disabled');
        } else {
            $('#btnOrderDown').attr('disabled', 'true');
        }
    }

    function planOrderUp() {
        let curr = $('#plan_order_form .tree-item.active');
        if (curr.length) {
            let prev = $(curr.prev());
            if (prev.length) {
                curr.insertBefore(prev);
                planOrderRefresh();
            }
        }
    }

    function planOrderDown() {
        let curr = $('#plan_order_form .tree-item.active');
        if (curr.length) {
            let next = $(curr.next());
            if (next.length) {
                curr.insertAfter(next);
                planOrderRefresh();
            }
        }
    }

</script>
@endsection
