Пример запуска на проде композера

`php7.2 /usr/local/bin/composer require knplabs/knp-paginator-bundle`

Создать базу

`php bin/console doctrine:schema:create`

Обновить базу

`php bin/console doctrine:schema:update --force`

Регенерация полей Entity

`php bin/console make:entity --regenerate App`

Генерация контроллера

`php bin/console make:controller Default`

Очистка кэша (после добавления нового ресурса перевода обязательно!)

`php bin/console cache:clear`

Кроновская команда на отправку сообщений-напоминаний об оплате:

`php72 bin/console notification http://vijoys.com`

Кроновская команда для удаления файлов старых заказов (2 месяца - оплаченные, месяц - неплаченные)

`php bin/console clearOrder "C:/OSPanel/domains/render/public/"`

Кроновская команда для очистки файлов папки tmp

`php bin/console clearFiles`

Создания админ пользователя:

`php bin/console fos:user:create admin --super-admin`

Создание символических ссылок с бандлов

`php bin/console assets:install --symlink`

Загрузка начальных(тестовых, рыбных) данных:

`php bin/console doctrine:fixtures:load`
`php bin/console doctrine:fixtures:load --group=worker`

После первого запуска нужно скачать файлы для работы CKEditor

`php bin/console ckeditor:install`

Миграции после внесения изменения в структуру базы:

Создать миграцию на основе изменений:

`php bin/console make:migration`

Применить миграцию:

`php bin/console doctrine:migrations:migrate`

Сгенерировать sitemap:

`php bin/console presta:sitemaps:dump --base-url=https://vijoys.com/`

Запуск удаленного рендеринга:
0. Глобально устанавливаем nexrender 
`npm i -g @nexrender/cli @nexrender/action-copy @nexrender/action-encode @nexrender/provider-s3 @nexrender/server @nexrender/worker
 npm i -g @nexrender/api --save`
1. Запускаем API сервер `nexrender-server --port=3050 --secret=myapisecret`
2. Запускаем node render `nexrender-worker --host=https://nexrender-server.local:3050 --secret=myapisecret`
    --sectet всегда должен совпадать. 
3. В папке проекта запускаем `node render.js`

Требования к содержанию архива:
0. Проект должен сохраняться как XML project (обязательное условие)
1. В корне иметь проект название которого должно быть "project.aepx"
2. В корне иметь проект название которого должно быть "project_demo.aepx"
3. Картинки должны быть всегда в корне. Плейсхолдеры изображений указывать их же названием без указания формата, он всегда jpg.
Пример "Image.png" -> "Image"
4. Композиция для рендеринга должна называться "Render"
5. Для работы с текстом нужно сообщить название композиии и ID Layer'a в композиции для его точной идентификации (http://prntscr.com/mpoi5h)
6. Все расширения файлов должны быть в нижнем регистре.
    Для картинок - только .jpg
    Для аудио - только .mp3
    Для видео - только .mp4
7. В случае не воспроизведения аудио - нужно включать его на родительских layer'aх (http://prntscr.com/mpp3yk)
8. Каждый проект настраивать рендеринг что бы была активна очередь (без запуска)
Правки - Шаблона - Модуль рендеринга - Изменить (настройки, блок) - Формат : AVI