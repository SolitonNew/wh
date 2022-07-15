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
<style>
    .plan-part-toolbar {
        display:flex;
        align-items: center; 
        background-color: #ffffff;
        padding: 0.375rem 1.5rem;
        border: 1px solid #ced4da;
        border-radius: .25rem;
    }
    
    .plan-part-toolbar > * {
        margin: 0px;
        margin-right: 1.25rem;
    }
    
    .plan-part-toolbar .btn {
        margin: 0.25rem;
    }
    
    .main-content {
        display: flex;
        flex-direction: column;
    }
</style>

<div id="planToolbar" style="margin: -0.5rem 0px;display: flex;justify-content: center;width: 100%;">
    <div class="plan-part-toolbar">
        <label id="toolbarPartName" class="strong">Room</label>
        
        <label id="toolbarOperation" class="">operation</label>

        <label id="toolbarLabel1" class="strong">X</label>
        <input id="toolbarValue1" class="form-control" type="number" step="0.01" style="width: 80px;" oninput="planToolbarValue1(event);">
        
        <label id="toolbarLabel2" class="strong">Y</label>
        <input id="toolbarValue2" class="form-control" type="number" step="0.01" style="width: 80px;" oninput="planToolbarValue2(event)">

        <button class="btn btn-primary" onclick="planToolbarOk()">@lang('dialogs.btn_ok')</button>
        <button class="btn btn-secondary" onclick="planToolbarCancel()">@lang('dialogs.btn_cancel')</button>
    </div>
</div>

@endsection

@section('content')
<style>
    .content-body {
        background-image: url('/img/plan/grid.svg');
    }
    
    #planContentOff {
        opacity: 0.65;
    }
    
    .btn-zoom {
        padding: 12px 12px; 
        border-radius: 2rem;
        border: 1px solid #ffffff;
    }
</style>

@if($partID)
<div id="planPartsCompact" class="navbar navbar-page" style="display: none;">
    <select id="planPartsCombobox" class="nav-link custom-select select-tree" style="width: 100%;">
        @foreach(\App\Models\Room::generateTree() as $row)
        <option value="{{ $row->id }}" {{ $row->id == $partID ? 'selected' : '' }}>{!! $row->treePath !!} {{ $row->name }}</option>
        @endforeach
    </select>
</div>
<div style="display: flex; flex-direction: row; flex-grow: 1;height: 100%;">
    <div id="planParts" class="tree" style="width: 250px;min-width:250px; border-right: 1px solid rgba(0,0,0,0.125);" scroll-store="partPlanList">
        @foreach(\App\Models\Room::generateTree(null, false) as $row)
        <a href="{{ route('admin.plan', ['id' => $row->id]) }}" data-id="{{ $row->id }}"
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
                <div class="plan-part {{ $row->fill }}" data-id="{{ $row->id }}" data-parent-id="{{ $row->parent_id }}"
                     style="border: {{ $row->pen_width }}px {{ $row->pen_style }};"
                     data-x="{{ $row->X }}" data-y="{{ $row->Y }}" 
                     data-w="{{ $row->W }}" data-h="{{ $row->H }}"
                     data-pen-style="{{ $row->pen_style }}" data-pen-width="{{ $row->pen_width }}"
                     data-name-dx="{{ $row->name_dx }}" data-name-dy="{{ $row->name_dy }}">
                    <span>{{ $row->name }}</span>
                </div>
                @endif
            @endforeach
            @foreach($ports as $port)
            <div class="plan-port" 
                 data-id="{{ $port->id }}" data-index="{{ $port->index }}" data-part-id="{{ $port->partID }}" 
                 data-position="{{ $port->position }}" 
                 data-part-bounds="{{ $port->partBounds }}"></div>
            @endforeach
            @foreach($devices as $row)
            <div class="plan-device dev-{{ $row->app_control }}" 
                 data-id="{{ $row->id }}" data-part-id="{{ $row->room_id }}"
                 data-position="{{ $row->position }}"
                 data-part-bounds="{{ $row->partBounds }}"></div>
            @endforeach
            </div>
        </div>
    </div>
    <div id="planPartMenu" class="dropdown-menu">
        <div class="plan-part-context">
            <a class="dropdown-item strong" href="#" onclick="planMenuPlanEdit(); return false;">@lang('admin/plan.menu_plan_edit')</a>
            <a class="dropdown-item" href="#" onclick="planSelInTree(); return false;">@lang('admin/plan.menu_sel_in_tree')</a>
            <div class="dropdown-divider"></div>
            <a class="dropdown-item" href="#" onclick="planMenuToolbar('move'); return false;">@lang('admin/plan.menu_toolbar_move')</a>
            <a class="dropdown-item" href="#" onclick="planMenuToolbar('size'); return false;">@lang('admin/plan.menu_toolbar_size')</a>
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
            <a class="dropdown-item" href="#" onclick="planMenuAddPort(); return false;">@lang('admin/plan.menu_add_port')</a>
            <a class="dropdown-item" href="#" onclick="planMenuAddDevice(); return false;">@lang('admin/plan.menu_add_device')</a>
        </div>
        <div class="plan-device-context">
            <a class="dropdown-item strong" href="#" onclick="planMenuDeviceLink(); return false;">@lang('admin/plan.menu_device_link')</a>
            <div class="dropdown-divider"></div>
            <a class="dropdown-item" href="#" onclick="planMenuDeviceEdit(); return false;">@lang('admin/plan.menu_device_edit')</a>
        </div>
        <div class="plan-port-context">
            <a class="dropdown-item strong" href="#" onclick="planMenuPortEdit(); return false;">@lang('admin/plan.menu_port_edit')</a>
        </div>
    </div>
