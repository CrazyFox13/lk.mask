import moment from "moment";
import Swal from "sweetalert2-khonik";

export default {
    data() {
        return {
            headers: [],
            items: [],
            options: {},
            totalItems: 0,
            loading: true,
            query: {},
            errors: {},
            moment: moment,
            editItem: undefined,
            editDialog: false,

            resourceKey: "",
            resourceApiRoute: "",
            resourceApiParams: "",
            deleteSwalTitle: "Вы уверены?"
        }
    },
    watch: {
        options(v) {
            this.query = this.copyObject({...this.query, ...this.optionsToQuery(v)});
            this.$nextTick(() => {
                this.replaceRoute();
            });
        },
        "$route": {
            handler() {
                this.readRoute();
            }, deep: true
        },
    },
    mounted() {
        this.readRoute();
    },
    methods: {
        truncate(str, n) {
            return (str.length > n) ? str.slice(0, n - 1) + '&hellip;' : str;
        },
        search() {
            this.options.page = 1;
            this.replaceRoute();
        },
        readRoute() {
            this.query = this.$route.query;
            this.modifyQuery();
            this.options = this.copyObject({...this.options, ...this.queryToOptions(this.query)});
            this.$nextTick(() => {
                this.getItems();
            })
        },
        modifyQuery() {
            // implement if need
        },
        replaceRoute() {
            this.$router.replace(`${this.$route.path}?${this.setQueryString(this.query)}`).catch(() => {
            });
        },
        getItems() {
            this.$http.get(`${this.resourceApiRoute}?${this.resourceApiParams}&${this.setQueryString(this.query)}`).then(r => {
                this.items = r.body[this.resourceKey];
                this.totalItems = r.body.totalCount;
                this.loading = false;
            })
        },
        create() {
            this.editItem = {};
            this.$nextTick(() => {
                this.editDialog = true;
            });
        },
        onCreated() {
            this.getItems();
        },
        onUpdated(resource) {
            let item = this.items.find(i => i.id === resource.id);
            if (item) {
                item = {...item, ...resource};
            }
        },
        edit(item) {
            this.editItem = item;
            this.$nextTick(() => {
                this.editDialog = true;
            })
        },
        destroy(item, soft = true) {
            Swal.fire({
                title: this.deleteSwalTitle,
                showDenyButton: soft,
                denyButtonText: `Удалить навсегда`,
                showCancelButton: true,
                cancelButtonText: 'Отменить',
                showConfirmButton: true,
                confirmButtonText: 'Удалить',
                showCloseButton: false,
            }).then(({isDismissed, isDenied}) => {
                /* Read more about isConfirmed, isDenied below */
                if (isDismissed) return;
                this.$http.delete(`${this.resourceApiRoute}/${item.id}?force=${isDenied ? 1 : 0}`).then(() => {
                    this.items.splice(this.items.indexOf(item), 1);
                })
            })
        },
    }
}