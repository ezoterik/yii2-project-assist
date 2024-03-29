<?php /** @noinspection PhpMissingReturnTypeInspection */
/**
 * This view is used by console/controllers/MigrateController.php.
 *
 * The following variables are available in this view:
 */
/* @var $className string the new migration class name without namespace */
/* @var $namespace string the new migration class namespace */

echo "<?php\n\n";
echo "declare(strict_types=1);\n";

if (!empty($namespace)) {
    echo "\nnamespace {$namespace};\n";
}
?>

use Yii2ProjectAssist\Console\Migration;

final class <?= $className ?> extends Migration
{
    public function safeUp()
    {

    }

    public function safeDown(): bool
    {
        echo "<?= $className ?> cannot be reverted.\n";

        return false;
    }
}
