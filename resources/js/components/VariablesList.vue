<template>
    <span>
        <div>
            <template v-for="(v, i) in items">
                <b-badge class="mr-2" href="#" :key="i" pill variant="info">
                    <span>{{ v }}</span>
                </b-badge>
            </template>
        </div>
    </span>
</template>

<script>
export default {
    name: 'VariablesList',
    props: {
        /** @type {{ new (): Array<String> }} */
        items: {
            type: Array,
            required: true
        }
    },
    methods: {
        insertVariable(contact) {
            this.$emit('insert', {...contact})
        }
    }
}
</script>
