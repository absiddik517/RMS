<template>
  <Content>
    <div class="row">
      <div class="col-12">
        <Card varient="gray" body-class="p-0">
          <template #title>
            Subject Mapping
            <i v-show="loadingTable" class="fa fa-spinner fa-spin"></i>
          </template>
          <template #title_right>
            <Link :href="route('exam.map.create')" >Create</Link>
          </template>
          <div class="row p-2 gy-2">
            <div class="col-6">
              <input
                v-model="filter.search"
                type="text"
                placeholder="Search..."
                class="form-control"
              />
              <i
                v-show="filter.search"
                @click="filter.search = null"
                class="fa fa-times filter-close"
                style="right: 15px"
              ></i>
            </div>
            <div class="col-6 d-flex justify-content-end align-items-center">
              Show
              <select
                v-model="filter.per_page"
                class="ml-2 select_per_page"
                id="per_page"
              >
                <option disabled value="null">🔻</option>
                <option v-for="index in 100" :value="index * 5">
                  {{ index * 5 }}
                </option>
                <option value="all">All</option>
              </select>
              <Dropdown
                animate
                stay
                header="Filter"
                id="filtersubjectmappingDropdown"
              >
                <template #btn>
                  <i class="fa fa-filter"></i>
                </template>

                <div class="px-2 py-1">
                  <Dropdown
                    animate
                    stay
                    header="Toggle Collumn"
                    id="filterColumnToggle"
                  >
                    <template #btn>
                      <i class="fa fa-eye"></i> Collumn visibility
                    </template>

                    <label
                      :for="field + 'dropdown'"
                      class="dropdown-item"
                      v-for="(value, field) in columns"
                    >
                      <input
                        v-model="columns[field]"
                        type="checkbox"
                        :id="field + 'dropdown'"
                      />
                      {{ field.replace("_", " ").toUpperCase() }}
                    </label>
                  </Dropdown>
                </div>
              </Dropdown>
            </div>
          </div>
          <div class="table-responsive">
            <table class="table table-hover y-middle text-nowrap">
              <thead class="bg-gray-1">
                <tr>
                  <th v-show="columns.id">ID</th>
                  <th v-show="columns.exam_name">Exam</th>
                  <th v-show="columns.class_name">Class</th>
                  <th v-show="columns.subject_name">Subject Id</th>
                  <th v-show="columns.full_mark">Full Mark</th>
                  <th v-show="columns.criteria">Criteria</th>
                  <th v-show="columns.action" style="width: 30px"></th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(subjectmapping, index) in subjectmappings.data">
                  <td v-show="columns.id">{{ subjectmapping.id }}</td>
                  <td v-show="columns.exam_name">{{ subjectmapping.exam_name }}</td>
                  <td v-show="columns.class_name">
                    {{ subjectmapping.class_name }}
                  </td>
                  <td v-show="columns.subject_name">
                    {{ subjectmapping.subject_name }}
                  </td>
                  <td v-show="columns.full_mark">
                    {{ subjectmapping.full_mark }}
                  </td>
                  <td v-show="columns.criteria">
                    {{ subjectmapping.criteria }}
                  </td>
                  <td v-show="columns.action" class="text-right">
                    <Dropdown
                      stay
                      :header="subjectmapping.name"
                      :id="'subjectmappingindex' + index"
                    >
                      <Button
                        btnDropdown
                        type="button"
                        @click="deletesubjectmapping(subjectmapping.delete_url)"
                      >
                        <i class="fa fa-trash"></i> Delete
                      </Button>
                      <!--
                      <Button
                        btnDropdown
                        type="button"
                        @click="showModal(subjectmapping)"
                      >
                        <i class="fa fa-edit"></i> Edit
                      </Button>
                      -->
                    </Dropdown>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          <div class="p-2">
            <Pagination
              @traped="loadingTable = true"
              :items="subjectmappings"
            />
          </div>
        </Card>
      </div>
    </div>
  <DeleteConfirm :deleteUrl="deleteUrl" item="subjectmapping" />
  </Content>
</template>

<script>
import {
  AdminLayout,
  Modal,
  Card,
  Input,
  Select,
  DeleteConfirm,
  Spinner,
  Dropdown,
  Pagination,
  Filterth,
  Button,
  Content,
  useValidateForm,
} from "@/Components";
import toast from "@/Store/toast.js";
import _ from "lodash";
import { Inertia } from "@inertiajs/inertia";
import {useForm} from "@inertiajs/inertia-vue3";
import { reactive, ref } from "vue";
import { isRequired } from "intus/rules";

