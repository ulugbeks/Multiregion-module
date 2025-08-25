# Модуль Multiregions для OkayCMS 4.5.2

## 📋 Описание
Модуль мультирегионов позволяет создавать поддомены для разных городов с автоматической настройкой SEO-тегов и учетом склонений русского языка. Каждый город получает свой поддомен (например, msk.gidro-butik.ru для Москвы) с уникальными SEO-настройками.

## ✨ Возможности
- ✅ Создание и управление поддоменами через админ-панель
- ✅ Автоматическое определение склонений названий городов
- ✅ Гибкие SEO-шаблоны для каждого типа страниц
- ✅ Поддержка переменных в шаблонах
- ✅ Определение текущего поддомена по URL
- ✅ Передача данных о городе в шаблоны Smarty

## 📁 Структура модуля
```
Okay/Modules/OkayCMS/Multiregions/
├── Backend/
│   ├── Controllers/
│   │   ├── MultiregionsAdmin.php     # Список поддоменов
│   │   └── MultiregionAdmin.php      # Редактирование поддомена
│   ├── Helpers/
│   │   └── BackendMultiregionsHelper.php
│   ├── Requests/
│   │   └── BackendMultiregionsRequest.php
│   ├── design/
│   │   └── html/
│   │       ├── multiregions.tpl      # Шаблон списка
│   │       └── multiregion.tpl       # Шаблон редактирования
│   └── lang/
│       └── ru.php                    # Языковые переменные
├── Entities/
│   ├── SubdomainsEntity.php          # Сущность поддоменов
│   └── SubdomainSeoEntity.php        # Сущность SEO-шаблонов
├── Frontend/
│   └── MultiregionsPlugin.php        # Frontend плагин
├── Helpers/
│   ├── SubdomainDetector.php         # Определение поддомена
│   ├── SeoProcessor.php              # Обработка SEO
│   └── CityDeclension.php            # Склонения городов
├── Init/
│   ├── Init.php                      # Инициализация модуля
│   └── services.php                  # Конфигурация сервисов
└── module.json                        # Метаданные модуля
```

## 🚀 Установка

### 1. Загрузка модуля
```bash
# Перейдите в директорию модулей
cd /путь/к/сайту/Okay/Modules/OkayCMS/

# Скачайте или скопируйте модуль
# Структура должна быть: Okay/Modules/OkayCMS/Multiregions/
```

### 2. Установка через админ-панель
1. Зайдите в админ-панель OkayCMS
2. Перейдите в раздел **Модули**
3. Найдите модуль **Multiregions**
4. Нажмите **Установить**
5. После установки нажмите **Включить**

### 3. Настройка .htaccess
Добавьте после строки `RewriteEngine on` в корневом `.htaccess`:

```apache
# ===== MULTIREGIONS MODULE START =====
# Обработка поддоменов для мультирегионов
RewriteCond %{QUERY_STRING} !subdomain_city=
RewriteCond %{HTTP_HOST} ^([a-z0-9-]+)\.gidro-butik\.ru$ [NC]
RewriteCond %1 !^(www|mail|ftp|admin|api|dev)$ [NC]
RewriteRule ^(.*)$ $1?subdomain_city=%1 [QSA,DPI]
# ===== MULTIREGIONS MODULE END =====
```

**Важно:** Замените `gidro-butik\.ru` на ваш домен!

### 4. Настройка DNS
Добавьте wildcard A-запись для поддоменов:
```
*.ваш-домен.ru    A    IP_вашего_сервера
```
Или для каждого города отдельно:
```
msk.ваш-домен.ru   A    IP_вашего_сервера
spb.ваш-домен.ru   A    IP_вашего_сервера
```

### 5. Настройка веб-сервера

#### Apache
В конфигурации виртуального хоста:
```apache
<VirtualHost *:80>
    ServerName ваш-домен.ru
    ServerAlias *.ваш-домен.ru
    DocumentRoot /путь/к/сайту
    # остальные настройки...
</VirtualHost>
```

#### Nginx
```nginx
server {
    server_name ваш-домен.ru *.ваш-домен.ru;
    root /путь/к/сайту;
    # остальные настройки...
}
```

### 6. Изменения в index.php
Добавьте после строки `$modules->startEnabledModules();` (около строки 74):

