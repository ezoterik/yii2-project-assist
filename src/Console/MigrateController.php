<?php /** @noinspection PhpMissingFieldTypeInspection */

namespace Yii2ProjectAssist\Console;

final class MigrateController extends \yii\console\controllers\MigrateController
{
    public $templateFile = '@vendor/ezoterik/yii2-project-assist/src/Console/views/migration.php';

    public $generatorTemplateFiles = [
        'create_table' => '@vendor/ezoterik/yii2-project-assist/src/Console/views/createTableMigration.php',
        'drop_table' => '@vendor/ezoterik/yii2-project-assist/src/Console/views/dropTableMigration.php',
        'add_column' => '@vendor/ezoterik/yii2-project-assist/src/Console/views/addColumnMigration.php',
        'drop_column' => '@vendor/ezoterik/yii2-project-assist/src/Console/views/dropColumnMigration.php',
        'create_junction' => '@vendor/ezoterik/yii2-project-assist/src/Console/views/createTableMigration.php',
    ];

    public $migrationPath = null;

    public $migrationNamespaces = [
        'console\migrations',
    ];
}
