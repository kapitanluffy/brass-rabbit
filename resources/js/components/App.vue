<template>
    <span>
        <div class="row justify-content-center mt-5">
            <div class="col-md">
                <form>
                    <div class="form-group">
                        <label for="contacts">Contacts</label>
                        <input type="file" class="form-control-file" id="contacts" @change="loadContacts">
                    </div>
                </form>
            </div>
        </div>

        <div class="row justify-content-center mt-2">
            <div class="col-md">
                <template-form
                    :visible="templateFormVisible"
                    :template="editableTemplate"
                    :variables="variables"
                    @ok="saveTemplate"
                    @hidden="closeTemplateForm"
                    />
            </div>
        </div>

        <div class="row justify-content-center mt-4">
            <div class="col-md">
                <span v-if="templateForms.length > 0">
                    <h6 class="lead">Email Templates</h6>
                    <template-form-list :items="templateForms"  @edit="editTemplate" @delete="deleteTemplate"></template-form-list>
                </span>
            </div>
        </div>

        <div class="row justify-content-center mt-4">
            <div class="col-md">
                <div v-if="contacts.length > 0">
                    <h6 class="lead">Email Contacts</h6>
                    <contacts-list :items="contacts" @preview-template="previewContactTemplate" :enable-preview="templateForms.length > 0"></contacts-list>
                </div>
            </div>
        </div>

        <template v-if="previewTemplate">
            <b-modal id="previewModal" centered ok-only ok-title="Close"
                :title="previewTemplate.subject"
                :visible="previewTemplate !== null"
                @hidden="(e) => this.previewTemplate = null"
                >
                <span v-html="previewTemplate.message"></span>
            </b-modal>
        </template>
    </span>
</template>

<script>
import { uniqueId } from 'lodash'
import TemplateFormList from './TemplateFormList'
import ContactsList from './ContactsList'
import TemplateForm from './TemplateForm'

export default {
    name: 'App',
    components: {
        ContactsList,
        TemplateFormList,
        TemplateForm,
    },
    data() {
        return {
            csv: null,
            contacts: [],
            /** @type { Array<{ subject: String, message: String }> } */
            templateForms: [],
            templateFormVisible: false,
            editableTemplate: null,
            previewTemplate: null
        }
    },
    mounted() {
        this.getTemplates()
    },
    computed: {
        variables() {
            return (this.csv !== null) ? this.csv.headers : []
        }
    },
    methods: {
        previewContactTemplate(contact) {
            axios.get(`/api/contacts/${contact.id}/preview`)
                .then((response) => {
                    this.previewTemplate = response.data
                })
                .catch((error) => {
                    console.log(error);
                });
        },
        addTemplate() {
            this.editableTemplate = null
            this.templateFormVisible = true
        },
        closeTemplateForm() {
            this.editableTemplate = null
            this.templateFormVisible = false
        },
        editTemplate(template) {
            this.editableTemplate = {...template}
            this.templateFormVisible = true
        },
        getTemplates() {
            axios.get('/api/templates')
                .then((response) => {
                    this.templateForms = response.data.data
                    console.log(this.templateForms);
                })
                .catch((error) => {
                    console.log(error);
                });
        },
        createTemplate(template) {
            template.id = uniqueId()
            this.templateForms.push(template)

            axios.post('/api/templates', {...template})
                .then((response) => {
                    console.log(response);
                })
                .catch((error) => {
                    console.log(error);
                });
        },
        deleteTemplate(template) {
            axios.delete(`/api/templates/${template.id}`)
                .then((response) => {
                    let templates = [...this.templateForms]

                    let i = templates.findIndex((t) => {
                        return t.id === template.id
                    })
                    templates.splice(i, 1)

                    this.templateForms = [...templates]
                })
                .catch((error) => {
                    console.log(error);
                });
        },
        updateTemplate(template) {
            let templates = [...this.templateForms]

            let i = templates.findIndex((t) => {
                return t.id === template.id
            })

            templates[i] = {...template}
            this.templateForms = [...templates]

            axios.post(`/api/templates/${template.id}`, {...template})
                .then((response) => {
                    console.log(response);
                })
                .catch((error) => {
                    console.log(error);
                });
        },
        saveTemplate(template) {
            this.editableTemplate = null

            if (template.id !== undefined) {
                this.updateTemplate(template)
            }
            else if (template.id === undefined) {
                this.createTemplate(template)
            }
        },
        loadContacts(e) {
            let formData = new FormData()
            formData.append('contacts', e.target.files[0])

            axios.post('/api/contacts', formData, { headers: { 'Content-Type': 'multipart/form-data' } })
                .then((response) => {
                    console.log(response);
                    this.csv = response.data
                    this.contacts = response.data.rows
                })
                .catch((error) => {
                    console.log(error);
                })
                .then(() => {
                    e.target.value = null
                });
        }
    }
}
</script>
