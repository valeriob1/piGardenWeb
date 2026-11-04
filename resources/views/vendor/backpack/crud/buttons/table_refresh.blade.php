    <div class="text-right mb-2">
        <a class="btn btn-primary btn-table-refresh" href="#" data-state="stop" onclick="followLogChangeState(this)">
            <i class="fa fa-play"></i> <span class="button-zone-text follow-play">{{__('Follow Log')}}</span>
            <span class="button-zone-text follow-stop d-none">{{__('Stop Log')}}</span>
        </a>
    </div>

    <script>
        if (typeof followLogChangeState != 'function') {
            function followLogChangeState(button) {
                if($(button).data('state') == 'stop') {
                    $(button).data('state', 'play');
                    $(button).find('i').removeClass('fa-play').addClass('fa-stop');
                    $(button).find('.follow-play').addClass('d-none');
                    $(button).find('.follow-stop').removeClass('d-none');
                } else {
                    $(button).data('state', 'stop');
                    $(button).find('i').removeClass('fa-stop').addClass('fa-play');
                    $(button).find('.follow-stop').addClass('d-none');
                    $(button).find('.follow-play').removeClass('d-none');
                }
            }

            setInterval(function(){
                    if($('.btn-table-refresh').data('state') == 'play')
                        //crud.table.ajax.reload();
                        // newest first: column 1 is datetime_log (see LogCrudController).
                        // It used to sort on column 5, which does not exist.
                        crud.table.order([1,'desc']).page('first').draw();
            },
                10000
            );
        }

    </script>
