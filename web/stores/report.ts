import {acceptHMRUpdate, defineStore} from "pinia";

export interface IStatus {
    label: string
    icon?: string
}

export const useReportStore = defineStore("report", () => {

    const statuses: any = {
        draft: {
            label: "Черновик"
        },
        active: {
            label: "Не подтверждена",
            icon:"time"
        },
        referee: {
            label: "Вызван арбитр",
            icon: "referee",
        },
        confirmed: {
            label: "Подтверждена",
            icon: "report-confirmed",
        },
        rejected: {
            label: "Отклонена"
        },
        resolved: {
            label: "Урегулирована",
            icon: "report-resolved",
        },
        canceled: {
            label: "Отозвана"
        },
    };

    const getStatusByKey = (status: string) => {
        return statuses[status];
    }

    return {
        getStatusByKey
    };
});

if (import.meta.hot)
    import.meta.hot.accept(acceptHMRUpdate(useReportStore, import.meta.hot));
