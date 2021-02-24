@extends('admin.admin')

@section('top-menu')
<button class="btn btn-secondary" onclick="planZoomIn()">@lang('admin/plan.zoom_in')</button>
<button class="btn btn-secondary" onclick="planZoomOut()">@lang('admin/plan.zoom_out')</button>
@endsection

@section('down-menu')
<a href="#" class="dropdown-item" onclick="planAdd(); return false;">@lang('admin/plan.plan_add')</a>
<a href="#" class="dropdown-item" onclick="planEdit(); return false;">@lang('admin/plan.plan_edit')</a>
<div class="dropdown-divider"></div>
<a href="#" class="dropdown-item" onclick="planMoveChilds(); return false;">@lang('admin/plan.plan_move_childs')</a>
<a href="#" class="dropdown-item" onclick="planOrder(); return false;">@lang('admin/plan.plan_order')</a>
@endsection

@section('content')
<div style="display: flex; flex-direction: row; flex-grow: 1;height: 100%;">
    <div class="tree" style="width: 250px;min-width:250px; border-right: 1px solid rgba(0,0,0,0.125);" scroll-store="partPlanList">
        @foreach(\App\Http\Models\PlanPartsModel::generateTree() as $row)
        <a href="{{ route('plan', $row->ID) }}"
           class="tree-item {{ $row->ID == $partID ? 'active' : '' }}" style="padding-left: {{ $row->level + 1 }}rem">{{ $row->NAME }}</a>
        @endforeach
    </div>
    <div class="content-body" style="display:flex" scroll-store="planContentScroll">
        <div id="planContentOff" style="position:relative;">
            <div id="planContent" class="plan-parts-content" style="position:absolute;">
            @foreach($data as $row)
                @if($row->W > 0 && $row->H > 0)
                <div class="plan-part {{ $loop->first ? 'current' : '' }}" data-id="{{ $row->ID }}"
                     data-x="{{ $row->X }}" data-y="{{ $row->Y }}" data-w="{{ $row->W }}" data-h="{{ $row->H }}"></div>
                @endif
            @endforeach
            </div>
        </div>
    </div>
</div>

<script>
    var planZoom = 50;

    $(document).ready(() => {
        $('div.plan-part').on('click', function (e) {
            dialog('{{ route("plan-edit", "") }}/' + $(this).attr('data-id'));
        });

        planZoom = getCookie('planZoom');
        if (planZoom == undefined) {
            planZoom = 50;
        }

        $(window).on('resize', function() {
            planResize();
        }).trigger('resize');
    });

    function planResize() {
        let minX = 10000;
        let minY = 10000;
        let maxX = 0;
        let maxY = 0;

        $('#planContent .plan-part').css({
            'transition-duration': '0s',
        });

        $('#planContent .plan-part').each(function() {
            let x = $(this).data('x');
            let y = $(this).data('y');
            let w = $(this).data('w');
            let h = $(this).data('h');

            x = x * planZoom;
            y = y * planZoom;
            w = w * planZoom;
            h = h * planZoom;

            if (x < minX) minX = x;
            if (y < minY) minY = y;
            if (x + w > maxX) maxX = x + w;
            if (y + h > maxY) maxY = y + h;

            $(this).css({
                left: x + 'px',
                top: y + 'px',
                width: w + 'px',
                height: h + 'px',
            });
        });

        let w = maxX - minX;
        let h = maxY - minY;

        let p_w = $('.content-body').width();
        let p_h = $('.content-body').height();

        let nx = (p_w - w) / 2 - minX;
        if (nx < -minX) nx = -minX;

        let ny = (p_h - h) / 2 - minY;
        if (ny < -minY) ny = -minY;

        $('#planContent').css({
            left: nx + 'px',
            top: ny + 'px',
            width: w + minX + 'px',
            height: h + minY + 'px',
        });

        $('#planContent .plan-part').css({
            'transition-duration': '0.25s',
        });
    }

    function planZoomIn() {
        let z = planZoom * 1.5;
        if (z > 200) z = 200;
        planZoom = z;
        planResize();

        setCookie('planZoom', planZoom);
    }

    function planZoomOut() {
        let z = planZoom / 1.5;
        if (z < 2) z = 2;
        planZoom = z;
        planResize();

        setCookie('planZoom', planZoom);
    }

    function planAdd() {
        dialog('{{ route("plan-edit", [-1, $partID]) }}');
    }

    function planEdit() {
        dialog('{{ route("plan-edit", $partID) }}');
    }

    function planMoveChilds() {
        dialog('{{ route("plan-move-childs", $partID) }}');
    }

    function planOrder() {
        dialog('{{ route("plan-order", $partID) }}');
    }
</script>
@endsection
