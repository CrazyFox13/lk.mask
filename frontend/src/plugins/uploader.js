import UploadAdapter from "./uploadAdapter";

const uploader = function (editor) {
    editor.plugins.get('FileRepository').createUploadAdapter = (loader) =>
        new UploadAdapter(loader);
};

export default uploader;