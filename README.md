Вспомогательные инструменты для моих проектов на Yii2 для стандартизации процесса разработки.

Console
=======


Шаблоны миграций
----------------

В `console/config/main.php`:

```
    'controllerMap' => [
        ...
        'migrate' => [
            'class' => \Yii2ProjectAssist\Console\MigrateController::class,
        ],
        ...
    ],
```


Хелпер для перебора записей
---------------------------

```
$query = Model::find()->orderBy('id');

BatchIteratorHelper::processEach($query, function (Model $model, int &$updatedCounter) {
    //TODO: Do something with $model here...

    ++$updatedCounter;
});
```

или

```
$query = Model::find()->orderBy('id');

BatchIteratorHelper::processBatch($query, function (array $users, int &$updatedCounter) {
    //TODO: Do something with $users here...

    $updatedCounter += count($users);
});
```


TODO
====

* [Свои шаблоны для Gii](https://github.com/yiisoft/yii2-gii/blob/master/docs/guide-ru/topics-creating-your-own-templates.md)
