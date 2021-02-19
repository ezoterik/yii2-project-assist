<?php

namespace Yii2ProjectAssist\Console;

use yii\db\ColumnSchemaBuilder;

/**
 * Важно оставлять этот класс не final т.к. от него наследуются все миграции проекта
 */
class Migration extends \yii\db\Migration
{
    public function createTable($table, $columns, $options = null): void
    {
        if ($options === null) {
            /** @noinspection DegradedSwitchInspection */
            switch ($this->db->driverName) {
                case 'mysql':
                    //http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
                    $options = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
                    break;
            }
        }

        parent::createTable($table, $columns, $options);
    }

    public function boolean(): ColumnSchemaBuilder
    {
        return parent::boolean()->unsigned()->notNull()->defaultValue('0');
    }

    public function unixTimestamp(): ColumnSchemaBuilder
    {
        return $this->integer()->unsigned();
    }

    public function enumInt(): ColumnSchemaBuilder
    {
        return $this->tinyInteger()->unsigned()->notNull();
    }
}
