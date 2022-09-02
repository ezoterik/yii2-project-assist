<?php

declare(strict_types=1);

namespace Yii2ProjectAssist\Console;

use PDO;
use Yii;
use yii\db\ActiveRecord;
use yii\db\Connection;
use yii\db\Query;
use yii\helpers\Console;

final class BatchIteratorHelper
{
    public static function processEach(Query $query, callable $processFunction, int $batchSize = 500, bool $progressOutput = true): void
    {
        self::process($query, static function (Connection $unbufferedDb, Query $query, int &$countProcessedItems, int &$countChangedItems) use ($processFunction, $batchSize, $progressOutput) {
            if ($progressOutput) {
                $total = $query->count();
                Console::startProgress(0, $total, 'Processing items: ', false);
            }

            $items = $query->each($batchSize, $unbufferedDb);

            /** @var ActiveRecord|array $item */
            foreach ($items as $item) {
                if ($progressOutput) {
                    Console::updateProgress(++$countProcessedItems, $total);
                }

                $processFunction($item, $countChangedItems);
            }

            if ($progressOutput) {
                Console::endProgress('done (' . $countProcessedItems . ' items).' . PHP_EOL);
            }
        }, $progressOutput);
    }

    public static function processBatch(Query $query, callable $processFunction, int $batchSize = 500, bool $progressOutput = true): void
    {
        self::process($query, static function (Connection $unbufferedDb, Query $query, int &$countProcessedItems, int &$countChangedItems) use ($processFunction, $batchSize, $progressOutput) {
            if ($progressOutput) {
                $total = $query->count();
                Console::startProgress(0, $total, 'Processing items: ', false);
            }

            $packItems = $query->batch($batchSize, $unbufferedDb);

            /** @var ActiveRecord[]|array $items */
            foreach ($packItems as $items) {
                if ($progressOutput) {
                    Console::updateProgress($countProcessedItems += count($items), $total);
                }

                $processFunction($items, $countChangedItems);
            }

            if ($progressOutput) {
                Console::endProgress('done (' . $countProcessedItems . ' items).' . PHP_EOL);
            }
        }, $progressOutput);
    }

    private static function process(Query $query, callable $loopProcessFunction, bool $progressOutput): void
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

        if ($progressOutput && $countChangedItems > 0) {
            Console::output('Changed items: ' . $countChangedItems);
        }
    }
}
