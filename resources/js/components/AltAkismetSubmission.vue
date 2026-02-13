<template>
    <div>
        <PublishContainer
            v-model="formValues"
            :blueprint="blueprint"
            :meta="meta"
            :read-only="true"
        />
    </div>
</template>

<script>
import { PublishContainer } from '@statamic/cms/ui';

export default {
    components: { PublishContainer },
    props: {
        blueprint: {
            type: [Array, Object],
            required: true,
        },
        meta: {
            type: [Array, Object],
            default: () => ({}),
        },
        values: {
            type: [Array, Object],
            default: () => ({}),
        },
    },
    data() {
        return {
            formValues: this.values != null ? (Array.isArray(this.values) ? [...this.values] : { ...this.values }) : {},
        };
    },
};
</script>
