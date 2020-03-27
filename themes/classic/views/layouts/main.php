<?php /* @var $this Controller */ ?>
<!DOCTYPE html>
<html>
    <head>
        <?php $this->renderPartial('//layouts/sections/_header'); ?>
    </head>
    <body class="fixed-header menu-pin menu-behind menu-pin">
        <div class="page-container ">
            <div class="page-content-wrapper ">
                <div class="content">
                    <div class="container-fluid container-fixed-lg sm-p-l-10 sm-p-r-10">
                        <?= $content ?>
                    </div>
                </div>
            </div>

        </div>

        <script src="<?= Yii::app()->getBaseUrl() ?>/script/jackpot.js" type="text/javascript"></script>
        <script>
            $(document).ready(function () {
                var Global = {
                    module: '<?= ($this->module) ? $this->module->id : '' ?>',
                    controller: '<?= $this->id ?>',
                    action: '<?= $this->action->id ?>',
                    absoluteUrl: '<?= Yii::app()->getBaseUrl(true) ?>',
                    baseUrl: '<?= Yii::app()->baseUrl ?>',
                };

                var chart = new Jackpot({
                    chartHeight: 500,
                    url: Global.absoluteUrl
                });
                chart.bidUp({
                    name: 'Renziito',
                    total: 20
                });
                chart.bidUp({
                    name: 'Kaos',
                    total: 15
                });
                chart.bidUp({
                    name: 'Twitch',
                    total: 33
                });
                chart.bidUp({
                    name: 'StreamElements',
                    total: 45
                });

                chart.gameStart();
            });
        </script>
    </body>
</html>