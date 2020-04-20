<?php

function flotRandom($min = 1, $max = 99, $decimals = 4)
{
    $scale = pow(10, $decimals);
    return mt_rand($min * $scale, $max * $scale) / $scale;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="node_modules/bootstrap/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="dist/css/jackpot.min.css">
    <script src="https://www.gstatic.com/firebasejs/7.13.2/firebase-app.js"></script>
    <script src="https://www.gstatic.com/firebasejs/7.13.2/firebase-database.js"></script>
</head>
<body>
    <div class="container-fluid">
        <div class="row mb-5">
            <div class="col-12">
                <h1 class="text-white text-center">
                    Jackpot
                </h1>
                <div class="text-center">
                    <button id="addPLayer" disabled class="btn btn-success">Add Player</button>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12 col-md-7">
                <div id="jackpot-roulette">Loading...</div>
            </div>
            <div class="col-12 col-md-5">
                <div id="jackpot-round"></div>
            </div>
        </div>
    </div>
    <script src="node_modules/jquery/dist/jquery.min.js"></script>
    <script src="node_modules/popper.js/dist/umd/popper.min.js"></script>
    <script src="node_modules/bootstrap/dist/js/bootstrap.min.js"></script>
    <script src="dist/js/jackpot.min.js"></script>
    <script>

        var jackpot = new JackPot({
            roulette:{
                height: 592,
                width: 592,
            },
            firebase: {
                apiKey: "AIzaSyDDeITie7nZPcvOsyxEVRwJsR3m5A5Aayw",
                authDomain: "jackpot-fe203.firebaseapp.com",
                databaseURL: "https://jackpot-fe203.firebaseio.com",
                projectId: "jackpot-fe203",
                storageBucket: "jackpot-fe203.appspot.com",
                messagingSenderId: "336016118000",
                appId: "1:336016118000:web:3059118160a3df3c36b235"
            },
            onGameStart: function(scope){
                $("#addPLayer").prop("disabled", false);
            },
            onGameEnd: function(scope){
                $("#addPLayer").prop("disabled", true);
            },
        });

        jackpot.init();

        $("#addPLayer").on("click", function(){
            var ajax = new Promise(function(resolve, reject){
                $.post("server/jackpot/addPlayer",{
                    player: {
                        rid: jackpot.id,
                        uid: 1992,
                        bet: "random"
                    }
                },function(response){
                    if(!response.error){
                        resolve(response.data);
                    }
                });
            });
        });
        
    </script>
</body>
</html>