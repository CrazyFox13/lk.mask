import {acceptHMRUpdate, defineStore} from "pinia";
import moment from "moment";
import {apiFetch, objToQuery, ref, useCookie, useRuntimeConfig, ElMessage} from "#imports";
import {ICompany, IUser, useAuthStore} from "~/stores/user";

export interface IOrderFilter {
    cities_id: number[];
    shifts: string
    date: Date | undefined
    amount_by_agreement: boolean
    amount_with_vat: boolean
    amount_cash: boolean
    for_company_vehicles?: number
    with_company: boolean
    favorite: boolean
    regions_id?: number[];
}

export interface IOrderOfferDocument {
    id?: number
    order_offer_id: number
    type: "print_form" | "signed_document" | "application" | "invoice"
    url: string
    file_name?: string
    file_size?: number
    mime_type?: string
}

export interface IOrderOffer {
    id?: number
    order_id: number
    comment?: string
    amount_account_vat?: number
    amount_account?: number
    amount_cash?: number
    date_start?: string
    viewed_at?: string
    status: "waiting" | "accepted" | "declined"
    decline_reason?: string
    created_at: string
    company?: ICompany,
    user?: IUser
    documents?: IOrderOfferDocument[]
    print_form?: IOrderOfferDocument
    signed_document?: IOrderOfferDocument
    application?: IOrderOfferDocument
    invoice?: IOrderOfferDocument
}

