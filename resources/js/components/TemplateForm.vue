<template>
    <span>
        <div>
            <b-button v-b-modal.templateFormModal variant="primary">Add Template</b-button>
        </div>

        <b-modal id="templateFormModal" title="Save Template" @hidden="(e) => this.$emit('hidden', e)" @ok="saveTemplate" :visible="visible">
            <form>
                <div class="form-group">
                    <label for="subject">Subject</label>
                    <input type="text" class="form-control" id="subject" v-model="subject">
                </div>
                <div class="form-group">
                    <label for="message">Message</label>
                    <textarea name="message" class="form-control" id="message" rows="10" v-model="message"></textarea>
                </div>
            </form>

            <div>Available Variables:</div>
            <variables-list :items="variables"></variables-list>

            <template v-slot:modal-footer="{ ok, cancel }">
                <b-button @click="cancel">Cancel</b-button>
                <b-button @click="ok" :disabled="isDisabled" variant="success">Save</b-button>
            </template>
        </b-modal>
    </span>
</template>

<script>
import VariablesList from './VariablesList'

export default {
    name: 'TemplateForm',
    components: {
        VariablesList
    },
    props: {
        /** @type {{ new (): Array<{ subject: String, message: String }>|null }} */
        template: {
            type: [Object, null],
            default: () => null
        },
        /** @type {{ new (): Boolean }} */
        visible: {
            type: Boolean,
            default: () => false
        },
        /** @type {{ new (): Array<String> }} */
        variables: {
            type: Array,
            default: () => []
        }
    },
    data() {
        return {
            subject: "",
            message: ""
        }
    },
    watch: {
        /**
         * @param  {Object|null}  v
         */
        template(v) {
            if (v === null) {
                this.subject = ""
                this.message = ""
            }

            if (v !== null) {
                this.subject = v.subject
                this.message = v.message
            }
        }
    },
    computed: {
        /** @returns {Boolean} */
        isDisabled() {
            return this.subject === "" || this.message === ""
        }
    },
    methods: {
        /**
         * Saves a template.
         */
        saveTemplate() {
            let template = {
                subject: this.subject,
                message: this.message
            }

            if (this.template) {
                template.id = this.template.id
            }

            this.$emit('ok', {...template})
        }
    }
}
</script>
