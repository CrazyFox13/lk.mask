export default {
    props: ['value'],
    data() {
        return {
            input: this.value,
        }
    },
    watch: {
        input: {
            handler() {
                this.$emit("input", this.input)
            }, deep: true
        }
    }
}