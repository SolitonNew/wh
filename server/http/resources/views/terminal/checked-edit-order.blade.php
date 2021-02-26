@extends('terminal.checked-edit')

@section('page')
<div style="width: 40rem;">
    <div class="alert alert-warning">
        INFO
    </div>    
    
    <div class="list-group">
        @foreach($data as $row)
        <div class="list-group-item checked-edit-item">
            <div class="checked-edit-item-label">
                {{ $row->control->label }} {{ $row->data->COMM }}
            </div>
            <div class="checked-edit-item-edit" style="white-space: nowrap;">
                <a class="btn btn-sm btn-outline-primary checked-edit-item-order-up"
                    id="up_{{ $row->data->ID }}" href="#"><img src="/img/arrow-thick-top-2x.png"></a>
                <a class="btn btn-sm btn-outline-primary checked-edit-item-order-down"
                    id="down_{{ $row->data->ID }}" href="#"><img src="/img/arrow-thick-bottom-2x.png"></a>
            </div>
        </div>
        @endforeach
    </div>
</div>
    
<script>
    $('document').ready(() => {
        $('.checked-edit-item-order-up').on('click', (e) => {
            e.preventDefault();
            let id = $(e.currentTarget).attr('id').substr(3);
            
            $.ajax({
                url: '{{ route("checked-edit-order-up", "") }}/' + id,
            }).done((res) => {
                if (res == 'OK') {
                    let item = $(e.currentTarget).parent().parent();
                    item.insertBefore(item.prev());
                    recalcDisabledButtons();
                } else {
                    alert(res);
                }
            });
            
            return false;
        });
        
        $('.checked-edit-item-order-down').on('click', (e) => {
            e.preventDefault();
            let id = $(e.currentTarget).attr('id').substr(5);
            
            $.ajax({
                url: '{{ route("checked-edit-order-down", "") }}/' + id,
            }).done((res) => {
                if (res == 'OK') {
                    let item = $(e.currentTarget).parent().parent();
                    item.insertAfter(item.next());
                    recalcDisabledButtons();
                } else {
                    alert(res);
                }                
            });          
            
            return false;
        });
        
        recalcDisabledButtons();
    });
    
    function recalcDisabledButtons() {
        $('.checked-edit-item-order-up.disabled').removeClass('disabled');
        $('.checked-edit-item-order-down.disabled').removeClass('disabled');
        
        let ls = $('.list-group-item');
        if (ls.length > 0) {
             $('.checked-edit-item-order-up', ls[0]).addClass('disabled');
             $('.checked-edit-item-order-down', ls[ls.length - 1]).addClass('disabled');
        }
    }
    
    
    function variableOnChanged(varID, varValue, varTime) {
        
    }
</script>
@endsection