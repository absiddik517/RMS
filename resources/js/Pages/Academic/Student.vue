<template>
  <Content>
    <div class="row">
      <div class="col-12">
        <Card varient="gray" body-class="p-0">
          <template #title>
            Student <i v-show="loadingTable" class="fa fa-spinner fa-spin"></i>
          </template>
          <template #title_right>
            <Button
              class="mr-2"
              type="button"
              varient="light"
              @click="showModal(null)"
            >
              Create
            </Button>
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
              <Dropdown animate stay header="Filter" id="filterstudentDropdown">
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
              <div v-show="loadingTable" class="overlays">
                <span>Loading... <i class="fa fa-spin fa-spinner"></i></span>
              </div>
              <thead class="bg-gray-1">
                <tr>
                  <th v-show="columns.id">ID</th>
                  <th v-show="columns.class_id">Class Id</th>
                  <th v-show="columns.roll">Roll</th>
                  <th v-show="columns.name">Name</th>
                  <!--
                  <th v-show="columns.father_name">Father Name</th>
                  <th v-show="columns.mother_name">Mother Name</th>
                  <th v-show="columns.address">Address</th>
                  <th v-show="columns.phone">Phone</th>
                  -->
                  <th v-show="columns.action" style="width: 30px"></th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(student, index) in students.data">
                  <td v-show="columns.id">{{ student.id }}</td>
                  <td v-show="columns.class_id">{{ student.class_name }}</td>
                  <td v-show="columns.roll">{{ student.roll }}</td>
                  <td v-show="columns.name">{{ student.name }}</td>
                  <!--
                  <td v-show="columns.father_name">
                    {{ student.father_name }}
                  </td>
                  <td v-show="columns.mother_name">
                    {{ student.mother_name }}
                  </td>
                  <td v-show="columns.address">{{ student.address }}</td>
                  <td v-show="columns.phone">{{ student.phone }}</td>
                  -->
                  <td v-show="columns.action" class="text-right">
                    <Dropdown
                      stay
                      :header="student.name"
                      :id="'studentindex' + index"
                    >
                      <Button
                        btnDropdown
                        type="button"
                        @click="deletestudent(student.delete_url)"
                      >
                        <i class="fa fa-trash"></i> Delete
                      </Button>
                      <Button
                        btnDropdown
                        type="button"
                        @click="showModal(student)"
                      >
                        <i class="fa fa-edit"></i> Edit
                      </Button>
                    </Dropdown>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          <div class="p-2">
            <Pagination @traped="loadingTable = true" :items="students" />
          </div>
        </Card>
      </div>
    </div>
  </Content>
  <Modal id="academic_student_create_modal" :title="modalTitle" varient="light"
  :loading="loading">
    <template #body>
      <form @submit.prevent="submit" novalidate="novalidate">
        <Select
          v-model="form.class_id"
          label-text="Class"
          :options="classes"
        />
        <Input v-model="form.roll" field="roll" label="Roll" :form="form" />
        <Input v-model="form.name" field="name" label="Name" :form="form" />
        <!--
        <Input
          v-model="form.father_name"
          field="father_name"
          label="Father Name"
          :form="form"
          optional
        />
        <Input
          v-model="form.mother_name"
          field="mother_name"
          label="Mother Name"
          :form="form"
          optional
        />
        <Input
          v-model="form.address"
          field="address"
          label="Address"
          :form="form"
          optional
        />
        <Input
          v-model="form.phone"
          field="phone"
          label="Phone"
          :form="form"
          optional
        />
        -->
      </form>
    </template>

    <template #action>
      <Button :isLoading="form.processing" :hideLabel="true" @click="submit">
        <i class="fa fa-save"></i>
      </Button>
    </template>
  </Modal>
  <DeleteConfirm :deleteUrl="deleteUrl" item="student" />
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
import { useForm } from "@inertiajs/inertia-vue3";
import { reactive, ref } from "vue";
import { isRequired } from "intus/rules";

export default {
  name: "Student",
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
    students: Object,
    classes: Object,
    params: Object,
  },
  data() {
    return {
      form: useForm({
        class_id: null, 
        roll: null, 
        name: null, 
        father_name: null, 
        mother_name: null, 
        address: null, 
        phone: null, 
      }),

      filter: reactive({
        search: this.params.search ?? null,
        per_page: this.params.per_page ?? 5,
      }),
      columns: reactive({
        id: true,
        class_id: true,
        roll: true,
        name: true,
        father_name: true,
        mother_name: true,
        address: true,
        phone: true,
        action: true,
      }),

      modal: { form: null, confirm: null },
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

        this.getstudents(query);
      }, 1000),
      deep: true,
    },
  },
  mounted() {
    let form = document.querySelector("#academic_student_create_modal");
    let confirm = document.querySelector("#confirmModel");
    this.modal.form = new bootstrap.Modal(form);
    this.modal.confirm = new bootstrap.Modal(confirm);
  },
  methods: {
    getstudents(filter = {}) {
      this.loadingTable = true;
      Inertia.get(this.route("student.index"), filter, {
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
    async getRollNumber() {
      this.loading = true;
      try {
        const response = await axios.get(route('student.roll.get'));
        this.form.roll = response.data;
        console.log(response)
      } catch (error) {
        console.log('Error on getSubjects', error);
      } finally {
        this.loading = false;
      }
    },
    showModal(data = null) {
      this.isEditing = data !== null;
      this.modalTitle = data == null ? "Create Student" : "Update Student";
      this.form.clearErrors();
      if (this.isEditing) {
        this.editUrl = data.edit_url;
        this.form.class_id = data.class_id;
        this.form.roll = data.roll;
        this.form.name = data.name;
        this.form.father_name = data.father_name;
        this.form.mother_name = data.mother_name;
        this.form.address = data.address;
        this.form.phone = data.phone;
      } else {
        this.form.reset();
        this.getRollNumber();
      }
      this.modal.form.show();
    },
    submit() {
      this.form.clearErrors();
      var url = this.isEditing ? this.editUrl : route("student.store");
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
    deletestudent(url) {
      this.deleteUrl = url;
      this.modal.confirm.show();
      Inertia.on("finish", () => {
        this.deleteUrl = null;
        this.modal.confirm.hide();
      });
    },
  },
};
</script>
