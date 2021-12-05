<?php

declare(strict_types=1);

namespace Yii2ProjectAssist\Console;

use yii\db\ColumnSchemaBuilder;

/**
 * Все миграции проекта должны наследоваться от этого класса
 */
abstract class Migration extends \yii\db\Migration
{
    public function createTable($table, $columns, $options = null): void
    {
        if ($options === null) {
            /** @noinspection DegradedSwitchInspection */
            /** @noinspection PhpSwitchStatementWitSingleBranchInspection */
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
