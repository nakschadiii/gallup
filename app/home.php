<?php

Route::_()->handle("/", function () {
    print <<<HTML
    <a href="/create">
        <button>créer un questionnaire</button>
    </a>
    HTML;
});

Route::_()->handle("/create", function () {
    $fName = 'base/token.txt';
    $baseTokens = fetchTable($fName);

    if (!empty($_POST['cQ'])) {
        $content = $_POST['cQ'];
        header('Location: /survey/'.setTable($fName, [$_COOKIE['token'], "survey", date("Y-m-d H:i:s"), $content]));
    }

    print <<<HTML
    <a href="/">
        <button>Retour</button>
    </a>

    <form action="" id="create" method="POST">
        <h1>Créer votre questionnaire</h1>
        <input type="text" name="cQ[title]" id="" required placeholder="Titre...">
        <button type="button" onclick="addFragen(this.parentNode.childElementCount, this);">Ajouter une question</button>
    </form>
    <button type="submit" name="cQ[submit]" form="create">Soumettre le sondage</button>

    <script>
        function addFragen(nth, elem) {
            var template = '<div> <label><input type="radio" onclick="displayVote('+nth+', this);" name="cQ[questions]['+nth+'][type]" value="question" required checked> Question</label> <label><input type="radio" onclick="displayVote('+nth+', this);" name="cQ[questions]['+nth+'][type]" value="polling" required placeholder="Entrez une réponse"> Sondage</label> <label><input type="radio" onclick="displayVote('+nth+', this);" name="cQ[questions]['+nth+'][type]" value="range" required> Range</label> <label><textarea name="cQ[questions]['+nth+'][fragen]" id="" placeholder="Question..." required></textarea></label> </div>';
            elem.parentNode.insertAdjacentHTML( 'beforeend', template );
        }

        function addVoteCand(elem) {
            var exists = false;
            document.querySelectorAll('input[type=\'hidden\']').forEach( input => {
                if (input.value == elem.parentNode.querySelector('input').value) { exists = true; }
            });
            if (elem.parentNode.querySelector('input').value !== '' && !exists) {
                var new_answer = document.createElement('input');
                new_answer.setAttribute('name', elem.parentNode.querySelector('input').getAttribute('name').replace('options_', 'polling')+'[]');
                new_answer.setAttribute('type', 'hidden');
                new_answer.value = elem.parentNode.querySelector('input').value;
                new_answer_ = document.createElement('div')
                new_answer_.appendChild(new_answer);
                var span = document.createElement('span'); span.append(elem.parentNode.querySelector('input').value);
                var close = document.createElement('button'); close.setAttribute('type', 'button'); close.setAttribute('onclick', 'this.parentNode.remove();'); close.setAttribute('class', 'close'); close.append('X');
                new_answer_.appendChild(span);
                new_answer_.appendChild(close);
                elem.parentNode.appendChild(new_answer_);
            }
        }

        function displayVote(nth, elem) {
            elem.parentNode.parentNode.querySelectorAll('.vote, .range').forEach(element => {
                element.remove();
            });
            if (elem.value=="polling" && elem.parentNode.parentNode.querySelectorAll('.vote').length == 0) {
                var template = '<div class="vote"> <input type="text" name="cQ[questions]['+nth+'][options_]"> <button type="button" onclick="addVoteCand(this); this.parentNode.querySelector(\'input\').value = \'\';">Add</button> </div>';
                elem.parentNode.parentNode.insertAdjacentHTML( 'beforeend', template );
            }
            if (elem.value=="range" && elem.parentNode.parentNode.querySelectorAll('.range').length == 0) {
                var template = '<div class="range"> <input type="number" name="cQ[questions]['+nth+'][range][min]" placeholder="Min"><input type="number" name="cQ[questions]['+nth+'][range][max]" placeholder="Max"> </div>';
                elem.parentNode.parentNode.insertAdjacentHTML( 'beforeend', template );
            }
            if (elem.value=="question") {
                
            }
        }
    </script>
    HTML;
});

