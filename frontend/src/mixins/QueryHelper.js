export default {
    methods: {
        setQueryString(searchParams) {
            Object.keys(searchParams).forEach(key => {
                if (searchParams[key] === undefined || searchParams[key] === null || searchParams[key] === "") {
                    delete searchParams[key]
                } else if (searchParams[key] === true) {
                    searchParams[key] = 1;
                } else if (searchParams[key] === false) {
                    searchParams[key] = 0;
                }
            });
            return new URLSearchParams(searchParams).toString();
        },
        optionsToQuery(opts) {
            return {
                page: opts.page,
                take: opts.itemsPerPage,
                sort_by: opts.sortBy[0],
                sort_desc: opts.sortDesc[0],
            };
        },
        queryToOptions(query) {
            const sort = [query.sort_by].filter(x => x);
            const desc = [query.sort_desc ? !!parseInt(query.sort_desc) : true];//.filter(x => x);
            return {
                page: parseInt(query.page),
                itemsPerPage: parseInt(query.take) ? parseInt(query.take) : 10,
                sortBy: sort.length ? sort : [],
                sortDesc: desc,
            };
        },
    }
}