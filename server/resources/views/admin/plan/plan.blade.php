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
@endsection

@section('content')
@if($partID)
<div style="display: flex; flex-direction: row; flex-grow: 1;height: 100%;">
    <div id="planParts" class="tree" style="width: 250px;min-width:250px; border-right: 1px solid rgba(0,0,0,0.125);" scroll-store="partPlanList">
        @foreach(\App\Http\Models\PlanPartsModel::generateTree(null, false) as $row)
        <a href="{{ route('admin.plan', $row->id) }}" data-id="{{ $row->id }}"
            class="tree-item {{ $row->id == $partID ? 'active' : '' }}">
            @foreach($row->treePath as $v)
            <span class="tree-item-path tree-item-path-{{ $v }}"></span>
            @endforeach
            {{ $row->name }}
        </a>
        @endforeach
    </div>
    <div id="planContentScroll" class="content-body" style="display:flex; user-select: none;" scroll-store="planContentScroll">
        <div id="planContentOff" style="position:relative;">
            <div id="planContent" class="plan-parts-content" style="position:absolute;">
            @foreach($data as $row)
                @if($row->W > 0 && $row->H > 0)
                <div class="plan-part" data-id="{{ $row->id }}"
                     style="border: {{ $row->pen_width }}px {{ $row->pen_style }} {{ $row->pen_color }}; background-color: {{ $row->fill_color }}"
                     data-x="{{ $row->X }}" data-y="{{ $row->Y }}" 
                     data-w="{{ $row->W }}" data-h="{{ $row->H }}"
                     data-pen-style="{{ $row->pen_style }}" data-pen-width="{{ $row->pen_width }}"></div>
                @endif
            @endforeach
            @foreach($devices as $row)
            <div class="plan-device" 
                 data-id="{{ $row->id }}" data-part-id="{{ $row->group_id }}"
                 data-position="{{ $row->position }}"
                 data-part-bounds="{{ $row->partBounds }}"></div>
            @endforeach
            </div>
        </div>
    </div>
    <div id="planPartMenu" class="dropdown-menu">
        <a class="dropdown-item strong" href="#" onclick="planMenuPlanEdit(); return false;">@lang('admin/plan.menu_plan_edit')</a>
        <a class="dropdown-item" href="#" onclick="planSelInTree(); return false;">@lang('admin/plan.menu_sel_in_tree')</a>
        <div class="dropdown-divider"></div>
        <div class="dropdown-item dropdown-menu-sub">
            <div><span>@lang('admin/plan.menu_clone_part')</span></div>
            <div class="dropdown-menu">
                <a class="dropdown-item" href="#" onclick="planMenuClonePart('top'); return false;">@lang('admin/plan.menu_clone_part_top')</a>
                <a class="dropdown-item" href="#" onclick="planMenuClonePart('right'); return false;">@lang('admin/plan.menu_clone_part_right')</a>
                <a class="dropdown-item" href="#" onclick="planMenuClonePart('bottom'); return false;">@lang('admin/plan.menu_clone_part_bottom')</a>
                <a class="dropdown-item" href="#" onclick="planMenuClonePart('left'); return false;">@lang('admin/plan.menu_clone_part_left')</a>
            </div>
        </div>
        <div class="dropdown-divider"></div>
        <a class="dropdown-item" href="#" onclick="planMenuAddPart(); return false;">@lang('admin/plan.menu_add_part')</a>
        <div class="dropdown-divider"></div>
        <a class="dropdown-item" href="#" onclick="planMenuAddDevice(); return false;">@lang('admin/plan.menu_add_device')</a>
    </div>
</div>
@endif