</div>
<div class="only-small" style="position: absolute; right: 1rem; bottom: 1rem;">
    <div style="display: flex; flex-direction: column;">
        <a href="#" class="btn btn-dark btn-zoom" id="planZoomInBtn" style="margin-bottom: 0.5rem;">
            <img src="/img/zoom-in-3x.png">
        </a>
        <a href="#" class="btn btn-dark btn-zoom" id="planZoomOutBtn">
            <img src="/img/zoom-out-3x.png">
        </a>
    </div>
</div>
@else
<div style="display: flex; flex-direction: column; flex-grow: 1;height: 100%; align-items: center;">
    <div class="page-jumbotron">
        <div class="jumbotron">
            <h5 class="mb-4">@lang('admin/plan.main_prompt')</h5>
            <a href="javascript:planMenuAddPart()" class="btn btn-primary">@lang('admin/plan.menu_add_part')</a>
        </div>
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
    var planContextMenuMouse = false;
    var planContextMenuOpened = false;
    var planToolbarPart = false;
    var planRootPenWidth2 = false;

    $(document).ready(() => {
        $('#planToolbar').hide();
        
        window.addEventListener('mousedown', function (e) {
            planContextMenuOpened = ($('#planPartMenu').css('display') != 'none');
            
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
            if (planContextMenuOpened) return ;
            dialog('{{ route("admin.plan-edit", ["id" => ""]) }}/' + $(this).attr('data-id'));
        }).on('contextmenu', function (e) {
            planShowContextMenu(e, 'part');
            return false;
        }).on('mouseover', function () {
            $('#planParts a.tree-item[data-id="' + $(this).data('id') + '"]').addClass('hover'); 
        }).on('mouseleave', function () {
            $('#planParts a.tree-item[data-id="' + $(this).data('id') + '"]').removeClass('hover');
        });
        
        $('#planContent .plan-port').on('click', function (e) {
            if (planMouseScroll) return ;
            if (planContextMenuOpened) return ;
            dialog('{{ route("admin.plan-port-edit", ["planID" => "", "portIndex" => ""]) }}/' + $(this).data('part-id') + '/' + $(this).data('index'));
        }).on('contextmenu', function (e) {
            planShowContextMenu(e, 'port');
            return false;
        });
        
        $('#planContent .plan-device').on('click', function (e) {
            if (planMouseScroll) return ;
            if (planContextMenuOpened) return ;
            dialog('{{ route("admin.plan-link-device", ["planID" => "", "deviceID" => ""]) }}/' + $(this).data('part-id') + '/' + $(this).data('id'));
        }).on('contextmenu', function (e) {
            planShowContextMenu(e, 'device');
            return false;
        });
        @endif

        planZoom = getCookie('planZoom');
        if (planZoom == undefined) {
            planZoom = 50;
        }

        $(window).on('resize', function() {
            planResize();
        }).trigger('resize');
        
        $('#planContentScroll').on('wheel', function (e) {
            e.preventDefault();
            $('#planPartMenu').hide();
            if (e.originalEvent.deltaY < 0) {
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
        }).on('scroll', function () {
            let pos = $('#planContent').position();
            $(this).css({
                'background-position-x': pos.left + planMinX + planRootPenWidth2 - $(this).scrollLeft() + 'px',
                'background-position-y': pos.top + planMinY + planRootPenWidth2 - $(this).scrollTop() + 'px',                
            });
        });
        
        $('#planParts a.tree-item').on('mouseover', function () {
            $('#planContent .plan-part[data-id="' + $(this).data('id') + '"]').addClass('hover');
        }).on('mouseleave', function () {
            $('#planContent .plan-part[data-id="' + $(this).data('id') + '"]').removeClass('hover');
        });
        
        $('#planZoomInBtn').on('click', function () {
            planZoomIn(2);
        });
        
        $('#planZoomOutBtn').on('click', function () {
            planZoomOut(2);
        });
        
        // Compact Navigate
        $('#planPartsCombobox').on('change', function () {
            window.location.href = '{{ route("admin.plan", ["id" => ""]) }}/' + $(this).val();
        });
    });
    
    function planShowContextMenu(e, typ) {
        $('#planPartMenu > div').hide();
        
        switch (typ) {
            case 'part':
                $('#planPartMenu > .plan-part-context').show();
                break;
            case 'port':
                $('#planPartMenu > .plan-port-context').show();
                break;
            case 'device':
                $('#planPartMenu > .plan-device-context').show();
                break;
        }
        
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
        
        let part = $(e.currentTarget);
        let offset = part.offset();
        planContextMenuID = part.data('id');
        
        planContextMenuMouse = {
            x: e.pageX - offset.left,
            y: e.pageY - offset.top,
        };
    }

    function planResize() {
        let penWidth2Parts = new Array(); /* Нужен кеш с вычислениями бордеров, что бы правильно позиционировать устройства */
        
        let minX = 99999999;
        let minY = 99999999;
        let maxX = -99999999;
        let maxY = -99999999;

        $('#planContent .plan-part').css({
            'transition-duration': '0s',
        });
        
        planRootPenWidth2 = false;

        /* Setting the display of rooms */
        $('#planContent .plan-part').each(function() {
            let x = $(this).data('x');
            let y = $(this).data('y');
            let w = $(this).data('w');
            let h = $(this).data('h');
            let pw = parseInt($(this).data('pen-width'));
            let penWidth = pw ? pw : 1;
            penWidth = penWidth * planZoom / planPenZoomScale;
            if (penWidth < planMinPenWidth) penWidth = planMinPenWidth;

            let penWidth2 = Math.ceil(penWidth / 2);
            penWidth = penWidth2 + penWidth2;
            
            if (planRootPenWidth2 === false) {
                planRootPenWidth2 = penWidth2;
            }
            
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
            
            let bg_z = planZoom * 2.5 / Math.sqrt(planZoom);
            let f_size = planZoom * 2 / Math.sqrt(planZoom);

            $(this).css({
                left: x + 'px',
                top: y + 'px',
                width: w + 'px',
                height: h + 'px',
                'border-width': penWidth + 'px',
                'background-size': bg_z + 'px',
                'font-size': f_size + 'px',
            });
            
            let span = $('span', this);
            let w2 = 0;
            let h2 = 0;
            if ($(this).data('pen-style') !== 'none') {
                w2 = (w - penWidth - penWidth) / 2;
                h2 = (h - penWidth - penWidth) / 2;
            } else {
                w2 = w / 2;
                h2 = h / 2;
            }
            span.css({
                left: w2 + w2 * $(this).data('name-dx') / 100 - span.width() / 2 + 'px',
                top: h2 + h2 * $(this).data('name-dy') / 100 - span.height() / 2 + 'px',
            });
        });
        
        $('#planContent .plan-port').each(function () {
            let partId = $(this).data('part-id');
            let position = $(this).data('position');
            let partBounds = $(this).data('partBounds');            
            
            let partX = partBounds.X * planZoom;
            let partY = partBounds.Y * planZoom;
            let partW = partBounds.W * planZoom;
            let partH = partBounds.H * planZoom;
            
            let pw = 0;
            let ph = 0;
            let pw2 = 1;
            
            for (let i = 0; i < penWidth2Parts.length; i++) {
                if (penWidth2Parts[i].id == partId) {
                    pw2 = penWidth2Parts[i].width;
                    break;
                }
            }
            
            switch (position.surface) {
                case 'top':
                    pw = position.width * planZoom + pw2 + pw2;
                    ph = position.depth * planZoom + pw2 + pw2;
                    $(this).css({
                        'border-width': (pw2 + pw2) + 'px',
                        left: (partX + position.offset * planZoom - pw2) + 'px',
                        top: (partY - ph + pw2) + 'px',
                        width: pw + 'px',
                        height: ph + 'px',
                    });
                    break;
                case 'right':
                    pw = position.depth * planZoom + pw2 + pw2;
                    ph = position.width * planZoom + pw2 + pw2;
                    $(this).css({
                        'border-width': (pw2 + pw2) + 'px',
                        left: (partX + partW - pw2) + 'px',
                        top: (partY + position.offset * planZoom - pw2) + 'px',
                        width: pw + 'px',
                        height: ph + 'px',
                    });
                    break;
                case 'bottom':
                    pw = position.width * planZoom + pw2 + pw2;
                    ph = position.depth * planZoom + pw2 + pw2;
                    $(this).css({
                        'border-width': (pw2 + pw2) + 'px',
                        left: (partX + partW - position.offset * planZoom - pw - pw2) + 'px',
                        top: (partY + partH - pw2) + 'px',
                        width: pw + 'px',
                        height: ph + 'px',
                    });
                    break;
                case 'left':
                    pw = position.depth * planZoom + pw2 + pw2;
                    ph = position.width * planZoom + pw2 + pw2;
                    $(this).css({
                        'border-width': (pw2 + pw2) + 'px',
                        left: (partX - pw + pw2) + 'px',
                        top: (partY + partH - ph - position.offset * planZoom + pw2) + 'px',
                        width: pw + 'px',
                        height: ph + 'px',
                    });
                    break;
            }
        });
        
        /* Setting the display of devices */
        let devicePenWidth = planZoom / planPenZoomScale;
        if (devicePenWidth < planMinPenWidth) devicePenWidth = planMinPenWidth;
        let devicePenWidth2 = Math.ceil(devicePenWidth / 2);
        devicePenWidth = devicePenWidth2 + devicePenWidth2;
        let deviceW = 0.20 * planZoom;
        let deviceH = 0.20 * planZoom;
        let deviceR = 0.05 * planZoom;
        
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
            
            let partX = partBounds.X * planZoom + partPenWidth2;
            let partY = partBounds.Y * planZoom + partPenWidth2;
            let partW = partBounds.W * planZoom - partPenWidth;
            let partH = partBounds.H * planZoom - partPenWidth;
            
            let kx = (partW - deviceW) / partBounds.W;
            let ky = (partH - deviceH) / partBounds.H;
            
            switch (position.surface) {
                case 'top':
                    $(this).css({
                        'border-width': devicePenWidth + 'px',
                        'border-radius': deviceR + 'px',
                        left: (partX + position.offset * kx) + 'px',
                        top: (partY) + 'px',
                        width: deviceW + 'px',
                        height: deviceH + 'px',
                    });
                    break;
                case 'right':
                    $(this).css({
                        'border-width': devicePenWidth + 'px',
                        'border-radius': deviceR + 'px',
                        left: (partX + partW - deviceW) + 'px',
                        top: (partY + position.offset * ky) + 'px',
                        width: deviceW + 'px',
                        height: deviceH + 'px',
                    });
                    break;
                case 'bottom':
                    $(this).css({
                        'border-width': devicePenWidth + 'px',
                        'border-radius': deviceR + 'px',
                        left: (partX + partW - position.offset * kx - deviceW) + 'px',
                        top: (partY + partH - deviceH) + 'px',
                        width: deviceW + 'px',
                        height: deviceH + 'px',
                    });
                    break;
                case 'left':
                    $(this).css({
                        'border-width': devicePenWidth + 'px',
                        'border-radius': deviceR + 'px',
                        left: (partX) + 'px',
                        top: (partY + partH - deviceH - position.offset * ky) + 'px',
                        width: deviceW + 'px',
                        height: deviceH + 'px',
                    });
                    break;
                case 'roof':
                    $(this).css({
                        'border-width': devicePenWidth + 'px',
                        'border-radius': deviceR + 'px',
                        left: (partX + position.offset * kx) + 'px',
                        top: (partY + position.cross * ky) + 'px',
                        width: deviceW + 'px',
                        height: deviceH + 'px',
                    });
                    break;
            }
        });
        
        /* Setting a view port */
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
        
        $('#planContentScroll').css({
            'background-size': planZoom + 'px',
            'background-position-x': nx + planMinX + planRootPenWidth2 - $('#planContentScroll').scrollLeft() + 'px',
            'background-position-y': ny + planMinY + planRootPenWidth2 - $('#planContentScroll').scrollTop() + 'px',
        });
    }

    function planZoomIn(step) {
        let z = planZoom * (step ? step : planZoomStep);
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

    function planZoomOut(step) {
        let z = planZoom / (step ? step : planZoomStep);
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
        dialog('{{ route("admin.plan-edit", ["id" => -1, "p_id" => $partID]) }}');
    }

    @if($partID)
    function planEdit() {
        dialog('{{ route("admin.plan-edit", ["id" => $partID]) }}');
    }

    function planMoveChilds() {
        dialog('{{ route("admin.plan-move-childs", ["id" => $partID]) }}');
    }

    function planOrder() {
        dialog('{{ route("admin.plan-order", ["id" => $partID]) }}');
    }
    @endif
    
    function planImport() {
        dialog('{{ route("admin.plan-import") }}');
    }
    
    @if($partID)
    function planMenuPlanEdit() {
        dialog('{{ route("admin.plan-edit", ["id" => ""]) }}/' + planContextMenuID);
    }

    function planSelInTree() {
        window.location.href = '{{ route("admin.plan", ["id" => ""]) }}/' + planContextMenuID;
    }

    function planMenuAddPart() {
        dialog('{{ route("admin.plan-edit", ["id" => -1, "p_id" => ""]) }}/' + planContextMenuID);
    }
        
    function planMenuAddDevice() {
        let part = $('#planContent .plan-part[data-id="' + planContextMenuID + '"]');
        
        let w = part.width();
        let h = part.height();
        
        let b = Math.min(w, h) / 4;
        
        let surface = 'top';
        let offset = 0;
        let cross = 0;
        
        let x1 = b;
        let x2 = w - b;
        let y1 = b;
        let y2 = h - b;
        
        if (planContextMenuMouse.y < y1) {
            surface = 'top';
            if (planContextMenuMouse.y > planContextMenuMouse.x) {
                surface = 'left';
            } else
            if (planContextMenuMouse.y > w - planContextMenuMouse.x) {
                surface = 'right';
            }
        } else
        if (planContextMenuMouse.x > x2) {
            surface = 'right';
            if (w - planContextMenuMouse.x > planContextMenuMouse.y) {
                surface = 'top';
            } else
            if (w - planContextMenuMouse.x > h - planContextMenuMouse.y) {
                surface = 'bottom';
            }
        } else
        if (planContextMenuMouse.y > y2) {
            surface = 'bottom';
            if (h - planContextMenuMouse.y > planContextMenuMouse.x) {
                surface = 'left';
            } else
            if (h - planContextMenuMouse.y > w - planContextMenuMouse.x) {
                surface = 'right';
            }
        } else
        if (planContextMenuMouse.x < x1) {
            surface = 'left';
            if (planContextMenuMouse.x > planContextMenuMouse.y) {
                surface = 'top';
            } else
            if (planContextMenuMouse.x > h - planContextMenuMouse.y) {
                surface = 'bottom';
            }
        } else {
            surface = 'roof';
        }
        
        let m_x = planContextMenuMouse.x;
        let m_y = planContextMenuMouse.y;
        
        switch (surface) {
            case 'top':
                offset = Math.round(part.data('w') * m_x / w * 10) / 10;
                break;
            case 'right':
                offset = Math.round(part.data('h') * m_y / h * 10) / 10;
                break;
            case 'bottom':
                offset = Math.round((part.data('w') - part.data('w') * m_x / w) * 10) / 10;
                break;
            case 'left':
                offset = Math.round((part.data('h') - part.data('h') * m_y / h) * 10) / 10;
                break;
            default:
                offset = Math.round(part.data('w') * m_x / w * 10) / 10;
                cross = Math.round(part.data('h') * m_y / h * 10) / 10;
                break;
        }
        
        dialog('{{ route("admin.plan-link-device", ["planID" => "", "deviceID" => ""]) }}/' + planContextMenuID + '/-1?surface=' + surface + '&offset=' + offset + '&cross=' + cross);
    }
    
    function planMenuClonePart(direction) {
        $.ajax({
            url: '{{ route("admin.plan-clone", ["id" => "", "direction" => ""]) }}/' + planContextMenuID + '/' + direction,
            success: function (data) {
                if (data == 'OK') {
                    window.location.reload();
                } else {
                    
                }
            },
        });
    }
    
    function planMenuToolbar(operation) {
        planToolbarCancel(true);
        
        planToolbarPart = $('#planContent .plan-part[data-id="' + planContextMenuID + '"]');
        
        $('#toolbarPartName').text($('#planParts .tree-item[data-id="' + planContextMenuID + '"]').text());
        
        switch (operation) {
            case 'move':
                $('#toolbarOperation').text('@lang("admin/plan.toolbar_move")');
                $('#toolbarLabel1').text('@lang("admin/plan.toolbar_move_x"):');
                $('#toolbarLabel2').text('@lang("admin/plan.toolbar_move_y"):');
                
                let parent = $('#planContent .plan-part[data-id="' + planToolbarPart.data('parent-id') + '"]');
                let parent_x = 0;
                let parent_y = 0;
                if (parent.length) {
                    parent_x = Math.ceil(parent.data('x') * 100) / 100;
                    parent_y = Math.ceil(parent.data('y') * 100) / 100;
                }
                
                $('#toolbarValue1')
                    .val(Math.ceil((planToolbarPart.data('x') - parent_x) * 100) / 100)
                    .data('old', planToolbarPart.data('x'))
                    .data('parent', parent_x);
                $('#toolbarValue2')
                    .val(Math.ceil((planToolbarPart.data('y') - parent_y) * 100) / 100)
                    .data('old', planToolbarPart.data('y'))
                    .data('parent', parent_y);
                break;
            case 'size':
                $('#toolbarOperation').text('@lang("admin/plan.toolbar_size")');
                $('#toolbarLabel1').text('@lang("admin/plan.toolbar_size_w"):');
                $('#toolbarLabel2').text('@lang("admin/plan.toolbar_size_h"):');
                
                $('#toolbarValue1')
                    .val(planToolbarPart.data('w'))
                    .data('old', planToolbarPart.data('w'));
                $('#toolbarValue2')
                    .val(planToolbarPart.data('h'))
                    .data('old', planToolbarPart.data('h'));
                break;
        }
        
        $('#planToolbar')
            .data('operation', operation)
            .fadeIn(250);
    }
    
    function planToolbarValue1(event) {
        switch ($('#planToolbar').data('operation')) {
            case 'move':
                let newX = parseFloat($('#toolbarValue1').val()) + parseFloat($('#toolbarValue1').data('parent'));
                planToolbarPart.data('x', newX);
                planResize();
                break;
            case 'size':
                planToolbarPart.data('w', $(event.target).val());
                planResize();
                break;
        }
    }
    
    function planToolbarValue2(event) {
        switch ($('#planToolbar').data('operation')) {
            case 'move':
                let newY = parseFloat($('#toolbarValue2').val()) + parseFloat($('#toolbarValue2').data('parent'));
                planToolbarPart.data('y', newY);
                planResize();
                break;
            case 'size':
                planToolbarPart.data('h', $(event.target).val());
                planResize();
                break;
        }
    }
    
    function planToolbarOk() {
        let id = planToolbarPart.data('id');
        switch ($('#planToolbar').data('operation')) {
            case 'move':
                let newX = parseFloat($('#toolbarValue1').val()) + parseFloat($('#toolbarValue1').data('parent'));
                let newY = parseFloat($('#toolbarValue2').val()) + parseFloat($('#toolbarValue2').data('parent'));
                
                $.post({
                    url: '{{ route("admin.plan-move", ["id" => "", "newX" => "", "newY" => ""]) }}/' + id + '/' + newX + '/' + newY,
                    data: {
                        
                    },
                    success: function (data) {
                        if (data == 'OK') {
                            $('#planToolbar').fadeOut(250);
                            planToolbarPart = false;
                        } else {
                            alert(data);
                        }
                    },
                });
                break;
            case 'size':
                let newW = $('#toolbarValue1').val();
                let newH = $('#toolbarValue2').val();
                $.post({
                    url: '{{ route("admin.plan-size", ["id" => "", "newW" => "", "newH" => ""]) }}/' + id + '/' + newW + '/' + newH,
                    data: {
                        
                    },
                    success: function (data) {
                        if (data == 'OK') {
                            $('#planToolbar').fadeOut(250);
                            planToolbarPart = false;
                        }
                    },
                });
                break;
        }
    }
    
    function planToolbarCancel(fast) {
        if (planToolbarPart) {
            switch ($('#planToolbar').data('operation')) {
                case 'move':
                    planToolbarPart.data('x', $('#toolbarValue1').data('old'));
                    planToolbarPart.data('y', $('#toolbarValue2').data('old'));
                    break;
                case 'size':
                    planToolbarPart.data('w', $('#toolbarValue1').data('old'));
                    planToolbarPart.data('h', $('#toolbarValue2').data('old'));
                    break;
            }
        }
        planResize();
        planToolbarPart = false;
        
        $('#planToolbar').fadeOut(fast ? 0 : 250);
    }
    
    function planMenuDeviceLink() {
        let device = $('#planContentOff .plan-device[data-id="' + planContextMenuID + '"]');
        dialog('{{ route("admin.plan-link-device", ["planID" => "", "deviceID" => ""]) }}/' + device.data('part-id') + '/' + device.data('id'));
    }
    
    function planMenuDeviceEdit() {       
        dialog('{{ route("admin.hub-device-edit", ["hubID" => -1, "id" => ""]) }}/' + planContextMenuID);
    }
    
    function planMenuAddPort() {
        let part = $('#planContent .plan-part[data-id="' + planContextMenuID + '"]');
        
        let w = part.width();
        let h = part.height();
        
        let b = Math.min(w, h) / 4;
        
        let surface = 'top';
        let offset = 0;
        
        let x1 = b;
        let x2 = w - b;
        let y1 = b;
        let y2 = h - b;
        
        if (planContextMenuMouse.y < y1) {
            surface = 'top';
            if (planContextMenuMouse.y > planContextMenuMouse.x) {
                surface = 'left';
            } else
            if (planContextMenuMouse.y > w - planContextMenuMouse.x) {
                surface = 'right';
            }
        } else
        if (planContextMenuMouse.x > x2) {
            surface = 'right';
            if (w - planContextMenuMouse.x > planContextMenuMouse.y) {
                surface = 'top';
            } else
            if (w - planContextMenuMouse.x > h - planContextMenuMouse.y) {
                surface = 'bottom';
            }
        } else
        if (planContextMenuMouse.y > y2) {
            surface = 'bottom';
            if (h - planContextMenuMouse.y > planContextMenuMouse.x) {
                surface = 'left';
            } else
            if (h - planContextMenuMouse.y > w - planContextMenuMouse.x) {
                surface = 'right';
            }
        } else
        if (planContextMenuMouse.x < x1) {
            surface = 'left';
            if (planContextMenuMouse.x > planContextMenuMouse.y) {
                surface = 'top';
            } else
            if (planContextMenuMouse.x > h - planContextMenuMouse.y) {
                surface = 'bottom';
            }
        } 
        
        switch (surface) {
            case 'top':
                offset = Math.round(part.data('w') * planContextMenuMouse.x / w * 10) / 10;
                break;
            case 'right':
                offset = Math.round(part.data('h') * planContextMenuMouse.y / h * 10) / 10;
                break;
            case 'bottom':
                offset = Math.round((part.data('w') - part.data('w') * planContextMenuMouse.x / w) * 10) / 10;
                break;
            case 'left':
                offset = Math.round((part.data('h') - part.data('h') * planContextMenuMouse.y / h) * 10) / 10;
                break;
        }
        
        dialog('{{ route("admin.plan-port-edit", ["planID" => "", "portIndex" => ""]) }}/' + planContextMenuID + '/-1?surface=' + surface + '&offset=' + offset);
    }
    
    function planMenuPortEdit() {
        let port = $('#planContentOff .plan-port[data-id="' + planContextMenuID + '"]');
        dialog('{{ route("admin.plan-port-edit", ["planID" => "", "portIndex" => ""]) }}/' + port.data('part-id') + '/' + port.data('index'));
    }
    
    @endif
</script>
@endsection
