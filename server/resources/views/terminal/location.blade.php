<div id="locationViewer" class="location-view">
    <div class="alert alert-light location-title">@lang('terminal.location_title')</div>
    <div class="location-data">
        <div id="latitude" class="location-data-value">-//-</div>
        <div id="longitude" class="location-data-value"></div>
    </div>
</div>
<script>
    $(document).ready(() => {
        $(window).on('resize', function () {
            let locationViewer = $('#locationViewer');
            let parent = locationViewer.parent();
            if ($(this).width() > 992) {
                /* We are looking for the smallest group and attach a location to it */
                let col = false;
                let maxH = 0;
                $('#roomsDiv .col .main-column').each(function () {
                    maxH = Math.max(maxH, $(this).height());
                    
                    if (col === false) {
                        col = $(this);
                    } else
                    if ($(this).height() < col.height()){
                        col = $(this);
                    }
                });
                
                if (col) {                    
                    locationViewer.insertAfter(col);
                    let p_h = col.parent().height();
                    locationViewer.css('padding-bottom', 'calc(0.75rem + ' + (p_h - maxH) + 'px)');
                }                
            } else {
                if (parent.attr('id') !== 'roomsDiv') {
                    $('#locationViewer').appendTo($('#roomsDiv')).css('padding-bottom', '0px');
                }
            }
        }).trigger('resize');
        
        geolocation();
    });
    
    function geolocation() {
        navigator.geolocation.getCurrentPosition(function (pos) {
            $('#latitude').text(pos.coords.latitude);
            $('#longitude').text(pos.coords.longitude);
        });
    }
</script>