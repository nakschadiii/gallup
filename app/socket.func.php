<?php

Route::_()->handle('/__server//:page', function (){
    $page = Params::_()->get()['page'];
    echo <<<JS
    function square(receptacle, callback) {
        doc.ready(function(){
            const serv = () => {
                $.get("/__server/${page}", function(data) {
                    if(receptacle != data){
                        const on = (key, callback_) => {
                            k = JSON.parse(receptacle ?? JSON.stringify(null))?.[key];
                            d = JSON.parse(data ?? JSON.stringify(null))?.[key];
                            if(k != d){ callback_(d); }
                        };
                        callback(JSON.parse(data), on);
                        receptacle = data;
                    }
                });
            }
            setTimeout(serv, 0);
            setInterval(serv, 1000);
        });
    }
    JS;
});

?>