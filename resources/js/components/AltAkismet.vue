<script setup>
import { Header, Button, Card, Pagination } from '@statamic/cms/ui';
import { router } from '@statamic/cms/inertia'
import { computed, ref } from 'vue';

const props = defineProps({
    blueprint: Array,
    meta: Array,
    values: Array,
    items: Array,
});

const perPage = 10;
const currentPage = ref(1);

const lastPage = computed(() => {
    return Math.ceil(itemsSliced.value.total / perPage);
});

const itemsSliced = computed(() => {
    let temp = props.items;

    const start = (currentPage.value - 1) * perPage;
    const end = start + perPage;

    return {
        total: temp.length,
        data: temp.slice(start, end)
    };
});

function setPage(page) {
    // If the page we were looking at has now been removed
    if(page > lastPage.value) {
        page = lastPage.value;
    }
    currentPage.value = page
}
   
function update(id, type) {
    const message = type === 'ham' ? 'Are you sure you want to mark this as ham (not spam)?' : 'Are you sure you want to report this as spam?';
    if (confirm(message)) {
        router.post(cp_url('/alt-design/alt-akismet/update'), {id, type}, {
            preserveState: "errors",
            preserveScroll: true, 
            onSuccess: () => {
                Statamic.$toast.success("Updated successfully!")
                setPage(currentPage.value);
            }
        });
    }
}
</script>

<template>
    <div id="alt-akismet">
        <Header :title="title">
            <template #title>
                <div>
                    Alt Akismet
                    <div class="text-sm">We basically try and guess which fields are which, so that you don't have to manually config each field. Below are the contents of the fields that we guessed, and the ham or spam result - we'll add a way of customising them too soon!</div>
                </div>
            </template>
        </Header>

        <Card class="overflow-hidden p-0">
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
                        <th class="group from-column sortable-column">
                            <span>Form</span>
                        </th>
                        <th class="group from-column sortable-column">
                            <span>Submission</span>
                        </th>
                        <th class="actions-column">
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="item in itemsSliced.data" :key="item.id">
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
                            <a class="text-blue-400 underline" :href="cp_url('forms/' + item.alt_form_slug)">{{ item.alt_form_slug }}</a>
                        </td>
                        <td>
                            <a v-if="item.alt_akismet == 'ham'" class="text-blue-400 underline" :href="cp_url('forms/' + item.alt_form_slug + '/submissions/' + item.alt_akismet_id)">View</a>
                            <a v-else class="text-blue-400 underline" :href="cp_url('alt-design/alt-akismet/submissions/' + item.alt_akismet_id)">View</a>
                        </td>
                        <td>
                            <Button v-if="item.alt_akismet == 'ham'" @click="update(item.alt_akismet_id, 'spam')" size="sm" variant="danger" text="Report Spam" />
                            <Button v-else @click="update(item.alt_akismet_id, 'ham')" size="sm" variant="primary" text="Report Ham" />
                        </td>
                    </tr>
                </tbody>
            </table>
            <Pagination :resource-meta="{
                current_page: currentPage,
                last_page: lastPage,
                total: itemsSliced.total
            }" :show-totals="false" :show-per-page-selector="false" @page-selected="setPage" />
        </Card>
    </div>
</template>