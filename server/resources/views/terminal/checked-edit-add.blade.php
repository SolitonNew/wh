@extends('terminal.checked-edit')

@section('page')
<div style="width: 40rem;">
    <div class="alert alert-warning">
        INFO
    </div>
    
    <div class="alert alert-dark" style="margin-bottom: 1rem;">
        <select id="filter" class="form-control">
            <option value="-1">-- ВСЕ --</option>
            @foreach($appControls as $row)
            <option value="{{ $row->key }}" {{ $row->key == $selKey ? 'selected' : '' }}>{{ $row->value }}</option>>
            @endforeach
        </select>
    </div>
    
    @foreach($data as $row)
    <div class="list-group-item checked-edit-item">
        <div class="checked-edit-item-label">
            {{ $row->typLabel }} {{ $row->COMM }}
        </div>
        <div class="checked-edit-item-edit {{ in_array($row->ID, $checks) ? 'del' : '' }}">
            <a class="btn btn-sm btn-outline-primary checked-edit-item-edit-del" id="del_{{ $row->ID }}" href="#">
                <img src="/img/check-2x.png">
            </a>
            <a class="btn btn-sm btn-outline-primary checked-edit-item-edit-add" id="add_{{ $row->ID }}" href="#">
                <img src="/img/check-2x.png" style="opacity: 0;">
            </a>
        </div>
    </div>
    @endforeach
</div>

<script>
    $('document').ready(() => {
        $('.checked-edit-item-edit-del').on('click', (e) => {
            e.preventDefault();
            let id = $(e.currentTarget).attr('id').substr(4);
            
            $.ajax({
                url: '{{ route("checked-edit-add-del", "") }}/' + id,
            }).done((res) => {
                if (res == 'OK') {
                    $(e.currentTarget).parent().removeClass('del');
                } else {
                    alert(res);
                }
            });
        });
        
        $('.checked-edit-item-edit-add').on('click', (e) => {
            e.preventDefault();
            let id = $(e.currentTarget).attr('id').substr(4);
            
            $.ajax({
                url: '{{ route("checked-edit-add-add", "") }}/' + id,
            }).done((res) => {
                if (res == 'OK') {
                    $(e.currentTarget).parent().addClass('del');
                } else {
                    alert(res);
                }
            });
        });
        
        $('#filter').change((e) => {
            let id = $(e.target).val()
            let url = '{{ route("checked-edit-add", "") }}';
            if (id == -1) {
                //
            } else {
                url += '/' + id;
            }
            window.location = url;
        });
    });
    
    function variableOnChanged(varID, varValue, varTime) {
        
    }
</script>
@endsection