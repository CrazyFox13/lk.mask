export default {
    data() {
        return {
            companyModerationStatuses: [
                {key: 'draft', value: 'Новый', color: ''},
                {key: 'moderation', value: 'На модерации', color: 'warning'},
                {key: 'approved', value: 'Подтверждено', color: 'success'},
                {key: 'canceled', value: 'Отклонено', color: 'error'},
            ],
            orderModerationStatuses: [
                {key: 'draft', value: 'Черновик', color: ''},
                {key: 'moderation', value: 'На модерации', color: 'warning'},
                {key: 'on_approval', value: 'На согласовании', color: 'info'},
                {key: 'approved', value: 'Новая заявка', color: 'success'},
                {key: 'in_progress', value: 'В работе', color: 'primary'},
                {key: 'canceled', value: 'Отменённая', color: 'error'},
                {key: 'removed', value: 'Снято с публикации', color: 'secondary'},
                {key: 'completed', value: 'Завершённая', color: 'secondary'},
            ],
            reportModerationStatuses: [
                {key: 'draft', value: 'Новый', color: 'warning'},
                {key: 'active', value: 'В работе', color: 'info'},
                {key: 'referee', value: 'Вызван арбитр', color: 'warning'},
                {key: 'confirmed', value: 'Подтверждено', color: 'primary'},
                {key: 'rejected', value: 'Отклонено', color: 'error'},
                {key: 'resolved', value: 'Решено', color: 'success'},
                {key: 'canceled', value: 'Отозванно', color: ''}
            ],
            recommendationModerationStatuses: [
                {key: 'draft', value: 'На модерации', color: 'warning'},
                {key: 'viewed', value: 'Просмотрено', color: 'info'},
                {key: 'approved', value: 'Подтверждено', color: 'success'},
                {key: 'canceled', value: 'Отклонено', color: ''},
                {key: 'deleted', value: 'Удалено', color: 'secondary'}
            ],
            claimModerationStatuses: [
                {key: 'draft', value: 'На модерации', color: 'warning'},
                {key: 'viewed', value: 'Просмотрено', color: 'info'},
                {key: 'approved', value: 'Подтверждено', color: 'success'},
                {key: 'canceled', value: 'Отклонено', color: ''}
            ],
            formQuestionTypes: [
                {
                    key: 'radio',
                    label: 'Одиночный выбор',
                    options: true,
                },
                {
                    key: 'select',
                    label: 'Выбор из списка',
                    options: true,
                },
                {
                    key: 'checkbox',
                    label: 'Чекбокс',

                },
                {
                    key: 'file',
                    label: 'Файл',
                },
                {
                    key: 'integer',
                    label: 'Целое число',
                },
                {
                    key: 'float',
                    label: 'Дробное число',
                },
                {
                    key: 'range',
                    label: 'Диапазон',
                },
                {
                    key: 'text',
                    label: 'Текст',
                },
                {
                    key: 'link',
                    label: 'Ссылка',
                },
                {
                    key: 'date',
                    label: 'Дата',
                },
                {
                    key: 'datetime',
                    label: 'Дата и время',
                },
                {
                    key: 'date_range',
                    label: 'Диапазон дат',
                },
                {
                    key: 'security',
                    label: 'Охрана',
                    default_label: true,
                },
                {
                    key: 'living',
                    label: 'Проживание',
                    default_label: true,
                },
                {
                    key: 'orv',
                    label: 'Вездеход',
                    default_label: true,
                },
            ],
            pushStatuses: [
                {key: "draft", value: "Черновик", color: 'warning'},
                {key: "scheduled", value: "Запланировано", color: 'info'},
                {key: "sending", value: "Отправляется", color: 'info'},
                {key: "sent", value: "Отправлено", color: 'success'},
                {key: "paused", value: "Приостановлено", color: 'secondary'},
            ],
            faqCategories: [
                {'path': '/', 'label': 'О сервисе'},
                {'path': '/get-started', 'label': 'Начало работы'},
                {'path': '/to-customers', 'label': 'Заказчикам'},
                {'path': '/to-companies', 'label': 'Исполнителям'},
                {'path': '/rating', 'label': 'Рейтинги',}
            ]
        }
    },
    methods: {
        companyStatusLabelText(key) {
            return this.companyModerationStatuses.find(i => i.key === key)?.value;
        },
        companyStatusLabelColor(key) {
            return this.companyModerationStatuses.find(i => i.key === key)?.color;
        },
        orderStatusLabelText(key) {
            return this.orderModerationStatuses.find(i => i.key === key)?.value;
        },
        orderStatusLabelColor(key) {
            return this.orderModerationStatuses.find(i => i.key === key)?.color;
        },
        reportStatusLabelText(key) {
            return this.reportModerationStatuses.find(i => i.key === key)?.value;
        },
        reportStatusLabelColor(key) {
            return this.reportModerationStatuses.find(i => i.key === key)?.color;
        },
        recommendationStatusLabelText(key) {
            return this.recommendationModerationStatuses.find(i => i.key === key)?.value;
        },
        recommendationStatusLabelColor(key) {
            return this.recommendationModerationStatuses.find(i => i.key === key)?.color;
        },
        pushStatusLabelText(key) {
            return this.pushStatuses.find(i => i.key === key)?.value;
        },
        pushStatusLabelColor(key) {
            return this.pushStatuses.find(i => i.key === key)?.color;
        },

    }
}