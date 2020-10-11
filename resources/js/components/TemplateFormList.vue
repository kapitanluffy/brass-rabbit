<template>
    <span>
        <ul class="list-group">
            <template v-for="(v, i) in items">
                <li href="#" :key="i" class="list-group-item d-flex">
                    <span class="flex-grow-1">
                        <h6>{{ v.subject }}</h6>

                        <small class="text-muted">{{ v.message.substr(0, 100) + `...` }}</small>
                    </span>

                    <span class="pull-right">
                        <b-button size="sm" variant="primary" @click="editTemplate(v)">
                            Edit
                        </b-button>
                        <b-button size="sm" variant="danger" @click="deleteTemplate(v)">
                            Delete
                        </b-button>
                    </span>
                </li>
            </template>
        </ul>
    </span>
</template>

<script>
export default {
    name: 'TemplateFormList',
    props: {
        /** @type {{ new (): Array<{ subject: String, message: String }> }} */
        items: {
            type: Array,
            required: true
        }
    },
    methods: {
        deleteTemplate(template) {
            console.log(template);
            this.$emit('delete', {...template})
        },
        editTemplate(template) {
            this.$emit('edit', {...template})
        }
    }
}
</script>
