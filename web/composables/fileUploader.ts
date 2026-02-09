import {apiFetch} from "~/composables/apiFetch";
import EventEmitter from "events";

export type FileType = "video" | "img" | "doc";

export interface IUploadedFile {
    url: string,
    type: FileType
    file_size: number,
    mime_type: string,
}

export default class FileUploader extends EventEmitter {
    file: File | Blob | undefined;
    CHUNK_SIZE = 1024 * 1024 * 6;
    uploaded = 0;
    chunks: Blob[] = [];
    loading = false;

    key: string | undefined;
    upload_id: string | undefined;

    constructor() {
        super()
    }

    uploadFile = async (file: File | Blob) => {
        this.file = file;
        if (file.size <= this.CHUNK_SIZE) {
            await this.regularUpload();
            return
        }
        this.uploaded = 0;
        await this.createMultipartUpload();


        await this.uploadParts();
    }

    createMultipartUpload = async () => {
        if (!this.file) return;
        this.loading = true;
        return apiFetch(`upload/multipart`, {
            method: 'post',
            body: {
                filename: this.file.name,
                file_type: this.file.type
            },
        }).then(body => {
            this.key = body.Key;
            this.upload_id = body.UploadId;
        }).catch(err => {
            console.log(err);
        });
    }

    async uploadParts() {
        if (!this.file) return;
        let chunks = Math.ceil(this.file.size / this.CHUNK_SIZE);
        for (let i = 0; i < chunks; i++) {
            let el = this.file.slice(
                i * this.CHUNK_SIZE, Math.min(i * this.CHUNK_SIZE + this.CHUNK_SIZE, this.file.size), this.file.type
            );
            this.chunks.push(el);
        }

        while (this.chunks.length !== 0) {
            console.log('fd', this.formData.get('PartNumber'), this.formData.get('Key'), this.formData.get('part'))
            await this.uploadPart();
        }

        await this.completeMultipartUpload();
    }

    uploadPart = async () => {
        const body = await apiFetch(`upload/multipart/${this.upload_id}`, {
            method: 'post',
            body: this.formData,
        });
        if (body.UploadId) {
            this.upload_id = body.UploadId;
        }
        if (body.Key) {
            this.key = body.Key;
        }

        this.uploaded++;
        this.chunks.shift();
    }

    completeMultipartUpload = async () => {
        const body = await apiFetch(`upload/multipart/${this.upload_id}?Key=${this.key}`);

        return apiFetch(`upload/multipart/${this.upload_id}/complete`, {
            method: "post",
            body: {
                Key: this.key,
                Parts: body.parts,
            },
        }).then(body => {
            this.loading = false;
            if (!this.file) return;

            this.emit('uploaded', {
                url: body.file.url,
                type: this.getFileType,
                file_size: this.file.size,
                mime_type: this.file.type,
            } as IUploadedFile);
        })
    }

    regularUpload = async () => {
        if (!this.file) return;
        const fd = new FormData;
        fd.append('file', this.file);
        this.loading = true;
        const body = await apiFetch(`upload`, {
            method: 'post',
            body: fd,
            processData: false,
            contentType: false,
        });

        this.emit('uploaded', {
            url: body.file.url,
            type: this.getFileType,
            file_size: this.file.size,
            mime_type: this.file.type,
        } as IUploadedFile);

        this.loading = false;
    }

    get getFileType(): FileType {
        if (!this.file) return 'doc';
        return this.file.type.includes('video') ? 'video' : this.file.type.includes('image') ? 'img' : 'doc';
    }

    get progress() {
        if (!this.file) return 0;
        return Math.floor((this.uploaded * 100) / this.file.size);
    }

    get formData() {
        let formData = new FormData;
        if (this.file) formData.append('part', this.chunks[0], `${this.file.name}`);
        formData.append('PartNumber', (this.uploaded + 1).toString());
        if (this.key) formData.append('Key', this.key);
        return formData;
    }
}
