import {acceptHMRUpdate, defineStore} from "pinia";
import {apiFetch} from "#imports";

export interface IVehicleCategory {
    color: string
    id: number
    image: string
    show_in_menu: boolean
    title: string
    groups: IVehicleGroup[]
}

export interface IVehicleGroup {
    color: string
    id: number
    vehicle_category_id: number
    image: string
    logo: string
    show_in_menu: boolean
    title: string
    types: IVehicleType[]
}

export interface IVehicleType {
    id: number
    vehicle_group_id: number
    title: string
    color: string
    image: string
    show_in_menu: boolean
}

export const useVehicleStore = defineStore("vehicle", () => {
    const getVehicleCategories = async (): Promise<IVehicleCategory[]> => {
        const {vehicleCategories} = await apiFetch('vehicle-categories')
        return vehicleCategories
    }
    return {
        getVehicleCategories
    };
});

if (import.meta.hot)
    import.meta.hot.accept(acceptHMRUpdate(useVehicleStore, import.meta.hot));
