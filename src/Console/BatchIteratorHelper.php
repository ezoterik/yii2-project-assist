<?php

namespace Yii2ProjectAssist\Console;

use PDO;
use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\Connection;
use yii\helpers\Console;

final class BatchIteratorHelper
{
    public static function processEach(ActiveQuery $query, callable $processFunction): void
    {
        self::process($query, function (Connection $unbufferedDb, ActiveQuery $query, int &$countProcessedItems, int &$countChangedItems) use ($processFunction) {
            $total = $query->count();

            Console::startProgress(0, $total, 'Processing items: ', false);

            /** @var ActiveRecord[] $items */
            $items = $query->each(500, $unbufferedDb);
            foreach ($items as $item) {
                Console::updateProgress(++$countProcessedItems, $total);

                $processFunction($item, $countChangedItems);
            }

            Console::endProgress('done (' . $countProcessedItems . ' items).' . PHP_EOL);
        });
    }

    public static function processBatch(ActiveQuery $query, callable $processFunction): void
    {
        self::process($query, function (Connection $unbufferedDb, ActiveQuery $query, int &$countProcessedItems, int &$countChangedItems) use ($processFunction) {
            $total = $query->count();

            Console::startProgress(0, $total, 'Processing items: ', false);

            /** @var array $packItems */
            $packItems = $query->batch(500, $unbufferedDb);
            foreach ($packItems as $items) {
                Console::updateProgress($countProcessedItems += count($items), $total);

                $processFunction($items, $countChangedItems);
            }

            Console::endProgress('done (' . $countProcessedItems . ' items).' . PHP_EOL);
        });
    }

    private static function process(ActiveQuery $query, callable $loopProcessFunction): void
    {
        $countProcessedItems = 0;
        $countChangedItems = 0;

        $unbufferedDb = new Connection([
            'dsn' => Yii::$app->db->dsn,
            'username' => Yii::$app->db->username,
            'password' => Yii::$app->db->password,
            'charset' => Yii::$app->db->charset,
            'attributes' => Yii::$app->db->attributes,
            'tablePrefix' => Yii::$app->db->tablePrefix,
        ]);
        $unbufferedDb->open();
        $unbufferedDb->pdo->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, false);

        try {
            $loopProcessFunction($unbufferedDb, $query, $countProcessedItems, $countChangedItems);
        } finally {
            $unbufferedDb->close();
        }

        Console::output('Changed items: ' . $countChangedItems);
    }
}
