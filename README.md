[Русская версия](#Модуль-оплаты-begateway-для-joomla-2.5-3.x-и-j2store)

# BeGateway payment module for Joomla 2.5/3.x and J2Store

## System requirements

* PHP 5.6+
* [cURL extension](http://php.net/manual/en/book.curl.php)
* [Joomla](http://www.joomla.org/download.html) (the module was tested with version 3.8.7)
* [J2Store](https://www.j2store.org/) (the module was tested with version 3.3.0)

## The module installation

1. Download [payment_begateway.zip](https://github.com/begateway/j2store-payment-module/blob/master/payment_begateway.zip?raw=true)
2. Go to Joomla's administration panel
3. Go to _Manage_ page via the menu _Extensions_
4. Install the module package
  1. Open the page _Install_ and the tab _Upload Package File_
  2. Select the module package file saved at the step 1.
  3. Click _Upload & Install_ to install the module
  4. Go to _Plugins_ via the menu _Extensions_
  5. Locate the plugin _BeGateway Payment Plugin_ and enable it

## The module configuration

1. Go to Joomla's administration panel
2. Go to J2Store's administration panel via _Components → J2Store_
3. Go to the menu _Setup → Payment methods_
4. Click the the link _BeGateway Payment Plugin_
5. Configure the plugin and click the _Save_ button
6. Enable the payment plugin
  1. Select the configured payment plugin in the list of payment methods
  2. Publish it by clicking an icon in the column _Enabled_

## Testing

You can use the following information to adjust the payment method in test mode:

  * __Shop ID:__ 361
  * __Shop Key:__ b8647b68898b084b836474ed8d61ffe117c9a01168d867f24953b776ddcb134d
  * __Checkout page domain:__ checkout.begateway.com

Use the following test card to make successful test payment:

  * Card number: 4200000000000000
  * Name on card: JOHN DOE
  * Card expiry date: 01/30
  * CVC: 123

Use the following test card to make failed test payment:

  * Card number: 4005550000000019
  * Name on card: JOHN DOE
  * Card expiry date: 01/30
  * CVC: 123

[English version](#begateway-payment-module-for-joomla-2.x-3-and-j2store)

# Модуль оплаты BeGateway для Joomla 2.5/3.x и J2Store

## Системные требования

* PHP 5.6+
* [cURL](http://php.net/manual/en/book.curl.php)
* [Joomla](http://www.joomla.org/download.html) 3.x (модуль был разработан и протестирован с версией 3.8.7)
* [J2Store](https://www.j2store.org/) (модуль был разработан и протестирован с версией 3.3.0)

## Установка

1. Скачайте [payment_begateway.zip](https://github.com/begateway/j2store-payment-module/blob/master/payment_begateway.zip?raw=true)
2. Зайдите в панель администратора Joomla
3. Через меню _Расширения_ перейдите в _Менеджер расширений_
4. Установите пакет
  1. Откройте страницу _Установка_ и закладку _Загрузить файл пакета_
  2. Выберите файл архива модуля, скаченного на шаге 1
  3. Нажмите _Загрузить и установить_, чтобы установить модуль
  4. Через меню _Расширения_ перейдите в _Плагины_
  5. Найдите плагин _BeGateway Payment Plugin_ и включите его

## Настройка модуля

1. Зайдите в панель администратора Joomla
2. Зайдите в панель администратора J2Store через _Компоненты → J2Store_
3. Перейдите в меню _Настройки → Способы оплаты_
4. Выберите _BeGateway Payment Plugin_
5. Настройте плагин и нажмите кнопку _Сохранить_
6. Опубликуйте способ оплаты
  1. Выберите настроенный способ оплаты в списке доступных способов оплаты
  2. Опубликуйте его, кликнув на иконку в колонке _Enabled_

## Тестирование

Вы можете использовать следующие данные, чтобы настроить способ оплаты в тестовом режиме

  * __Идентификационный номер магазина:__ 361
  * __Секретный ключ магазина:__ b8647b68898b084b836474ed8d61ffe117c9a01168d867f24953b776ddcb134d
  * __Домен платежного шлюза:__ demo-gateway.begateway.com
  * __Домен платежной страницы:__ checkout.begateway.com
  * __Режим работы:__ Тестовый

Используйте следующие данные карты для успешного тестового платежа:

  * Номер карты: 4200000000000000
  * Имя на карте: JOHN DOE
  * Месяц срока действия карты: 01/30
  * CVC: 123

Используйте следующие данные карты для неуспешного тестового платежа:

  * Номер карты: 4005550000000019
  * Имя на карте: JOHN DOE
  * Месяц срока действия карты: 01/30
  * CVC: 123
