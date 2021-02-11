@extends('terminal.checked-edit')

@section('page')
<div style="width: 40rem;">
    <div class="alert alert-warning">
        INFO
    </div>
    
    <div class="alert alert-dark checked-edit-color-panel">
        <input id="keyword" type="text" class="form-control" value="" style="flex-grow:1;margin-right:1rem;">
        <input id="color" type="text" class="form-control" value="" style="width:10rem;margin-right:1rem;">
        <div>
            <a id="btn_add" href="#" class="btn btn-primary">@lang('terminal.btn_append')</a>
            <a id="btn_set" href="#" class="btn btn-primary" style="margin-left:1rem;">@lang('terminal.btn_update')</a>
        </div>
    </div>

    <div class="list-group checked-edit-color-list">
        @foreach($data as $row)
        <div class="list-group-item" style="display:flex;align-items: center;">
            <div>
                <a class="set_keyword" style="flex-grow: 1;" href="#">{{ $row['keyword'] }}</a>
                <a class="set_color" style="width: 10rem;" href="#">{{ $row['color'] }}</a>
            </div>
            <a class="btn btn-primary btn-sm btn_del" href="#" data="{{ $row['keyword'] }}" >@lang('terminal.btn_delete')</a>
        </div>
        @endforeach
    </div>
</div>

<script>
    $('document').ready(() => {
        $('#btn_add').on('click', (e) => {
            e.preventDefault();
            if ($('#keyword').val()) {
                $.post({
                    method: 'POST',
                    url: '{{ route("checked-edit-color-action", "add") }}',
                    data: {
                        _token: '{{ csrf_token() }}',
                        keyword: $('#keyword').val(),
                        color: $('#color').val(),
                    }
                }).done((res) => {
                    window.location.reload();
                });
            }
        });
        
        $('#btn_set').on('click', (e) => {
            e.preventDefault();
            if ($('#keyword').val()) {
                $.post({
                    method: 'POST',
                    url: '{{ route("checked-edit-color-action", "set") }}',
                    data: {
                        _token: '{{ csrf_token() }}',
                        keyword: $('#keyword').val(),
                        color: $('#color').val(),
                    }
                }).done((res) => {
                    window.location.reload();
                });
            }
        });
        
        $('.btn_del').on('click', (e) => {
            e.preventDefault();
            $.post({
                method: 'POST',
                url: '{{ route("checked-edit-color-action", "del") }}',
                data: {
                    _token: '{{ csrf_token() }}',
                    keyword: $(e.target).attr('data'),
                }
            }).done((res) => {
                window.location.reload();
            });            
        });
        
        $('.set_keyword').on('click', (e) => {
            e.preventDefault();
            $('#keyword').val($(e.target).text());
            $('#color').val($(e.target).next().text());
        });

        $('.set_color').on('click', (e) => {
            e.preventDefault();
            $('#color').val($(e.target).text());
            $('#keyword').val($(e.target).prev().text());
        });        
    });
    
    function variableOnChanged(varID, varValue, varTime) {
        
    }
</script>

@endsection