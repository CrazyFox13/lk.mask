import {acceptHMRUpdate, defineStore} from "pinia";
import moment from "moment";

export interface ICompanyFilter {
    cities_id: number[];
    rating: number | null
}

export const useCompanyStore = defineStore("company", () => {
    const defaultFilters = ref({
        cities_id: [],
        rating: null
    });

    return {
        defaultFilters,
    };
});

if (import.meta.hot)
    import.meta.hot.accept(acceptHMRUpdate(useCompanyStore, import.meta.hot));
