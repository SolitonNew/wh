<div class="modal-content">
    <div class="modal-header">
        <h5 class="modal-title">@yield('title')</h5>
        <button id="btn-dialog-close" type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    <div class="modal-body">
        <div class="alert alert-danger" id="errorAlert" style="display: none;"></div>
        @yield('content')
    </div>
    <div class="modal-footer">@yield('buttons')</div>
</div>

<script>
    function dialogHideErrors() {
        $('#errorAlert').hide();
        $('#dialog_content .is-invalid').removeClass('is-invalid');
    }
    
    function dialogShowErrors(data) {
        dialogHideErrors();
        let t = '';
        for (key in data) {
            let o = data[key];
            let s = o.join('<br>');
            let contr = $('#dialog_content [name="' + key + '"]');
            if (contr.length) {
                contr.addClass('is-invalid').next().html(s);
            } else {
                t += s + '<br>';
            } 
        }
        
        if (t != '') {
            $('#errorAlert').html(t).show();
        }
    }
</script>
@yield('script')