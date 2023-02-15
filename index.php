<?php

require '../chdLibrary/index.php';

//début print handle (sauf /__server/)
Route::_()->handle("*", function () {
    print <<<HTML
    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <script src="/jquery.js"></script>
        <script>doc = $(document);</script>
        <title>Document</title>
    </head>
    <body>
    HTML;
}, ['/__server']);


//include init
include 'app/scripts/init.php';

//include scripts interface
foreach (
    array_filter(
        array_map(function($v){
            return 'app/'.$v;
        }, scandir('app')),
        function($v){ return is_file($v); }
    )
as $file) {
    include $file;
}

//fin print handle (sauf /__server/)
Route::_()->handle("*", function () {
    print <<<HTML
    </body>
    </html>
    HTML;

    Route::_()->handle('**', function (){
        die(header('Location: /'));
    });
}, ['/__server']);

?>