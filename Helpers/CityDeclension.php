<?php

namespace Okay\Modules\OkayCMS\Multiregions\Helpers;

class CityDeclension
{
    // Предопределенные склонения для сложных названий
    private $customCases = [
        'Москва' => [
            'nominative' => 'Москва',
            'genitive' => 'Москвы',
            'dative' => 'Москве',
            'accusative' => 'Москву',
            'instrumental' => 'Москвой',
            'prepositional' => 'Москве'
        ],
        'Санкт-Петербург' => [
            'nominative' => 'Санкт-Петербург',
            'genitive' => 'Санкт-Петербурга',
            'dative' => 'Санкт-Петербургу',
            'accusative' => 'Санкт-Петербург',
            'instrumental' => 'Санкт-Петербургом',
            'prepositional' => 'Санкт-Петербурге'
        ],
        'Нижний Новгород' => [
            'nominative' => 'Нижний Новгород',
            'genitive' => 'Нижнего Новгорода',
            'dative' => 'Нижнему Новгороду',
            'accusative' => 'Нижний Новгород',
            'instrumental' => 'Нижним Новгородом',
            'prepositional' => 'Нижнем Новгороде'
        ],
        'Ростов-на-Дону' => [
            'nominative' => 'Ростов-на-Дону',
            'genitive' => 'Ростова-на-Дону',
            'dative' => 'Ростову-на-Дону',
            'accusative' => 'Ростов-на-Дону',
            'instrumental' => 'Ростовом-на-Дону',
            'prepositional' => 'Ростове-на-Дону'
        ],
        'Набережные Челны' => [
            'nominative' => 'Набережные Челны',
            'genitive' => 'Набережных Челнов',
            'dative' => 'Набережным Челнам',
            'accusative' => 'Набережные Челны',
            'instrumental' => 'Набережными Челнами',
            'prepositional' => 'Набережных Челнах'
        ],
        'Сочи' => [
            'nominative' => 'Сочи',
            'genitive' => 'Сочи',
            'dative' => 'Сочи',
            'accusative' => 'Сочи',
            'instrumental' => 'Сочи',
            'prepositional' => 'Сочи'
        ]
    ];
    
    /**
     * Получить все склонения города
     */
    public function getCases($cityName)
    {
        // Проверяем предопределенные склонения
        if (isset($this->customCases[$cityName])) {
            return $this->customCases[$cityName];
        }
        
        // Пытаемся склонить автоматически
        return $this->declineCity($cityName);
    }
    
    /**
     * Автоматическое склонение города по правилам русского языка
     */
    private function declineCity($city)
    {
        // Несклоняемые города (оканчивающиеся на -о, -е, -и, -у, -ы)
        if (preg_match('/[оеиуы]$/ui', $city)) {
            return array_fill_keys([
                'nominative', 'genitive', 'dative',
                'accusative', 'instrumental', 'prepositional'
            ], $city);
        }
        
        $result = [
            'nominative' => $city,
            'genitive' => $city,
            'dative' => $city,
            'accusative' => $city,
            'instrumental' => $city,
            'prepositional' => $city
        ];
        
        // Города на -ск, -цк (Новосибирск, Минск)
        if (preg_match('/([а-я]+)(ск|цк)$/ui', $city, $matches)) {
            $base = $matches[1] . $matches[2];
            $result = [
                'nominative' => $city,
                'genitive' => $base . 'а',
                'dative' => $base . 'у',
                'accusative' => $city,
                'instrumental' => $base . 'ом',
                'prepositional' => $base . 'е'
            ];
        }
        // Города на -ов, -ев, -ёв (Ростов, Киев)
        elseif (preg_match('/([а-я]+)(ов|ев|ёв)$/ui', $city, $matches)) {
            $base = $matches[1] . $matches[2];
            $result = [
                'nominative' => $city,
                'genitive' => $base . 'а',
                'dative' => $base . 'у',
                'accusative' => $city,
                'instrumental' => $base . 'ом',
                'prepositional' => $base . 'е'
            ];
        }
        // Города на -ий (Нижний, Великий)
        elseif (preg_match('/([а-я]+)ий$/ui', $city, $matches)) {
            $base = $matches[1];
            $result = [
                'nominative' => $city,
                'genitive' => $base . 'его',
                'dative' => $base . 'ему',
                'accusative' => $city,
                'instrumental' => $base . 'им',
                'prepositional' => $base . 'ем'
            ];
        }
        // Города на -ый (Новый, Старый)
        elseif (preg_match('/([а-я]+)ый$/ui', $city, $matches)) {
            $base = $matches[1];
            $result = [
                'nominative' => $city,
                'genitive' => $base . 'ого',
                'dative' => $base . 'ому',
                'accusative' => $city,
                'instrumental' => $base . 'ым',
                'prepositional' => $base . 'ом'
            ];
        }
        // Города на -ь мужского рода (Казань, Рязань)
        elseif (preg_match('/([а-я]+)нь$/ui', $city, $matches)) {
            $base = $matches[1] . 'н';
            $result = [
                'nominative' => $city,
                'genitive' => $base . 'и',
                'dative' => $base . 'и',
                'accusative' => $city,
                'instrumental' => $base . 'ью',
                'prepositional' => $base . 'и'
            ];
        }
        // Города на -а (Москва, Самара)
        elseif (preg_match('/([а-я]+)а$/ui', $city, $matches)) {
            $base = $matches[1];
            // Проверяем на шипящие
            if (preg_match('/[жшчщ]$/ui', $base)) {
                $result = [
                    'nominative' => $city,
                    'genitive' => $base . 'и',
                    'dative' => $base . 'е',
                    'accusative' => $base . 'у',
                    'instrumental' => $base . 'ей',
                    'prepositional' => $base . 'е'
                ];
            } else {
                $result = [
                    'nominative' => $city,
                    'genitive' => $base . 'ы',
                    'dative' => $base . 'е',
                    'accusative' => $base . 'у',
                    'instrumental' => $base . 'ой',
                    'prepositional' => $base . 'е'
                ];
            }
        }
        // Города на -я (Анталья)
        elseif (preg_match('/([а-я]+)я$/ui', $city, $matches)) {
            $base = $matches[1];
            $result = [
                'nominative' => $city,
                'genitive' => $base . 'и',
                'dative' => $base . 'е',
                'accusative' => $base . 'ю',
                'instrumental' => $base . 'ей',
                'prepositional' => $base . 'е'
            ];
        }
        // Города на согласную (мужской род)
        elseif (preg_match('/[бвгджзклмнпрстфхцчшщ]$/ui', $city)) {
            $result = [
                'nominative' => $city,
                'genitive' => $city . 'а',
                'dative' => $city . 'у',
                'accusative' => $city,
                'instrumental' => $city . 'ом',
                'prepositional' => $city . 'е'
            ];
        }
        
        return $result;
    }
    
    /**
     * Получить конкретный падеж
     */
    public function getCase($cityName, $case)
    {
        $cases = $this->getCases($cityName);
        return $cases[$case] ?? $cityName;
    }
    
    /**
     * Добавить пользовательское склонение
     */
    public function addCustomCase($cityName, $cases)
    {
        $this->customCases[$cityName] = $cases;
    }
}