```php
// ===== MULTIREGIONS MODULE INITIALIZATION START =====
if (class_exists('Okay\Modules\OkayCMS\Multiregions\Frontend\MultiregionsPlugin')) {
    try {
        if (!preg_match('~^/?backend~', $_SERVER['REQUEST_URI'])) {
            $multiregionsPlugin = new \Okay\Modules\OkayCMS\Multiregions\Frontend\MultiregionsPlugin();
            $GLOBALS['multiregions_plugin'] = $multiregionsPlugin;
        }
    } catch (\Exception $e) {
        if ($config->get('debug_mode') == true) {
            error_log('Multiregions plugin initialization error: ' . $e->getMessage());
        }
    }
}
// ===== MULTIREGIONS MODULE INITIALIZATION END =====
```

## ⚙️ Настройка модуля

### 1. Добавление поддомена
1. В админ-панели перейдите в **Настройки → Мультирегионы**
2. Нажмите **Добавить поддомен**
3. Заполните поля:
   - **Поддомен**: например, `msk` (только латиница и дефис)
   - **Город**: например, `Москва`
   - **Активен**: включите галочку

### 2. Настройка склонений
Нажмите **Определить автоматически** или заполните вручную:
- **Именительный**: Москва (кто? что?)
- **Родительный**: Москвы (кого? чего?)
- **Дательный**: Москве (кому? чему?)
- **Винительный**: Москву (кого? что?)
- **Творительный**: Москвой (кем? чем?)
- **Предложный**: Москве (о ком? о чём? где?)

### 3. Настройка SEO-шаблонов
Для каждого типа страниц можно настроить свои шаблоны:

#### Главная страница
```
Meta Title: Интернет-магазин сантехники в {city_prepositional} - {site_name}
Meta Description: Купить сантехнику в {city_prepositional} с доставкой. ✓ Большой выбор ✓ Низкие цены
H1: Интернет-магазин сантехники в {city_prepositional}
```

#### Категории товаров
```
Meta Title: {category} в {city_prepositional} - купить по низким ценам | {site_name}
Meta Description: {category} в {city_prepositional} с доставкой. Большой выбор, низкие цены.
H1: {category} в {city_prepositional}
```

#### Страницы товаров
```
Meta Title: {product} купить в {city_prepositional} - цена {price} руб | {site_name}
Meta Description: Купить {product} в {city_prepositional}. ✓ В наличии ✓ Доставка по {city_dative}
```

## 📝 Доступные переменные

### Переменные города
| Переменная | Описание | Пример |
|------------|----------|--------|
| `{city}` | Название города | Москва |
| `{city_nominative}` | Именительный падеж | Москва |
| `{city_genitive}` | Родительный падеж | Москвы |
| `{city_dative}` | Дательный падеж | Москве |
| `{city_accusative}` | Винительный падеж | Москву |
| `{city_instrumental}` | Творительный падеж | Москвой |
| `{city_prepositional}` | Предложный падеж | Москве |
| `{in_city}` | В городе | в Москве |
| `{from_city}` | Из города | из Москвы |
| `{to_city}` | В город | в Москву |

### Переменные контента
| Переменная | Описание | Где доступна |
|------------|----------|--------------|
| `{category}` | Название категории | Страницы категорий |
| `{product}` | Название товара | Страницы товаров |
| `{price}` | Цена товара | Страницы товаров |
| `{brand}` | Название бренда | Страницы брендов |
| `{site_name}` | Название сайта | Везде |

## 🎨 Использование в шаблонах

В шаблонах Smarty доступны следующие переменные:

```smarty
{* Проверка наличия поддомена *}
{if $current_subdomain}
    <p>Вы находитесь в городе: {$current_city}</p>
    <p>Доставка {$city_cases.in_city}</p>
    <p>Доставка {$city_cases.from_city}</p>
{/if}

{* Все склонения *}
{if $city_cases}
    Именительный: {$city_cases.nominative}
    Родительный: {$city_cases.genitive}
    Дательный: {$city_cases.dative}
    Винительный: {$city_cases.accusative}
    Творительный: {$city_cases.instrumental}
    Предложный: {$city_cases.prepositional}
    В городе: {$city_cases.in_city}
{/if}
```

## 🔧 Решение проблем

### Проблема: HTTP 500 ошибка
**Решение:**
1. Проверьте логи ошибок PHP
2. Убедитесь, что все файлы модуля на месте
3. Проверьте права доступа (755 для папок, 644 для файлов)
4. Отключите вывод ошибок в index.php (закомментируйте `error_reporting(E_ALL)`)

### Проблема: Поддомен не открывается
**Решение:**
1. Проверьте DNS записи: `nslookup msk.ваш-домен.ru`
2. Проверьте настройки веб-сервера (ServerAlias для Apache)
3. Убедитесь, что .htaccess настроен правильно
4. Проверьте, что поддомен активен в админ-панели

