<template>
    <div id="alt-akismet">

    <publish-form
        :title="title"
        :action="action"
        :meta="meta"
        :values="values"
    ></publish-form>

    <div class="card overflow-hidden p-0">
        <table data-size="sm" tabindex="0" class="data-table">
            <thead>
            <tr>
                <th class="group from-column sortable-column">
                    <span>Name</span>
                </th>
                <th class="group to-column pr-8">
                    <span>Email</span>
                </th>
                <th class="group to-column pr-8">
                    <span>Content</span>
                </th>
                <th class="actions-column">
                </th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="item in itemsSliced" :key="item.id">
                <td>
                    {{ item.alt_akismet_name }}
                </td>
                <td>
                    {{ item.alt_akismet_email }}
                </td>
                <td>
                    {{ item.alt_akismet_content }}
                </td>
                <td>
                    <button v-if="item.alt_akismet == 'ham'" @click="update(item.id, 'spam')" class="btn" style="color: red;">Report Spam </button>
                    <button v-else @click="update(item.id, 'ham')" class="btn" style="color: green;">Report Ham</button>
                </td>
            </tr>
            </tbody>
        </table>

        <div class="pagination py-2">
            <!-- Generate numbers based on total left -->
            <span class="cursor-pointer py-1 px-2 border mx-1" :class="{'font-bold': n == currentPage}" v-for="n in Math.ceil(totalItems / perPage)" :key="n" @click="setPage(n)">{{ n }}</span>
        </div>
    </div>
    </div>
</template>

<script>
export default({
    props: {
        title: String,
        action: String,
        blueprint: Array,
        meta: Array,
        redirectTo: String,
        values: Array,
        data: Array,
        items: Array,
    },
    data() {
        return {
            itemsReady: [],
            itemsSliced: [],
            perPage: 10,
            currentPage: 1,
            totalItems: 0,
        }
    },
    mounted() {
        this.itemsReady = this.items
        this.totalItems = this.items.length
        this.sliceItems()
        console.log(this.itemsSliced)
    },
    methods: {
        updateItems(res) {
            this.itemsReady = res.data.data
            this.totalItems = res.data.data.length
            this.sliceItems()
            this.$forceUpdate()
        },
        setPage(page) {
            this.currentPage = page
            this.sliceItems()
        },
        sliceItems() {
            const start = (this.currentPage - 1) * this.perPage
            const end = start + this.perPage
            console.log(this.currentPage)
            this.itemsSliced = this.itemsReady.slice(start, end)
        },
        update(id, type) {
            console.log(id, type)
            if (confirm('Are you sure you want to report this as ham?')) {
                Statamic.$axios.post(cp_url('alt-design/alt-akismet/update'), {
                    id: id,
                    type: type,
                }).then(res => {
                    this.updateItems(res)
                })
                    .catch(err => {
                        console.log(err)
                    })
            }
        },
    }
})
</script>
