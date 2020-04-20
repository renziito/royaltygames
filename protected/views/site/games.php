<?php

function flotRandom($min = 1, $max = 99, $decimals = 4) {
    $scale = pow(10, $decimals);
    return mt_rand($min * $scale, $max * $scale) / $scale;
}
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
    <div class="col-md-9 col-height">
        <div class="row">
            <div class="container-fluid">
                <div class="row mb-5">
                    <div class="col-12">
                        <div class="text-white text-center">
                            <span class="text-white float-left"><?= $data['display_name'] ?>
                                <b id="points"><?= $data['points'] ?></b>
                            </span>
                            <b>KhaosGG</b>
                            <span class="float-right">
                                <a class="btn btn-xs btn-primary" href="<?= Yii::app()->createUrl('logout') ?>">
                                    Cerrar Sesión <i class="fas fa-sign-out-alt"></i>
                                </a>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="row mb-5">
                    <div class="col-12">
                        <h1 class="text-white text-center">
                            Jackpot
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
    <div class="col-md-3 col-height">
        <div class="embed-container">
            <iframe scrolling="no" class="mi-iframe" frameborder="0" src="https://player.twitch.tv?channel=renziito"></iframe>
        </div>
        <div class="embed-container-large" style="margin-top: 10px;">
            <iframe scrolling="no" class="mi-iframe" frameborder="0" src="https://www.twitch.tv/embed/renziito/chat?darkpopout"></iframe>
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
            img: '<?= Yii::app()->homeUrl ?>protected/extensions/jackpot/dist/arrow.png'
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
</script>