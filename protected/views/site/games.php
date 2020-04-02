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
                        <span class="text-white"><b>KhaosGG</b></span>
                        <span class="text-white float-right">User <b>10000</b></span>
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
                    </div>
                    <div class="col-12 col-md-6">
                        <div id="jackpot-round"></div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12 col-md-6">
                        <button></button>
                    </div>
                </div>

            </div>
        </div>

        <div class="clearfix"></div>
        <div class=" container-fluid  container-fixed-lg footer">

            <div class="copyright sm-text-center">
                <p class="small no-margin pull-right sm-pull-reset">
                    <span class="hint-text">Copyright &copy; <?= date('Y') ?> </span>
                    <span class="font-montserrat"><?= $_SERVER['HTTP_HOST'] ?></span>.
                    <span class="hint-text">All rights reserved. </span>
                    <span class="hint-text">Made with Love <i class="fas fa-heart text-danger"></i></span>
                </p>
                <div class="clearfix"></div>
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

<script src="<?= Yii::app()->getBaseUrl(true) ?>/protected/extensions/jackpot/dist/js/jackpot.es6.min.js"></script>
<script>
    var jackpot = new Jackpot({
        nextRoundIn: 5,
        gameStartIn: 10,
        url: '<?= Yii::app()->getBaseUrl(true) ?>/protected/extensions/jackpot/',
        roulette: {
            width: 500,
            height: 500
        },
        onNextRound: function (scope) {
            $("#addPLayer").prop("disabled", false);
            var players = Math.floor(Math.random() * 4) + 1;
            var countDown = setInterval(function () {
                players--;
                if (players > 0) {
                    $("#addPLayer").trigger("click");
                } else {
                    clearInterval(countDown);
                }
            }, 2000);
        },
        onGameEnd: function (scope, winner, players) {
            return new Promise(function (resolve, reject) {
                console.log(winner);
                console.log(players);
                console.log(scope.getTotalCurrentRound());
                console.log(scope.roundID);
                resolve();
                /*
                 $.post("server.php", {
                 a: winner,
                 b: players,
                 c: scope.getTotalCurrentRound(),
                 d: scope.roundID
                 }, function (response) {
                 resolve(response);
                 });
                 */
            });
        },
        onClickPlayer: function (scope, player) {
            console.log("player", player);
        },
        onClickWinner: function (scope, winner) {
            console.log("winner", winner);
        }
    });

    jackpot.init();

    $("#addPLayer").on('click', () => {
        jackpot.bidUp({
            name: `Player ${parseInt(Math.random() * 7200)}`,
            total: Math.random() * (10 - 100) + 100
        });
    });
</script>