export const useOrderStore = defineStore("order", () => {
    const defaultFilters = ref({
        cities_id: [],
        shifts: '',
        date: undefined,
        amount_by_agreement: false,
        amount_with_vat: false,
        amount_cash: false,
        with_company: false,
        favorite: false,
    });

    // Глобальное хранилище для принятых offers (чтобы статус не слетал при пересоздании компонентов)
    const acceptedOffers = ref<Set<number>>(new Set());

    const shiftsCount = (startDate: string, endDate: string) => {
        return moment(endDate).diff(moment(startDate), 'days') + 1;
    }

    const markerIndexes = ref(['А', 'Б', 'В', 'Г', 'Д']);

    const fetchFormQuestions = async (vehicleGroupId: number, vehicleTypeId: number) => {
        const {formQuestions} = await apiFetch(`vehicle-groups/${vehicleGroupId}/vehicle-types/${vehicleTypeId}/form-questions`);
        return formQuestions
    }

    const getOffers = async (order_id: number, filters: any = {}, page = 1, take = 10) => {
        const q = objToQuery(filters)
        const response = await apiFetch(`orders/${order_id}/order-offers?take=${take}&page=${page}&${q}`);
        return {
            offers: response.orderOffers,
            totalCount: response.totalCount,
            pagesCount: response.pagesCount,
        };
    }
    const acceptOffer = async (offer: IOrderOffer) => {
        // Сохраняем ID принятого offer в глобальное хранилище
        if (offer.id) {
            acceptedOffers.value.add(offer.id);
        }
        await apiFetch(`orders/${offer.order_id}/order-offers/${offer.id}/accept`, {
            method: "POST"
        });
    }

    const undoAcceptOffer = async (offer: IOrderOffer) => {
        // Удаляем ID offer из глобального хранилища
        if (offer.id) {
            acceptedOffers.value.delete(offer.id);
        }
        await apiFetch(`orders/${offer.order_id}/order-offers/${offer.id}/undo-accept`, {
            method: "POST"
        });
    }


    const declineOffer = async (offer: IOrderOffer, declineReason: string) => {
        if (!declineReason) {
            throw new Error('Причина отказа обязательна');
        }
        await apiFetch(`orders/${offer.order_id}/order-offers/${offer.id}/decline`, {
            method: "POST",
            body: { decline_reason: declineReason }
        });
    }

    const revertOffer = async (offer: IOrderOffer) => {
        await apiFetch(`orders/${offer.order_id}/order-offers/${offer.id}/revert`, {
            method: "POST"
        });
    }

    const downloadPrintForm = async (offer: IOrderOffer) => {
        try {
            const config = useRuntimeConfig();
            const base = typeof window !== 'undefined' ? '' : (config.public.baseURL || 'http://localhost:5000').replace(/\/$/, '');
            const url = base ? `${base}/api/orders/${offer.order_id}/order-offers/${offer.id}/application` : `/api/orders/${offer.order_id}/order-offers/${offer.id}/application`;

            const authStore = useAuthStore();
            const userSession = useCookie('user-session');

            const response = await fetch(url, {
                headers: {
                    Authorization: `Bearer ${authStore.getToken()}`,
                    Accept: 'text/html',
                    UserSession: userSession.value || '',
                },
            });

            if (!response.ok) {
                let errorMessage = `HTTP error! status: ${response.status}`;
                try {
                    const errorData = await response.json();
                    errorMessage = errorData.message || errorData.error || errorMessage;
                } catch (e) {
                    //
                }
                throw new Error(errorMessage);
            }

            const html = await response.text();
            const blob = new Blob([html], { type: 'text/html; charset=UTF-8' });
            const blobUrl = URL.createObjectURL(blob);
            const newWindow = window.open(blobUrl, '_blank');

            if (!newWindow) {
                ElMessage.warning('Пожалуйста, разрешите открытие всплывающих окон для просмотра заявки');
                const downloadLink = document.createElement('a');
                downloadLink.href = blobUrl;
                downloadLink.download = `application-${offer.order_id}.html`;
                document.body.appendChild(downloadLink);
                downloadLink.click();
                document.body.removeChild(downloadLink);
            }

            setTimeout(() => URL.revokeObjectURL(blobUrl), 1000);
        } catch (error: any) {
            console.error('Ошибка при загрузке заявки:', error);
            ElMessage.error(error?.body?.message || error?.message || 'Не удалось загрузить заявку');
        }
    }

    const uploadSignedDocument = async (offer: IOrderOffer, fileData: any) => {
        const response = await apiFetch(`orders/${offer.order_id}/order-offers/${offer.id}/upload-signed-document`, {
            method: "POST",
            body: {
                url: fileData.url,
                file_name: fileData.name || 'signed-document.pdf',
                file_size: fileData.file_size,
                mime_type: fileData.mime_type
            }
        });
        return response.document;
    }

    const downloadDocument = async (offer: IOrderOffer, documentId: number) => {
        const url = `/api/orders/${offer.order_id}/order-offers/${offer.id}/documents/${documentId}/download`;
        window.open(url, '_blank');
    }

    const previewDocument = async (offer: IOrderOffer, documentId: number) => {
        const url = `/api/orders/${offer.order_id}/order-offers/${offer.id}/documents/${documentId}/preview`;
        window.open(url, '_blank');
    }

    const setOrderInProgress = async (offer: IOrderOffer) => {
        await apiFetch(`orders/${offer.order_id}/order-offers/${offer.id}/set-in-progress`, {
            method: "POST"
        });
    }

    const downloadInvoice = async (offer: IOrderOffer) => {
        try {
            const config = useRuntimeConfig();
            const base = typeof window !== 'undefined' ? '' : (config.public.baseURL || 'http://localhost:5000').replace(/\/$/, '');
            const url = base ? `${base}/api/orders/${offer.order_id}/order-offers/${offer.id}/invoice` : `/api/orders/${offer.order_id}/order-offers/${offer.id}/invoice`;
            
            const authStore = useAuthStore();
            const userSession = useCookie('user-session');
            
            // Используем нативный fetch для получения PDF
            const response = await fetch(url, {
                headers: {
                    Authorization: `Bearer ${authStore.getToken()}`,
                    Accept: 'application/pdf',
                    UserSession: userSession.value || '',
                },
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const blob = await response.blob();
            const blobUrl = URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.href = blobUrl;
            link.download = `invoice-${offer.order_id}.pdf`;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            
            // Освобождаем память после загрузки
            setTimeout(() => URL.revokeObjectURL(blobUrl), 1000);
        } catch (error: any) {
            console.error('Ошибка при загрузке счета:', error);
            ElMessage.error(error?.body?.message || error?.message || 'Не удалось загрузить счет');
        }
    }

    return {
        defaultFilters,
        shiftsCount,
        markerIndexes,
        fetchFormQuestions,
        getOffers,
        acceptOffer,
        declineOffer,
        undoAcceptOffer,
        revertOffer,
        acceptedOffers,
        downloadPrintForm,
        uploadSignedDocument,
        downloadDocument,
        previewDocument,
        setOrderInProgress,
        downloadInvoice
    };
});

if (import.meta.hot)
    import.meta.hot.accept(acceptHMRUpdate(useOrderStore, import.meta.hot));
