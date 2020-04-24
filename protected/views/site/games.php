<?php

function flotRandom($min = 1, $max = 99, $decimals = 4) {
    $scale = pow(10, $decimals);
    return mt_rand($min * $scale, $max * $scale) / $scale;
}

$admins = ['renziito', 'khaosgg'];
?>
<style>
    .col-height {
        height: 100vh;
        display: block;
    }
    .embed-container {
        position: relative;
        padding-bottom: 56.25%;
        height: 0;
        overflow: hidden;
    }
    .embed-container-large {
        position: relative;
        padding-bottom: 150%;
        height: 0;
        overflow: hidden;
    }
    .embed-container-large iframe {
        position: absolute;
        top:0;
        left: 0;
        width: 100%;
        height: 100%;
    }
    .embed-container iframe {
        position: absolute;
        top:0;
        left: 0;
        width: 100%;
        height: 100%;
    }

    .mi-iframe {
        width: 100px;
        height: 50px;
    }

    @media (min-width: 320px) {
        .mi-iframe {
            width: 200px;
            height: 150px;
        } 
    }

    @media (min-width: 768px) {
        .mi-iframe {
            width: 500px;
            height: 350px;
        } 

    }
</style>
<div class="row">
    <div class="col-md-9">
        <div class="row">
            <div class="container-fluid">
                <div class="row mb-5">
                    <div class="col-12">
                        <div class="text-white text-center">
                            <span class="text-white float-left"><?= $data['display_name'] ?>
                                <b id="pointsShow"><?= (isset($data['points']) ? number_format($data['points'] / 1000, 3, '.', ',') : 0.00) ?></b>
                                <b id="points" class="d-none"><?= (isset($data['points']) ? $data['points'] : 0) ?></b>
                            </span>
                            <!--<b>KhaosGG</b>-->
                            <span class="float-right">
                                <a class="btn btn-xs btn-primary" href="<?= Yii::app()->createUrl('logout') ?>">
                                    Log Out <i class="fas fa-sign-out-alt"></i>
                                </a>
                                <?php if (in_array($data['login'], $admins)): ?>
                                    <a class="btn btn-xs btn-success" href="<?= $this->createUrl('admin') ?>">
                                        Admin <i class="fas fa-key"></i>
                                    </a>
                                <?php endif; ?>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="row mb-5">
                    <div class="col-12">
                        <h1 class="text-white text-center">
                            &nbsp;
                        </h1>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12 col-md-6">
                        <div id="jackpot-roulette"></div>
                        <div class="clearfix"></div>
                        <div class="row">
                            <div class="col-12 text-center">
                                <input type="hidden" id="name" value="<?= $data['display_name'] ?>"/>
                                <input type="number" id="beatAmount" value="1"  class="form-control-sm" step=".1"/>
                                <button class="btn btn-sm btn-danger" id="addBeat">Make a Bet</button>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div id="jackpot-round"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="embed-container">
            <iframe scrolling="no" class="mi-iframe" frameborder="0" src="https://player.twitch.tv?channel=khaosgg"></iframe>
        </div>
        <div class="embed-container-large" style="margin-top: 10px;">
            <iframe scrolling="no" class="mi-iframe" frameborder="0" src="https://www.twitch.tv/embed/khaosgg/chat?darkpopout"></iframe>
        </div>
    </div>
</div>

<script src="<?= Yii::app()->getBaseUrl(true) ?>/protected/extensions/jackpot/dist/js/jackpot.min.js"></script>
<script>
    var jackpot = new JackPot({
        roulette: {
            height: 592,
            width: 592,
        },
        spinner: {
            img: '<?= Yii::app()->homeUrl ?>protected/extensions/jackpot/dist/arrow.png',
            secondsReset: 3
        },
        firebase: {
            apiKey: "AIzaSyApd6cxR14IUKR93jwnbLy68zPzLUL45qU",
            authDomain: "royaltygames-5d879.firebaseapp.com",
            databaseURL: "https://royaltygames-5d879.firebaseio.com",
            projectId: "royaltygames-5d879",
            storageBucket: "royaltygames-5d879.appspot.com",
            messagingSenderId: "311098795508",
            appId: "1:311098795508:web:353bd1af82d2d94807c5a1",
            measurementId: "G-EXPVPVSTYP"
        },
        onGameStart: function (scope) {
            $.get("<?= $this->createUrl('getPoints') ?>", {}, function (data) {
                if (data) {
                    $('#points').html(data.points);
                    changeValue(data.points);
                } else {
                    location.href = "<?= Yii::app()->createUrl('login') ?>";
                }
            }, 'json');
            $("#addBeat").prop("disabled", false);
        },
        onGameEnd: function (scope) {
            $.get("<?= $this->createUrl('getPoints') ?>", {}, function (data) {
                if (data) {
                    $('#points').html(data.points);
                    changeValue(data.points);
                } else {
                    location.href = "<?= Yii::app()->createUrl('login') ?>";
                }
            }, 'json');
            $("#addBeat").prop("disabled", true);
        }
    });

    jackpot.init();

    $("#addBeat").on('click', function () {
        var points = $('#points').html();
        var amount = $("#beatAmount").val();
        var name = $("#name").val();
        if (amount > 0 || amount != "") {
            var am = (parseFloat(amount) * 1000);

            if (am <= points) {
                $('#points').html(points - am);
                changeValue(points - am);
                var ajax = new Promise(function (resolve, reject) {
                    $.post("<?= $this->createUrl('addPlayer') ?>", {
                        player: {
                            rid: jackpot.id,
                            uid: <?= Yii::app()->request->cookies['uuid'] ?>,
                            name: '<?= Yii::app()->request->cookies['name'] ?>',
                            bet: amount
                        }
                    }, function (response) {
                        if (!response.error) {
                            resolve(response.data);
                        }
                    }, 'json');
                });
                $("#addBeat").prop('disabled', true);
            } else {
                alert("No se puede apostar esa cantidad");
            }
        }
    });

    function changeValue(number) {
        number = (number / 1000);
        var decimals = 3;
        var dec_point = '.';
        var thousands_sep = ',';

        number = number.toFixed(decimals);

        var nstr = number.toString();
        nstr += '';
        var x = nstr.split('.');
        var x1 = x[0];
        var x2 = x.length > 1 ? dec_point + x[1] : '';
        var rgx = /(\d+)(\d{3})/;

        while (rgx.test(x1))
            x1 = x1.replace(rgx, '$1' + thousands_sep + '$2');

        $('#pointsShow').html(x1 + x2);
    }

</script>