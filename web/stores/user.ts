import {acceptHMRUpdate, defineStore} from "pinia";
import {apiFetch, ref, useCookie} from "#imports";

export interface IGeoRegion {
    id: number;
    federal_district: string;
    fias_id: string;
    name: string;
    name_with_type: string;
    timezone: string;
    type: string;
    geoname_id: string;
    cities?: IGeoCity[];
}

export interface IGeoCity {
    id: number;
    geo_region_id: number;
    name: string;
    postal_code: string;
    timezone: string;
    lat: number;
    lng: number;
    fias_id: string;
    region?: IGeoRegion
}

export interface ISignInEmailCredentials {
    email: string;
    password: string
    remember?: boolean;
}

export interface ISignInPhoneCredentials {
    phone: string;
    password: string;
    captcha_token: string;
    remember?: boolean;
}

export interface ISendPhoneCodeCredentials {
    phone: string;
    captcha_token: string;
}

export interface IEmailResetRequest {
    email: string;
}

export interface IConfirmPhoneCredentials {
    phone: string
    phone_code: string
}

export interface IConfirmPhoneOptions {
    reset_password: boolean
}

export interface ISetPasswordRequest {
    password: string
    password_confirmation: string
}

export interface IUpdatePasswordRequest {
    old_password: string
    password: string
    password_confirmation: string
}

export interface IUser {
    id: number,
    name: string
    surname: string
    phone: string
    email: string
    temp_password: boolean
    avatar: string | null
    rating: number
    last_online_datetime: Date
    company_id: number | null
    company_role: null | 'boss' | 'user'
    company?: ICompany
    city_id?: number
    geo_city_id?: number
    city?: IGeoCity
    region_id: number
    approved_recommendations_count?: number
    active_reports_count?: number
    silence: number
    silence_from: number
    silence_from_m: number
    silence_to: number
    silence_to_m: number
    allowed_to_show_contacts: boolean
}

export interface ICompany {
    id?: number
    company_type_id?: number
    inn: string
    title: string
    full_title: string
    ogrn: string
    kpp: string
    okpo: string
    legal_address: string
    address: string
    director: string
    phone: string
    email: string
    website: string
    description: string
    rating?: number
    reg_number: string
    documents: any[]
    moderation_status?: string
    moderation_message?: string
    vehicle_types_id?: number[]
    boss?: IUser
}

export const useAuthStore = defineStore("auth", () => {
    const jwtCookie = useCookie('jwt', {})
    const jwt = ref<string | null | undefined>(jwtCookie.value);
    const user = ref<IUser>();
    const geoCity = ref<IGeoCity>();
    const geoCityConfirmed = ref(false);

    function askLocationPermission() {
        return new Promise((resolve, reject) => {
            navigator.geolocation.getCurrentPosition(resolve, reject)
        })
    }

    async function getGeoData() {
        const savedCity = getGeoCity();
        if (savedCity) {
            setGeoCity(savedCity);
            return;
        }
        askLocationPermission().then(async (position: any) => {
            const {city} = await apiFetch(`geo-by-ip?lat=${position.coords.latitude}&lng=${position.coords.longitude}`);
            setGeoCity(city)
        }).catch(async () => {
            const {cities} = await apiFetch(`geo-cities`);
            setGeoCity(cities[0])
        })
    }

    function setGeoCity(city: IGeoCity) {
        geoCity.value = city;
        localStorage.setItem('geoCity', JSON.stringify(city));
    }

    function getGeoCity() {
        const savedData = localStorage.getItem('geoCity');
        if (savedData) {
            geoCityConfirmed.value = !!localStorage.getItem('geoCityConfirmed');
            return JSON.parse(savedData);
        }
    }

    function confirmCity() {
        localStorage.setItem('geoCityConfirmed', 'true');
        geoCityConfirmed.value = true;
    }

    async function signUp(credentials: ISendPhoneCodeCredentials) {
        return await apiFetch('auth/register', {
            method: 'post',
            body: credentials
        });
    }

    async function signInEmail(credentials: ISignInEmailCredentials) {
        return await apiFetch('auth/login/password', {
            method: 'post',
            body: credentials
        });
    }

    async function signInPhone(credentials: ISignInPhoneCredentials) {
        const body = await apiFetch('auth/login/password', {
            method: 'post',
            body: credentials
        });
        setToken(body.access_token);
        return body;
    }

    async function sendEmailConfirmation(email: string) {
        return await apiFetch('auth/send-email-code', {
            method: 'post',
            body: {
                email: email,
            }
        });
    }

    async function sendPhoneCode(credentials: ISendPhoneCodeCredentials) {
        return await apiFetch('auth/login', {
            method: 'post',
            body: credentials
        });
    }

    async function confirmPhoneCode(credentials: IConfirmPhoneCredentials, opts?: IConfirmPhoneOptions) {
        const body = await apiFetch('auth/validate-phone-code', {
            method: 'post',
            body: {...credentials, ...opts}
        });
        setToken(body.access_token);
        return body;
    }

    async function setPassword(request: ISetPasswordRequest) {
        return await apiFetch('auth/set-password', {
            method: 'post',
            body: request
        });
    }

    async function resetPasswordByEmail(request: IEmailResetRequest) {
        return await apiFetch('auth/reset-email', {
            method: 'post',
            body: request
        });
    }

    async function authByHash(hash: string, email: string) {
        const body = await apiFetch('auth/auth-by-hash', {
            method: 'post',
            body: {
                hash: hash,
                email: email,
            }
        });
        setToken(body.access_token);
        return body;
    }

    function setToken(token: string) {
        jwt.value = token;
        jwtCookie.value = token;
    }

    function getToken() {
        return jwt.value;
    }

    function removeToken() {
        jwtCookie.value = null;
        jwt.value = null;
    }

    async function getUser() {
        try {
            const response = await apiFetch('auth/me?is_web=1', {}, true)
            user.value = response.user;
            return response.user;
        } catch ({body}) {
            removeToken();
        }
    }

    async function setProfile(request: IUser) {
        return await apiFetch('auth/profile', {
            method: 'post',
            body: request
        });
    }


    async function setAvatar(url: string) {
        await apiFetch(`auth/avatar`, {
            method: "POST",
            body: {
                avatar: url
            }
        });
        user.value!.avatar = url;
    }

    function noApprovedCompany() {
        const company = user.value!.company;
        return !company || company.moderation_status !== 'approved';
    }

    function logout() {
        removeToken();
        user.value = undefined;
    }

    return {
        jwt,
        user,
        geoCity,
        geoCityConfirmed,
        getGeoData,
        confirmCity,
        setGeoCity,
        sendPhoneCode,
        confirmPhoneCode,
        getToken,
        getUser,
        signUp,
        signInEmail,
        signInPhone,
        setPassword,
        resetPasswordByEmail,
        authByHash,
        setAvatar,
        setProfile,
        sendEmailConfirmation,
        noApprovedCompany,
        logout
    };
});

if (import.meta.hot)
    import.meta.hot.accept(acceptHMRUpdate(useAuthStore, import.meta.hot));
