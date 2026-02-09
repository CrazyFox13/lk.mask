import store from '@/store'

export default class UploadAdapter {
    constructor(loader) {
        // The file loader instance to use during the upload.
        this.loader = loader;
    }

    // Starts the upload process.
    upload() {
        return this.loader.file
            .then(async file => {
                const {VUE_APP_API_ENDPOINT} = process.env

                const data = new FormData();
                data.append('file', file);

                const response = await fetch(`${VUE_APP_API_ENDPOINT}/api/upload`, {
                    method: "POST",
                    headers: {
                        Authorization: `Bearer ${store.state.token}`,
                    },
                    body: data,
                });

                const result = await response.json();
                return {
                    default: result.file.url,
                };
            });
    }
}