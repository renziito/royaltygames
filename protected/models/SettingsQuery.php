<?php

class SettingsQuery
{
    public static function getActive()
    {
        return Yii::app()->db->createCommand()
        ->select("id")
        ->from("settings")
        ->where("active = 1 and status = 1")
        ->queryScalar();
    }
}
