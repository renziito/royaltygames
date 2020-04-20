<?php
/* @var $this SiteController */

$this->pageTitle = Yii::app()->name;
?>

<h1>
    Welcome to <i><?php echo CHtml::encode(Yii::app()->name); ?> Admin Page</i>
    <a class="btn btn-danger float-right" href="<?= Yii::app()->homeUrl ?>">Go back</a>
</h1>

<hr>
<div class="container">
    <form method="POST">
        <div class="form-group">
            <label for="jwtToken" class="text-white">SE Token</label>
            <small class="text-white-50">
                You can find your JWT token on your account channels page
                ("Show Secrets" to reveal the token): <a target="_blank" href="https://streamelements.com/dashboard/account/channels">
                    here</a>
            </small>
            <textarea class="form-control" id="jwtToken" rows="3" name="jwtToken"></textarea>
            <small class="text-white-50">
                For safety reason this textfield will be always blank
            </small>
        </div>
        <button type="submit" class="btn btn-primary">Submit</button>
    </form>
</div>
