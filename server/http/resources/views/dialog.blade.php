<div class="modal-content">
    <div class="modal-header">
        <h5 class="modal-title">@yield('title')</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    <div class="modal-body">@yield('content')</div>
    <div class="modal-footer">@yield('buttons')</div>
</div>

<script>
    function dialogHideErrors() {
        $('#dialog_content .is-invalid').removeClass('is-invalid');
    }
    
    function dialogShowErrors(data) {
        dialogHideErrors();
        for (key in data) {
            let o = data[key];
            let s = o.join('<br>');
            $('#dialog_content input[name="' + key + '"]').addClass('is-invalid').next().html(s);
        }        
        //$('#dialog_content')
    }
</script>
@yield('script')