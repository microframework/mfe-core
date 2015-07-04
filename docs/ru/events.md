# Events Manager #

Менеджер событий представляет удобный интерфейс для работы с событиями для разработчика приложения. Интерфейс реализуется тремя гибридными методами. 


## Запуск событий ##

    hybrid public method trigger(String eventName, Array args[], Closure callback);
	
Этот метод запускает цепочку событий, `args` представляет собой массив с аргументами, которые будут переданы в `callback` цепочки. `callback` возвращает результаты каждого события из цепочки.

Пример применения в PHP:

	mfe|$app::triger($name, []);
	mfe::app()|$app->events->trigger($name, []);


## Добавление событий ##

    hybrid public method on(String eventName, Mixed callback);

Этот метод добавляет событие в цепочку, `callback` представляет собой строку с названием функции `function`, или массив `[class, method]`, или же замыкание `Closure`

Пример применения в PHP:

	mfe|$app::on($name, $calback);
	mfe::app()|$app->events->on($name, $calback);


## Удаление событий ##

    hybrid public method off(String eventName, Mixed callback);

Этот метод удаляет событие из цепочки, `callback` представляет собой строку с названием функции `function`, или массив `[class, method]`, или же замыкание `Closure`.

Пример применения в PHP:

	mfe|$app::off($name, []);
	mfe::app()|$app->events->off($name, $calback);