export default {
  name: "SubjectMapping",
  layout: AdminLayout,
  components: {
    Spinner,
    Modal,
    Input,
    Select,
    Content,
    DeleteConfirm,
    Card,
    Button,
    Dropdown,
    Filterth,
    Pagination,
  },
  props: {
    subjectmappings: Object,
    params: Object,
  },
  data() {
    return {
      exams: {},
      subjects: {},
      classes: {},
      form: useForm({
        exam_id: null,
        class_id: null, 
        subject_id: null, 
        full_mark: null, 
        criteria: [{
          title: '', short_title: '', full_mark: '', pass_mark: ''
        }]
      }),

      filter: reactive({
        search: this.params.search ?? null,
        per_page: this.params.per_page ?? 5,
      }),
      columns: reactive({
        id: true,
        exam_name: true,
        class_name: true,
        subject_name: true,
        full_mark: true,
        criteria: true,
        action: true,
      }),

      modal: { confirm: null },
      modalTitle: null,
      isEditing: false,
      editUrl: null,
      deleteUrl: null,
      loadingTable: false,
      loading: false,
    };
  },
  watch: {
    filter: {
      handler: _.debounce(function (state, old) {
        let query = {};
        if (state.search) query.search = state.search;
        if (state.per_page) query.per_page = state.per_page;

        this.getsubjectmappings(query);
      }, 1000),
      deep: true,
    },
  },
  mounted() {
    let confirm = document.querySelector("#confirmModel");
    this.modal.confirm = new bootstrap.Modal(confirm);
  },
  methods: {
    async getExams () {
      this.loading = true;
      await axios.get(this.route('exam.map.exam.get'))
      .then(response => {
        this.exams = response.data.exams
        this.classes = response.data.classes
        this.loading = false;
      })
      .catch(error => {
        console.log(error)
        this.loading = false;
      });
    },
    async getSubjects () {
      if(!this.form.class_id) return
      this.loading = true;
      await axios.get(this.route('exam.map.subject.get', this.form.class_id))
      .then(response => {
        console.log(response.data)
        this.subjects = response.data
        this.loading = false;
      })
      .catch(error => {
        console.log('error on getSubjects', error)
        this.loading = false;
      });
    },
    
    getsubjectmappings(filter = {}) {
      this.loadingTable = true;
      Inertia.get(this.route("exam.map.index"), filter, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
        onSuccess: () => {
          this.loadingTable = false;
        },
        onError: (error) => {
          this.loadingTable = false;
          let message = "";
          for (let key in error) {
            message += error[key] + " ";
          }
          toast.add({
            type: "error",
            message,
          });
        },
      });
    },
    showModal(data = null) {
      this.getExams();
      this.isEditing = data !== null;
      this.modalTitle =
        data == null ? "Create SubjectMapping" : "Update SubjectMapping";
      this.form.clearErrors();
      if (this.isEditing) {
        console.log(data)
        this.editUrl = data.edit_url;
        this.form.exam_id = data.exam_id;
        this.form.class_id = data.class_id;
        this.form.subject_id = data.subject_id;
        this.form.full_mark = data.full_mark;
        this.form.criteria = JSON.parse(data.criteria);
        
        console.log(JSON.parse(data.criteria))
      } else {
        this.form.reset();
      }
      this.modal.form.show();
    },
    submit() {
      this.form.clearErrors();
      var url = this.isEditing ? this.editUrl : route("exam.map.store");
      this.form.post(url, {
        preserveScroll: true,
        onSuccess: () => {
          this.form.reset();
          this.modal.form.hide();
        },
      });
    },
    update() {
      this.form.clearErrors();
      this.form.post(this.editUrl, {
        preserveScroll: true,
        onSuccess: () => {
          this.form.reset();
          this.modal.form.hide();
        },
      });
    },
    deletesubjectmapping(url) {
      this.deleteUrl = url;
      this.modal.confirm.show();
      Inertia.on("finish", () => {
        this.deleteUrl = null;
        this.modal.confirm.hide();
      });
    },
    addCriteria(){
      this.form.criteria.push({
          title: '',
          short_title: '',
          full_mark: '',
          pass_mark: '',
      })
    },
    removeCriteria(index){
      if(this.form.criteria.length == 1) return;
      if(this.isEditing && confirm('Are you sure to delete?')){
        this.form.criteria.splice(index, 1);
      }else{
        this.form.criteria.splice(index, 1);
      }
    },
  },
};
</script>


<style scoped>
  
  .items_container {
    --item: #e6e6e6;
    border: 1px solid var(--item);
    border-radius: 4px;
    margin-bottom: 5px;
  }
  .item_header {
    padding: 8px 10px;
    background: var(--item);
  }
  .item_header span {
    color: #000;
    font-weight: bold;
    font-size: 1.35rem;
  }
  .item {
    padding: 8px;
    overflow: visible;
  }
</style>
