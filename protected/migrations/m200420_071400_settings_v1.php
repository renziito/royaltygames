<?php

class m200420_071400_settings_v1 extends CDbMigration {

    public function safeUp() {
        $this->createTable('settings_admin', array(
            'id' => 'pk auto_increment',
            'token' => 'text',
            'created_at' => 'datetime default now()',
            'state' => 'boolean default TRUE',
        ));
    }

    public function safeDown() {
        $this->dropTable('settings_admin');
    }

}
