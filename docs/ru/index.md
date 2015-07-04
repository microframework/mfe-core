MicroFramework Engine (Ядро)
===========================

Микрофреймворк представляет собой реализацию основных потребностей при написании приложений на языке PHP.

Документация
--------
* Использование двигателя
  * [События](events.md)
* Создание приложений
  * [Первое приложение](first-application.md)

Структура и API
--------
* mfe\core
  * \libs
    * \base
      * [CApplication](src/libs/base/CApplication.md)
      * [CComponent](src/libs/base/CComponent.md)
      * [CCore](src/libs/base/CCore.md)
      * [CManager](src/libs/base/CManager.md)
    * \components 
      * [CDebug](src/libs/components/CDebug.md)
      * [CDisplay](src/libs/components/CDisplay.md)
      * [CEvent](src/libs/components/CEvent.md)
      * [CEcxeption](src/libs/components/CEcxeption.md)
      * [CFilterVariable](src/libs/components/CFilterVariable.md)
      * [CLayout](src/libs/components/CLayout.md)
      * [CLog](src/libs/components/CLog.md)
      * [CObjectStack](src/libs/components/CObjectStack.md)
    * \handlers
      * [CRunHandler](src/libs/handlers/CRunHandler.md)
    * \helpers 
      * [CAssetHelper](src/libs/helpers/CAssetHelper.md)
      * [CSimpleFileHelper](src/libs/helpers/CSimpleFileHelper.md)
    * \managers 
      * [CApplicationManager](src/libs/managers/CApplicationManager.md)
      * [CComponentManager](src/libs/managers/CComponentManager.md)
      * [CEventManager](src/libs/managers/CEventManager.md)
    * \system
      * [IoC](src/libs/system/IoC.md)
      * [Object](src/libs/system/Object.md)
      * [PSR4Autoloader](src/libs/system/PSR4Autoloader.md)
      * [ServiceLocator](src/libs/system/ServiceLocator.md)
  * [Init](src/Init.md)
  * [MfE](src/Mfe.md)

Требования
--------

 - PHP 5.6 (PHP 7 на данный момент не поддерживается)

Авторы
--------

 - devinterx @ Dimitriy Kalugin


Лицензия
--------
BSD