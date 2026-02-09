<?php
return [

    /*
    |--------------------------------------------------------------------------
    | Языковые ресурсы для проверки значений
    |--------------------------------------------------------------------------
    |
    | Последующие языковые строки содержат сообщения по-умолчанию, используемые
    | классом, проверяющим значения (валидатором).Некоторые из правил имеют
    | несколько версий, например, size. Вы можете поменять их на любые
    | другие, которые лучше подходят для вашего приложения.
    |
    */

    "accepted" => "Вы должны принять :attribute.",
    "active_url" => "Поле :attribute недействительный URL.",
    "after" => "Поле :attribute должно быть датой после :date.",
    'after_or_equal' => 'Поле :attribute должно быть не раньше :date.',
    "alpha" => "Поле :attribute может содержать только буквы.",
    "alpha_dash" => "Поле :attribute может содержать только буквы, цифры и дефис.",
    "alpha_num" => "Поле :attribute может содержать только буквы и цифры.",
    "array" => "Поле :attribute должно быть массивом.",
    "before" => "Поле :attribute должно быть датой перед :date.",
    'before_or_equal' => 'Поле :attribute должно быть не позже :date.',
    "between" => [
        "numeric" => "Поле :attribute должно быть между :min и :max.",
        "file" => "Размер :attribute должен быть от :min до :max Килобайт.",
        "string" => "Длина :attribute должна быть от :min до :max символов.",
        "array" => "Поле :attribute должно содержать :min - :max элементов."
    ],
    "confirmed" => "Поле :attribute не совпадает с подтверждением.",
    "date" => "Поле :attribute не является датой.",
    "date_format" => "Поле :attribute не соответствует формату :format.",
    "different" => "Поля :attribute и :other должны различаться.",
    "digits" => "Длина цифрового поля :attribute должна быть :digits.",
    "digits_between" => "Длина цифрового поля :attribute должна быть между :min и :max.",
    "email" => "Поле :attribute имеет ошибочный формат.",
    "exists" => "Выбранное значение для :attribute не существует.",
    "image" => "Поле :attribute должно быть изображением.",
    "in" => "Выбранное значение для :attribute ошибочно.",
    "integer" => "Поле :attribute должно быть целым числом.",
    "ip" => "Поле :attribute должно быть действительным IP-адресом.",
    "max" => [
        "numeric" => "Поле :attribute должно быть не больше :max.",
        "file" => "Поле :attribute должно быть не больше :max Килобайт.",
        "string" => "Поле :attribute должно быть не длиннее :max символов.",
        "array" => "Поле :attribute должно содержать не более :max элементов."
    ],
    "mimes" => "Поле :attribute должно быть файлом одного из типов: :values.",
    "extensions" => "Поле :attribute должно иметь одно из расширений: :values.",
    "min" => [
        "numeric" => "Поле :attribute должно быть не менее :min.",
        "file" => "Поле :attribute должно быть не менее :min Килобайт.",
        "string" => "Поле :attribute должно быть не короче :min символов.",
        "array" => "Поле :attribute должно содержать не менее :min элементов."
    ],
    "not_in" => "Выбранное значение для :attribute ошибочно.",
    "numeric" => "Поле :attribute должно быть числом.",
    "regex" => "Поле :attribute имеет ошибочный формат.",
    "required" => "Поле :attribute обязательно для заполнения.",
    "required_if" => "Поле :attribute обязательно для заполнения, когда :other равно :value.",
    "required_with" => "Поле :attribute обязательно для заполнения, когда :values указано.",
    "required_without" => "Поле :attribute обязательно для заполнения, когда :values не указано.",
    "same" => "Значение :attribute должно совпадать с :other.",
    "size" => [
        "numeric" => "Поле :attribute должно быть :size.",
        "file" => "Поле :attribute должно быть :size Килобайт.",
        "string" => "Поле :attribute должно быть длиной :size символов.",
        "array" => "Количество элементов в поле :attribute должно быть :size."
    ],
    "unique" => "Такое значение поля :attribute уже существует.",
    "url" => "Поле :attribute имеет ошибочный формат.",

    /*
    |--------------------------------------------------------------------------
    | Собственные языковые ресурсы для проверки значений
    |--------------------------------------------------------------------------
    |
    | Здесь Вы можете указать собственные сообщения для атрибутов, используя
    | соглашение именования строк "attribute.rule". Это позволяет легко указать
    | свое сообщение для заданного правила атрибута.
    |
    | http://laravel.com/docs/validation#custom-error-messages
    |
    */

    'custom' => [],

    /*
    |--------------------------------------------------------------------------
    | Собственные названия атрибутов
    |--------------------------------------------------------------------------
    |
    | Последующие строки используются для подмены программных имен элементов
    | пользовательского интерфейса на удобочитаемые. Например, вместо имени
    | поля "email" в сообщениях будет выводиться "электронный адрес".
    |
    | Пример использования
    |
    |   'attributes' => array(
    |       'email' => 'электронный адрес',
    |   )
    |
    */

    'attributes' => [
        'old_password' => 'старый пароль',
        'password' => 'пароль',
        'user_id' => 'пользователь',
        'phone' => 'телефон',
        'email' => 'e-mail',
        'name' => 'имя',
        'surname' => 'фамилия',
        'city_id' => 'город',
        'city' => 'город',
        'avatar' => 'аватар',
        'phone_code' => 'смс код',
        // claim
        'order_id' => 'заявка',
        'text' => 'текст',
        'documents' => 'документы',
        'documents.*.type' => 'тип',
        'documents.*.url' => 'ссылка',
        //company
        'company_type_id' => 'тип',
        'inn' => 'ИНН',
        'title' => 'название',
        'full_title' => 'полное название',
        'ogrn' => 'ОГРН',
        'kpp' => 'КПП',
        'okpo' => 'ОКПО',
        'legal_address' => 'юр. адрес',
        'address' => 'адрес',
        'director' => 'директор',
        'website' => 'сайт',
        'description' => 'описание',
        'vehicles_types_id' => 'тип',
        'vehicles_types_id.*' => 'тип',
        // company type
        'is_worker' => 'исполнитель',
        // form question
        'type' => 'тип',
        'label' => 'текст вопроса',
        'required' => 'обязательно',
        'options' => 'варианты ответов',
        // message
        'file_url' => 'файл',
        'file_type' => 'тип файла',
        // order
        'vehicle_type_id' => 'техника',
        'vehicles_count' => 'количество',
        'form_answers' => 'анкета',
        'form_answers.*.form_question_id' => 'вопрос',
        'form_answers.*.value' => 'ответ',
        'start_date' => 'начало работ',
        'finish_date' => 'окончание работ',
        'addresses' => 'адрес',
        'addresses.*.lat' => 'широта',
        'addresses.*.lng' => 'долгота',
        'addresses.*.address' => 'адрес',
        'addresses.*.city_id' => 'город',
        'addresses.*.city' => 'город',
        'addresses.*.region_id' => 'регион',
        'addresses.*.region' => 'регион',
        'amount_type' => 'тип бюджета',
        'amount_account_vat' => 'бюджет с НДС',
        'amount_account' => 'бюджет без НДС',
        'amount_cash' => 'бюджет нал.',
        'amount_by_agreement' => 'по договорённости',
        // photo
        'url' => 'адрес',
        // photo group
        'photos_id' => 'изображения',
        'photos_id.*' => 'id изображений',
        'photos' => 'изображения',
        'photos.*' => 'url изображений',
        // push notification
        'subject' => 'тема',
        // recommendation
        'company_id' => 'компания',
        'target_user_id' => 'целевой пользователь',
        // report
        'referee_conclusion' => 'заключение',
        'report_type_id' => 'тип претензии',
        'amount' => 'сумма претензии',
        // reserved number
        'number' => 'номер',
        // upload
        'file' => 'файл',
        'payment_unit_id' => '"стоимость за"',
        'vehicle_types_id' => '"вид техники"',
        'content' => "Контент",
        // offers
        'date_start'=>'Дата начала'
    ],

];
