@extends('admin.admin')

@section('down-menu')
<a href="#" class="dropdown-item" onclick="planAdd(); return false;">@lang('admin/plan.plan_add')</a>
@if($partID)
<a href="#" class="dropdown-item" onclick="planEdit(); return false;">@lang('admin/plan.plan_edit')</a>
<div class="dropdown-divider"></div>
<a href="#" class="dropdown-item" onclick="planMoveChilds(); return false;">@lang('admin/plan.plan_move_childs')</a>
<a href="#" class="dropdown-item" onclick="planOrder(); return false;">@lang('admin/plan.plan_order')</a>
@endif
<div class="dropdown-divider"></div>
<a href="#" class="dropdown-item" onclick="planImport(); return false;">@lang('admin/plan.plan_import')</a>
@if($partID)
<a href="{{ route('admin.plan-export') }}" class="dropdown-item">@lang('admin/plan.plan_export')</a>
@endif
@endsection

@section('top-menu')
@if($partID)
<button class="btn btn-secondary" onclick="planZoomIn()">@lang('admin/plan.zoom_in')</button>
<button class="btn btn-secondary" onclick="planZoomOut()">@lang('admin/plan.zoom_out')</button>
@endif
@endsection

@section('content')
@if($partID)
<div style="display: flex; flex-direction: row; flex-grow: 1;height: 100%;">
    <div class="tree" style="width: 250px;min-width:250px; border-right: 1px solid rgba(0,0,0,0.125);" scroll-store="partPlanList">
        @foreach(\App\Http\Models\PlanPartsModel::generateTree() as $row)
        <a href="{{ route('admin.plan', $row->id) }}"
           class="tree-item {{ $row->id == $partID ? 'active' : '' }}" style="padding-left: {{ $row->level + 1 }}rem">{{ $row->name }}</a>
        @endforeach
    </div>
    <div id="planContentScroll" class="content-body" style="display:flex; user-select: none;" scroll-store="planContentScroll">
        <div id="planContentOff" style="position:relative;">
            <div id="planContent" class="plan-parts-content" style="position:absolute;">
            @foreach($data as $row)
                @if($row->W > 0 && $row->H > 0)
                <div class="plan-part" data-id="{{ $row->id }}"
                     style="border: {{ $row->pen_width }}px {{ $row->pen_style }} {{ $row->pen_color }}; background-color: {{ $row->fill_color }}"
                     data-x="{{ $row->X }}" data-y="{{ $row->Y }}" data-w="{{ $row->W }}" data-h="{{ $row->H }}"></div>
                @endif
            @endforeach
            </div>
        </div>
    </div>
</div>
@endif

<script>
    var planZoom = 50;
    var planMouseDown = false;
    var planMouseScroll = false;
    var planMinX = 0;    
    var planMinY = 0;    
    
    const planMouseScrollDelta = 15;
    const planZoomStep = 1.25;

    $(document).ready(() => {
        @if($partID)
        $('div.plan-part').on('click', function (e) {
            if (planMouseScroll) return ;
            dialog('{{ route("admin.plan-edit", "") }}/' + $(this).attr('data-id'));
        });
        @endif

        planZoom = getCookie('planZoom');
        if (planZoom == undefined) {
            planZoom = 50;
        }

        $(window).on('resize', function() {
            planResize();
        }).trigger('resize');
        
        $('#planContentScroll').on('mousewheel', function (e) {
            e.preventDefault();
            if (e.originalEvent.wheelDelta > 0) {
                planZoomIn();
            } else {
                planZoomOut();
            }
        }).on('mousedown', function (e) {
            planMouseScroll = false;
            planMouseDown = {
                x: e.screenX,
                y: e.screenY,
                scrollX: $(this).scrollLeft(),
                scrollY: $(this).scrollTop(),
            };
        }).on('mousemove', function (e) {
            if (!planMouseDown) return ;
            if (e.buttons == 1) {
                if (planMouseScroll) {
                    $(this).scrollLeft(planMouseDown.scrollX - e.screenX + planMouseDown.x);
                    $(this).scrollTop(planMouseDown.scrollY - e.screenY + planMouseDown.y);
                } else {
                    planMouseScroll = ((Math.abs(planMouseDown.x - e.screenX) > planMouseScrollDelta) ||
                                       (Math.abs(planMouseDown.y - e.screenY) > planMouseScrollDelta));
                }
                
                if (planMouseScroll) {
                    $('div', this).css('cursor', 'all-scroll');
                }
            } else {
                $('div', this).css('cursor', '');
            }
        }).on('mouseup', function (e) {
            $('div', this).css('cursor', '');
        });        
    });

    function planResize() {
        let minX = 999999;
        let minY = 999999;
        let maxX = -999999;
        let maxY = -999999;

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

        let p_w = $('#planContentScroll').width();
        let p_h = $('#planContentScroll').height();

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
        
        planMinX = minX;
        planMinY = minY;
    }

    function planZoomIn() {
        let z = planZoom * planZoomStep;
        if (z > 400) z = 400;
        
        let z_off = z / planZoom;
        let s_w = $('#planContentScroll').width();
        let s_h = $('#planContentScroll').height();
        let s_x = $('#planContentScroll').scrollLeft();
        let s_y = $('#planContentScroll').scrollTop();
        
        planZoom = z;
        planResize();
        
        let c_x = (s_x + s_w / 2) * z_off - s_w / 2;
        $('#planContentScroll').scrollLeft(c_x);
        let c_y = (s_y + s_h / 2) * z_off - s_h / 2;
        $('#planContentScroll').scrollTop(c_y);
        
        setCookie('planZoom', planZoom);
    }

    function planZoomOut() {
        let z = planZoom / planZoomStep;
        if (z < 2) z = 2;
        
        let z_off = z / planZoom;
        let s_w = $('#planContentScroll').width();
        let s_h = $('#planContentScroll').height();
        let s_x = $('#planContentScroll').scrollLeft();
        let s_y = $('#planContentScroll').scrollTop();
        
        planZoom = z;
        planResize();
        
        let c_x = (s_x + s_w / 2) * z_off - s_w / 2;
        $('#planContentScroll').scrollLeft(c_x);
        let c_y = (s_y + s_h / 2) * z_off - s_h / 2;
        $('#planContentScroll').scrollTop(c_y);

        setCookie('planZoom', planZoom);
    }

    function planAdd() {
        dialog('{{ route("admin.plan-edit", [-1, $partID]) }}');
    }

    @if($partID)
    function planEdit() {
        dialog('{{ route("admin.plan-edit", $partID) }}');
    }

    function planMoveChilds() {
        dialog('{{ route("admin.plan-move-childs", $partID) }}');
    }

    function planOrder() {
        dialog('{{ route("admin.plan-order", $partID) }}');
    }
    @endif
    
    function planImport() {
        dialog('{{ route("admin.plan-import") }}');
    }
</script>
@endsection