### Проблема: SEO-теги не применяются
**Решение:**
1. Проверьте, что SEO-шаблоны заполнены для нужного типа страниц
2. Очистите кэш: `rm -rf compiled/* cache/*`
3. Убедитесь, что поддомен определяется (проверьте переменную `$current_subdomain`)

### Проблема: Ошибка "unknown tag 'multiregion_seo'"
**Решение:**
Эта функция требует создания Smarty-плагина или использования inline SEO в шаблонах.
Временное решение - удалить вызовы `{multiregion_seo}` из шаблонов.

### Проблема: Warnings about missing files
**Решение:**
Если появляются предупреждения о несуществующих файлах других модулей:
1. Создайте пустые файлы-заглушки
2. Или отключите проблемные модули в админ-панели

## 📊 Таблицы в базе данных

### Таблица ok_subdomains
```sql
CREATE TABLE `ok_subdomains` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `subdomain` varchar(50) NOT NULL,
  `city_name` varchar(100) NOT NULL,
  `city_nominative` varchar(100) DEFAULT NULL,
  `city_genitive` varchar(100) DEFAULT NULL,
  `city_dative` varchar(100) DEFAULT NULL,
  `city_accusative` varchar(100) DEFAULT NULL,
  `city_instrumental` varchar(100) DEFAULT NULL,
  `city_prepositional` varchar(100) DEFAULT NULL,
  `enabled` tinyint(1) DEFAULT 1,
  `position` int(11) DEFAULT 0,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `subdomain` (`subdomain`)
);
```

### Таблица ok_subdomain_seo
```sql
CREATE TABLE `ok_subdomain_seo` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `subdomain_id` int(11) NOT NULL,
  `page_type` varchar(50) NOT NULL,
  `meta_title_pattern` text DEFAULT NULL,
  `meta_description_pattern` text DEFAULT NULL,
  `meta_keywords_pattern` text DEFAULT NULL,
  `h1_pattern` text DEFAULT NULL,
  `description_pattern` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `subdomain_page` (`subdomain_id`, `page_type`)
);
```

## 🔍 Проверка работы модуля

### 1. Проверка определения поддомена
Создайте файл `test_subdomain.php` в корне сайта:
```php
<?php
echo "Host: " . $_SERVER['HTTP_HOST'] . "<br>";
echo "Subdomain GET param: " . ($_GET['subdomain_city'] ?? 'not set') . "<br>";
```

Откройте `https://msk.ваш-домен.ru/test_subdomain.php` и убедитесь, что параметр определяется.

### 2. Проверка переменных в шаблоне
Добавьте в шаблон временно:
```smarty
{if $current_subdomain}
    <div style="background: green; color: white; padding: 10px;">
        Поддомен активен: {$current_city}
    </div>
{/if}
```

### 3. Проверка в админ-панели
- Перейдите в **Настройки → Мультирегионы**
- Убедитесь, что поддомен создан и активен
- Проверьте, что SEO-шаблоны заполнены

## 💡 Полезные советы

### Оптимизация для SEO
1. Используйте уникальные meta-теги для каждого города
2. Добавляйте город в H1 заголовки
3. Используйте правильные склонения для естественности текста
4. Создайте уникальный контент для главных страниц поддоменов

### Производительность
1. Регулярно очищайте кэш после изменений
2. Минимизируйте количество активных поддоменов
3. Используйте CDN для статических файлов

### Безопасность
1. Ограничьте создание поддоменов только нужными городами
2. Проверяйте входные данные при создании поддоменов
3. Регулярно обновляйте модуль

## 📞 Поддержка

При возникновении проблем:
1. Проверьте логи ошибок в `/var/log/` или в панели хостинга
2. Убедитесь, что версия OkayCMS >= 4.5.0
3. Проверьте совместимость с другими модулями

## 📄 Лицензия

Модуль распространяется под лицензией MIT.

## 🔄 История версий

### Версия 1.0.0 (2024)
- Первый релиз
- Поддержка поддоменов
- Автоматические склонения
- SEO-шаблоны
- Интеграция с OkayCMS 4.5.2

## ✅ Чеклист после установки

- [ ] Модуль установлен и включен в админ-панели
- [ ] .htaccess настроен правильно
- [ ] DNS записи добавлены
- [ ] Веб-сервер настроен для поддоменов
- [ ] index.php модифицирован
- [ ] Создан хотя бы один поддомен
- [ ] SEO-шаблоны настроены
- [ ] Кэш очищен
- [ ] Поддомен открывается и работает
- [ ] SEO-теги применяются корректно

## 🙏 Благодарности

Спасибо за использование модуля Multiregions для OkayCMS!