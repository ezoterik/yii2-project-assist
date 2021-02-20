<?php

namespace Yii2ProjectAssist\Console;

use PDO;
use Yii;
use yii\db\ActiveRecord;
use yii\db\Connection;
use yii\db\Query;
use yii\helpers\Console;

final class BatchIteratorHelper
{
    public static function processEach(Query $query, callable $processFunction): void
    {
        self::process($query, function (Connection $unbufferedDb, Query $query, int &$countProcessedItems, int &$countChangedItems) use ($processFunction) {
            $total = $query->count();

            Console::startProgress(0, $total, 'Processing items: ', false);

            $items = $query->each(500, $unbufferedDb);

            /** @var ActiveRecord|array $item */
            foreach ($items as $item) {
                Console::updateProgress(++$countProcessedItems, $total);

                $processFunction($item, $countChangedItems);
            }

            Console::endProgress('done (' . $countProcessedItems . ' items).' . PHP_EOL);
        });
    }

    public static function processBatch(Query $query, callable $processFunction): void
    {
        self::process($query, function (Connection $unbufferedDb, Query $query, int &$countProcessedItems, int &$countChangedItems) use ($processFunction) {
            $total = $query->count();

            Console::startProgress(0, $total, 'Processing items: ', false);

            $packItems = $query->batch(500, $unbufferedDb);

            /** @var ActiveRecord[]|array $items */
            foreach ($packItems as $items) {
                Console::updateProgress($countProcessedItems += count($items), $total);

                $processFunction($items, $countChangedItems);
            }

            Console::endProgress('done (' . $countProcessedItems . ' items).' . PHP_EOL);
        });
    }

    private static function process(Query $query, callable $loopProcessFunction): void
    {
        $startPeekMemoryUsage = memory_get_peak_usage();

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

        $endPeekMemoryUsage = memory_get_peak_usage();

        if ($countChangedItems > 0) {
            Console::output('Changed items: ' . $countChangedItems);
        }

        Console::output('Peek memory usage: ' . Yii::$app->formatter->asShortSize($endPeekMemoryUsage - $startPeekMemoryUsage));
    }
}