<script>
    const planMouseScrollDelta = 15;
    const planZoomStep = 1.25;
    const planZoomMax = 1000;
    const planZoomMin = 5;
    const planPenZoomScale = 50;
    const planMinPenWidth = 0.5;
    
    var planZoom = 50;
    var planMouseDown = false;
    var planMouseScroll = false;
    var planMinX = 0;    
    var planMinY = 0;    
    var planContextMenuID = -1;

    $(document).ready(() => {
        window.addEventListener('mousedown', function (e) {
            if ($('#planPartMenu').find(e.target).length == 0) {
                $('#planPartMenu').hide();
            }
        });
        
        window.addEventListener('mouseup', function (e) {
            $('#planPartMenu').hide();
        });
        
        @if($partID)
        $('#planContent .plan-part').on('click', function (e) {
            if (planMouseScroll) return ;
            dialog('{{ route("admin.plan-edit", "") }}/' + $(this).attr('data-id'));
        }).on('contextmenu', function (e) {
            let h = $('#planPartMenu').height();
            let x = e.pageX;
            let y = e.pageY;
            let pageH = window.innerHeight - 40; /* 40 - это заглушка */
            if (y + h > pageH) {
                y = pageH - h;
            }
            $('#planPartMenu').css({
                left: x + 'px',
                top: y + 'px',
            }).show();
            planContextMenuID = $(this).data('id');
            return false;
        }).on('mouseover', function () {
            $('#planParts a.tree-item[data-id="' + $(this).data('id') + '"]').addClass('hover'); 
        }).on('mouseleave', function () {
            $('#planParts a.tree-item[data-id="' + $(this).data('id') + '"]').removeClass('hover');
        });
        
        $('#planContent .plan-device').on('click', function (e) {
            dialog('{{ route("admin.plan-link-device", ["", ""]) }}/' + $(this).data('part-id') + '/' + $(this).data('id'));
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
            $('#planPartMenu').hide();
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
        
        $('#planParts a.tree-item').on('mouseover', function () {
            $('#planContent .plan-part[data-id="' + $(this).data('id') + '"]').addClass('hover');
        }).on('mouseleave', function () {
            $('#planContent .plan-part[data-id="' + $(this).data('id') + '"]').removeClass('hover');
        });
    });

    function planResize() {
        let penWidth2Parts = new Array(); /* Нужен кеш с вычислениями бордеров, что бы правильно позиционировать устройства */
        
        let minX = 99999999;
        let minY = 99999999;
        let maxX = -99999999;
        let maxY = -99999999;

        $('#planContent .plan-part').css({
            'transition-duration': '0s',
        });

        /* Настраиваем отображение комнат */
        $('#planContent .plan-part').each(function() {
            let x = $(this).data('x');
            let y = $(this).data('y');
            let w = $(this).data('w');
            let h = $(this).data('h');
            let penStyle = $(this).data('pen-style');
            let penWidth = 0; 
            if (penStyle !== 'none') {
                let pw = parseInt($(this).data('pen-width'));
                penWidth = pw ? pw : 1;
                penWidth = penWidth * planZoom / planPenZoomScale;
                if (penWidth < planMinPenWidth) penWidth = planMinPenWidth;
            }
            let penWidth2 = Math.ceil(penWidth / 2);
            penWidth = penWidth2 + penWidth2;
            
            penWidth2Parts.push({
                id: $(this).data('id'),
                width: penWidth2,
            });

            x = x * planZoom - penWidth2;
            y = y * planZoom - penWidth2;
            w = w * planZoom + penWidth;
            h = h * planZoom + penWidth;

            if (x < minX) minX = x;
            if (y < minY) minY = y;
            if (x + w > maxX) maxX = x + w;
            if (y + h > maxY) maxY = y + h;

            $(this).css({
                left: x + 'px',
                top: y + 'px',
                width: w + 'px',
                height: h + 'px',
                'border-width': penWidth + 'px',
            });
        });
        
        /* Настраиваем отображение устройств */
        $('#planContent .plan-device').each(function () {
            let partId = $(this).data('part-id');
            let partPenWidth2 = 1;
            for (let i = 0; i < penWidth2Parts.length; i++) {
                if (penWidth2Parts[i].id === partId) {
                    partPenWidth2 = penWidth2Parts[i].width;
                    break;
                }
            }
            let partPenWidth = partPenWidth2 + partPenWidth2;
            
            let position = $(this).data('position');
            let partBounds = $(this).data('partBounds');
            
            let w = $(this).width() + 4; /* Учитывается толщина обводки */
            let h = $(this).height() + 4;
            
            let partX = partBounds.X * planZoom + partPenWidth2;
            let partY = partBounds.Y * planZoom + partPenWidth2;
            let partW = partBounds.W * planZoom - partPenWidth;
            let partH = partBounds.H * planZoom - partPenWidth;
            
            let kx = (partW - w) / partBounds.W;
            let ky = (partH - h) / partBounds.H;
            
            switch (position.surface) {
                case 'top':
                    $(this).css({
                        left: (partX + position.offset * kx) + 'px',
                        top: (partY) + 'px',
                    });
                    break;
                case 'right':
                    $(this).css({
                        left: (partX + partW - w) + 'px',
                        top: (partY + position.offset * ky) + 'px',
                    });
                    break;
                case 'bottom':
                    $(this).css({
                        left: (partX + partW - position.offset * kx - w) + 'px',
                        top: (partY + partH - h) + 'px',
                    });
                    break;
                case 'left':
                    $(this).css({
                        left: (partX) + 'px',
                        top: (partY + partH - h - position.offset * ky) + 'px',
                    });
                    break;
                case 'roof':
                    $(this).css({
                        left: (partX + position.offset * kx) + 'px',
                        top: (partY + position.cross * ky) + 'px',
                    });
                    break;
            }
        });
        
        /* Настраиваем область отображения */
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
        if (z > planZoomMax) z = planZoomMax;
        
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
        if (z < planZoomMin) z = planZoomMin;
        
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
    
    @if($partID)
    function planMenuPlanEdit() {
        dialog('{{ route("admin.plan-edit", "") }}/' + planContextMenuID);
    }

    function planSelInTree() {
        window.location.href = '{{ route("admin.plan", "") }}/' + planContextMenuID;
    }

    function planMenuAddPart() {
        dialog('{{ route("admin.plan-edit", [-1, ""]) }}/' + planContextMenuID);
    }
        
    function planMenuAddDevice() {
        dialog('{{ route("admin.plan-link-device", ["", ""]) }}/' + planContextMenuID + '/-1');
    }
    
    function planMenuClonePart(direction) {
        $.ajax({
            url: '{{ route("admin.plan-clone", ["", ""]) }}/' + planContextMenuID + '/' + direction,
            success: function (data) {
                if (data == 'OK') {
                    window.location.reload();
                } else {
                    
                }
            },
        });
    }
    @endif
</script>
@endsection
