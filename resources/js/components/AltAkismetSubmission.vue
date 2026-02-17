<script setup>
import { Header, PublishContainer } from '@statamic/cms/ui';

const props = defineProps({
    blueprint: Array,
    meta: Array,
    values: Array,
    data: Array,
    items: Array,
});

const formValues = (props.values != null ? (Array.isArray(props.values) ? [...props.values] : { ...props.values }) : {});
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

        <PublishContainer :blueprint="blueprint" :meta="meta" v-model="formValues" :read-only="true" />
    </div>
</template>