Route::_()->handle("/vote/:id", function () {
    $fName = 'base/token.txt';
    $baseTokens = fetchTable($fName);

    $survey = @fetchRowsByColumnValue(fetchTable('base/token.txt'), "action", "survey")[Params::_()->get()['id']];
    if (!empty($survey)) {
        if (!empty($_POST['answers'])) {
            echo '<pre>'; print_r($_POST['answers']);
            $content = $_POST['answers'];
            setTable($fName, [$_COOKIE['token'], "surveyAnswer_".Params::_()->get()['id'], date("Y-m-d H:i:s"), setTable($fName, [$_COOKIE['token'], "surveyAnswer_".Params::_()->get()['id']."_".$_COOKIE['token'], date("Y-m-d H:i:s"), $content])]);
            header('Location: /survey/'.Params::_()->get()['id']);
        }

        $answered = @fetchRowsByColumnValue($baseTokens, "action", "surveyAnswer_".Params::_()->get()['id']."_".$_COOKIE['token']);
        if (empty($answered)) {
            extract((array)$survey['content']);
            echo $title;
            echo '<br>';

            ?>
            <form action="" method="POST">
                <?php
                foreach ($questions as $key => $value) { $value = (array)$value;
                    $type = $value['type'];
                    echo $value['fragen'];
                    if($type == 'question') { ?> <textarea name="answers[<?= $key; ?>]" required></textarea> <?php }else{
                        $q = (array)$value[$type];
                        if($type == 'polling') { foreach($q as $id_ans => $answers) { ?> <label><input type="radio" name="answers[<?= $key; ?>]" id="" value="<?= $answers; ?>" required> <?= $answers; ?> </label> <?php } }
                        if($type == 'range'){ ?> <input type="range" name="answers[<?= $key; ?>]" id="" min="<?= $q['min'] ?>" max="<?= $q['max'] ?>" required> <?php }
                    }
                }
                ?>
                <input type="submit" value="">
            </form>
            <?php
        }else{
            header("Location: /survey/".Params::_()->get()['id']);
        }
    }else{
        ?><script>location.replace('/');</script><?php
    }
});

Route::_()->handle("/__server/survey", function(){

    $surveysAnsLi = fetchRowsByColumnValue(fetchTable('base/token.txt'), "action", "surveyAnswer_".Params::_()->get()['id']);
    $surveysAnsLi = array_map(function($_) { return (array)fetchTable('base/token.txt')[$_["content"]]['content']; }, (array)$surveysAnsLi);
    //print_r($surveysAnsLi);

    $html = "";
    foreach ((array)((array)$survey['content'])['questions'] as $key => $value) {
        $value = (array)$value;
        $answers2thisQ = array_column($surveysAnsLi, $key);
        $html .= $value['fragen'].' - ';
        if (count($answers2thisQ)>0) {
            if ($value['type'] == 'range') {
                $html .=  "Moyenne :";
                $sum = 0;
                array_map(function ($v) use (&$sum) { $sum += $v; }, $answers2thisQ);
                echo $sum / count($answers2thisQ);
            }
            if ($value['type'] == 'question') {
                ?>Réponses : <ul><?php
                foreach ($answers2thisQ as $li){
                ?>
                    <li><?= $li; ?></li>
                <?php
                }
                ?></ul><?php
            }
            if ($value['type'] == 'polling') {
               ?>Sondage : <ul><?php
                foreach ($value[$value['type']] as $li){
                ?>
                    <li>
                        <?= $li; ?>
                        <?= count(array_filter($answers2thisQ, function($v) use($li){
                                return $li == $v;
                            })) / count($answers2thisQ) * 100;
                        ?>%
                    </li>
                <?php
                }
                ?></ul><?php
            }
        }else{
            echo "Pas de réponse";
        }
    }

});

Route::_()->handle("/survey/:id", function () {
    $fName = 'base/token.txt';
    $baseTokens = fetchTable($fName);

    $survey = @fetchRowsByColumnValue(fetchTable('base/token.txt'), "action", "survey")[Params::_()->get()['id']];
    if (!empty($survey)) {
    ?>

        Le lien à partager pour le vote : <?= $_SERVER['SERVER_NAME']; ?><?= ($_SERVER['SERVER_PORT'] != '80') ? ':'.$_SERVER['SERVER_PORT'] : null ?>/vote/<?= Params::_()->get()['id']; ?>
        <br>

        <script src="/__server//main"></script>
        <script>
            var msgs;
            square(msgs, function(content, on){
                on('__html', function(html){ document.querySelector('body').innerHTML = html; });
            });
        </script>
    
    <?php
    }else{
        ?><script>location.replace('/');</script><?php
    }
});


//$surveys = fetchRowsByColumnValue(fetchTable('base/token.txt'), "action", "survey");
//$surveysLi = array_map(function($_) { return "/survey_".$_; }, array_keys($surveys));
//$votesLi = array_map(function($_) { return "/vote".$_; }, array_keys($surveys));


?>