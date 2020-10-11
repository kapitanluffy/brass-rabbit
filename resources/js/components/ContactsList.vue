<template>
    <span>
        <ul class="list-group">
            <template v-for="(v, i) in items">
                <li href="#" :key="i" class="list-group-item d-flex">
                    <span class="flex-grow-1">{{ v.email }}</span>

                    <span class="pull-right">
                        <b-button size="sm" variant="success" @click="previewTemplate(v)" v-if="enablePreview">
                            Preview
                        </b-button>
                        <b-button size="sm" variant="info" @click="onPreviewData(v)">
                            Data
                        </b-button>
                    </span>
                </li>
            </template>
        </ul>

        <b-modal id="previewModal" centered ok-only ok-title="Close"
            :title="`Data Preview`"
            :visible="previewData !== null"
            @hidden="(e) => this.previewData = null"
            >
            <table class="table">
                <tbody>
                    <tr v-for="(v, i) in previewData" :key="i">
                        <td>{{ i }}</td>
                        <td>{{ v || "-- none --" }}</td>
                    </tr>
                </tbody>
            </table>
        </b-modal>
    </span>
</template>

<script>
export default {
    name: 'ContactsList',
    props: {
        /** @type {{ new (): Array<{ email: String }> }} */
        items: {
            type: Array,
            required: true
        },
        enablePreview: {
            type: Boolean,
            default: () => false
        }
    },
    data() {
        return {
            previewData: null
        }
    },
    methods: {
        previewTemplate(contact) {
            this.$emit('preview-template', {...contact})
        },
        onPreviewData(contact) {
            this.previewData = {...contact.data}
            this.$emit('preview-data', {...contact.data})
        }
    }
}
</script>
