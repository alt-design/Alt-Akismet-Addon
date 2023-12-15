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

        <div class="pagination text-sm py-4 px-4 flex items-center justify-between">
            <div class="w-1/3 flex items-center">
                Page <span class="font-semibold mx-1" v-html="currentPage"></span> of <span class="mx-1" v-html="lastPage"></span>
            </div>
            <div class="w-1/3 flex items-center justify-center">
                    <span style="height: 15px; margin: 0 15px; width: 12px;" class="cursor-pointer" @click="setPage(currentPage - 1 > 0 ? currentPage - 1 : 1)">
                        <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="205" height="205" viewBox="0 0 205 205"><defs><clipPath id="clip-LEFT"><rect width="205" height="205"/></clipPath></defs><g id="LEFT" clip-path="url(#clip-LEFT)"><rect width="205" height="205" fill="#fff"/><path stroke="#2e9fff" fill="#2e9fff" id="Icon_awesome-arrow-left" data-name="Icon awesome-arrow-left" d="M114.961,184.524l-9.91,9.91a10.669,10.669,0,0,1-15.132,0L3.143,107.7a10.669,10.669,0,0,1,0-15.132L89.919,5.794a10.669,10.669,0,0,1,15.132,0l9.91,9.91a10.725,10.725,0,0,1-.179,15.311L60.994,82.259H189.283A10.687,10.687,0,0,1,200,92.972v14.284a10.687,10.687,0,0,1-10.713,10.713H60.994l53.789,51.244A10.648,10.648,0,0,1,114.961,184.524Z" transform="translate(2.004 2.353)"/></g></svg>
                    </span>
                <!-- First Page -->
                <span v-if="currentPage > 1" class="cursor-pointer py-1 mx-1"
                      @click="setPage(1)">1</span>
                <span v-if="currentPage == 1" class="cursor-pointer py-1 mx-1 font-semibold"
                      @click="setPage(1)">1</span>

                <!-- Ellipsis for Previous Pages -->
                <span v-if="currentPage > 3">...</span>

                <!-- Previous Page -->
                <span v-if="currentPage > 2" class="cursor-pointer py-1 mx-1"
                      @click="setPage(currentPage - 1)">{{ currentPage - 1 }}</span>

                <!-- Current Page (not shown if it's the first or last page) -->
                <span v-if="currentPage !== 1 && currentPage !== lastPage"
                      class="cursor-pointer py-1 mx-1 font-semibold">{{ currentPage }}</span>

                <!-- Next Page -->
                <span v-if="currentPage < lastPage - 1" class="cursor-pointer py-1 mx-1"
                      @click="setPage(currentPage + 1)">{{ currentPage + 1 }}</span>

                <!-- Ellipsis for Next Pages -->
                <span v-if="currentPage < lastPage - 2">...</span>

                <!-- Last Page -->
                <span v-if="currentPage < lastPage" class="cursor-pointer py-1 mx-1"
                      @click="setPage(lastPage)">{{ lastPage }}</span>
                <span v-if="currentPage == lastPage && lastPage != 1"
                      class="cursor-pointer py-1 mx-1 font-semibold"
                      @click="setPage(lastPage)">{{ lastPage }}</span>
                <span style="height: 15px; margin: 0 15px; width: 12px;" class="cursor-pointer" @click="setPage(currentPage + 1 < lastPage ? currentPage + 1 : lastPage)">
                        <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="205" height="205" viewBox="0 0 205 205"><defs><clipPath id="clip-RIGHT"><rect width="205" height="205"/></clipPath></defs><g id="RIGHT" clip-path="url(#clip-RIGHT)"><rect width="205" height="205" fill="#fff"/><path stroke="#2e9fff" fill="#2e9fff" id="Icon_awesome-arrow-left" data-name="Icon awesome-arrow-left" d="M85.032,184.524l9.91,9.91a10.669,10.669,0,0,0,15.132,0L196.85,107.7a10.669,10.669,0,0,0,0-15.132L110.073,5.794a10.669,10.669,0,0,0-15.132,0l-9.91,9.91a10.725,10.725,0,0,0,.179,15.311L139,82.259H10.71A10.687,10.687,0,0,0,0,92.972v14.284A10.687,10.687,0,0,0,10.71,117.969H139L85.21,169.214A10.648,10.648,0,0,0,85.032,184.524Z" transform="translate(2.004 2.353)"/></g></svg>
                    </span>
            </div>
            <div class="w-1/3 flex justify-end">
                <select v-model="selectedPage" @change="dropdownPageChange" class="w-1/2 text-sm">
                    <option value="" disabled>Select Page</option>
                    <option v-for="n in lastPage" :key="n" :value="n">{{ n }}</option>
                </select>
            </div>
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
    computed: {
        lastPage() {
            return Math.ceil(this.totalItems / this.perPage);
        }
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
