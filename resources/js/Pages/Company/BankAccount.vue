<template>
  <Content>
    <div class="row">
      <div class="col-12">
        <Card varient="gray" body-class="p-0">
          <template #title> BankAccount <i v-show="loadingTable" class="fa fa-spinner fa-spin"></i>
          </template>
          <template #title_right>
            <Button class="mr-2" type="button" varient="light" @click="showModal(null)">
              Create
            </Button>
          </template>
          <div class="row p-2 gy-2">
            <div class="col-6">
              <input v-model="filter.search" type="text" placeholder="Search..." class="form-control" />
              <i v-show="filter.search" @click="filter.search = null" class="fa fa-times filter-close" style="right: 15px;"></i>
            </div>
            <div class="col-6 d-flex justify-content-end align-items-center">
              Show
              <select v-model="filter.per_page" class="ml-2 select_per_page" id="per_page">
                <option disabled value="null">🔻</option>
                <option v-for="index in 100" :value="index * 5">{{ index * 5 }}</option>
                <option value="all">All</option>
              </select>
              <Dropdown animate stay header="Filter" id="filterbankaccountDropdown">
                <template #btn>
                  <i class="fa fa-filter"></i>
                </template>
                
                <div class="px-2 py-1">
                  <Dropdown animate stay header="Toggle Collumn" id="filterColumnToggle">
                    <template #btn>
                      <i class="fa fa-eye"></i>  Collumn visibility
                    </template>
                    
                    <label :for="field + 'dropdown'" class="dropdown-item" v-for="(value, field) in columns">
                      <input v-model="columns[field]" type="checkbox" :id="field + 'dropdown'"> 
                      {{ field.replace('_', ' ').toUpperCase() }}
                    </label>
                  </Dropdown>
                </div>
              </Dropdown>
            </div>
          </div>

          <table class="table table-hover y-middle">
            <div v-show="loadingTable" class="overlays">
              <span>Loading... <i class="fa fa-spin fa-spinner"></i></span>
            </div>
            <thead class="bg-gray-1">
              <tr>
                <th v-show="columns.id">ID</th>
                <th v-show="columns.bank_name"> Bank Name</th>
                <th v-show="columns.account_number"> Account Number </th>
                <th v-show="columns.account_name"> Account Holder </th>
                <th v-show="columns.action" style="width: 30px;"></th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(bankaccount, index) in bankaccounts.data">
                <td v-show="columns.id">{{ bankaccount.id }}</td>
                <td v-show="columns.bank_name">{{ bankaccount.bank_name }}</td>
                <td v-show="columns.account_number">{{ bankaccount.account_number }}</td>
                <td v-show="columns.account_name">{{ bankaccount.account_name }}</td>
                <td v-show="columns.action" class="text-right">
                  <Dropdown stay :header="bankaccount.name" :id="'bankaccountindex' + index">
                    <Button
                      btnDropdown
                      type="button"
                      @click="deletebankaccount(bankaccount.delete_url)"
                    >
                      <i class="fa fa-trash"></i> Delete
                    </Button>
                    <Button btnDropdown type="button" @click="showModal(bankaccount)">
                      <i class="fa fa-edit"></i> Edit
                    </Button>
                  </Dropdown>
                </td>
              </tr>
            </tbody>
          </table>
          <div class="p-2">
            <Pagination @traped="loadingTable = true" :items="bankaccounts" />
          </div>
        </Card>
      </div>
    </div>
  </Content>
  <Modal id="company_bank_account_create_modal" :title="modalTitle" varient="light">
    <template #body>
      <form @submit.prevent="submit" novalidate="novalidate">
        <div class="form-group">
          <label for="form_input_bank_name">Bank Name</label>
          <input
            v-model="form.bank_name"
            :disabled="form.processing"
            type="text"
            name="bank_name"
            class="form-control"
            :class="{ 'is-invalid': form.errors.bank_name }"
            id="form_input_bank_name"
            placeholder="Bank name"
            aria-describedby="form_input_bank_name-error"
            aria-invalid="true"
          />
          <span id="form_input_bank_name-error" class="error invalid-feedback">{{ form.errors.bank_name }}</span>
        </div>
        <div class="form-group">
          <label for="form_input_account_number">Account Number</label>
          <input
            v-model="form.account_number"
            :disabled="form.processing"
            type="text"
            name="account_number"
            class="form-control"
            :class="{ 'is-invalid': form.errors.account_number }"
            id="form_input_account_number"
            placeholder="Account number"
            aria-describedby="form_input_account_number-error"
            aria-invalid="true"
          />
          <span id="form_input_account_number-error" class="error invalid-feedback">{{ form.errors.account_number }}</span>
        </div>
        <div class="form-group">
          <label for="form_input_account_name">Account Name</label>
          <input
            v-model="form.account_name"
            :disabled="form.processing"
            type="text"
            name="account_name"
            class="form-control"
            :class="{ 'is-invalid': form.errors.account_name }"
            id="form_input_account_name"
            placeholder="Account name"
            aria-describedby="form_input_account_name-error"
            aria-invalid="true"
          />
          <span id="form_input_account_name-error" class="error invalid-feedback">{{ form.errors.account_name }}</span>
        </div>
      </form>
    </template>

    <template #action>
      <Button :isLoading="form.processing" :hideLabel="true" @click="submit">
        <i class="fa fa-save"></i>
      </Button>
    </template>
  </Modal>
  <DeleteConfirm :deleteUrl="deleteUrl" item="bank account"/>
</template>

<script>
import {
  AdminLayout, Modal, Card, DeleteConfirm, Spinner, Dropdown, Pagination, Filterth, Button, Content, useValidateForm,
} from "@/Components";
import toast from '@/Store/toast.js';
import _ from "lodash";
import { Inertia } from "@inertiajs/inertia";
import { reactive, ref } from "vue";
import { isRequired } from "intus/rules";

export default {
  name: "BankAccount",
  layout: AdminLayout,
  components: {
    Spinner, Modal, Content, DeleteConfirm, Card, Button, Dropdown, Filterth, Pagination,
  },
  props: {
    bankaccounts: Object,
    params: Object,
  },
  data() {
    return {
      form: useValidateForm({
        bank_name: [null, [isRequired()]],
        account_number: [null, [isRequired()]],
        account_name: [null, [isRequired()]],
      }),

      filter: reactive({
        search: this.params.search ?? null,
        per_page: this.params.per_page ?? 5,
      }),
      columns: reactive({
        id: true,
        bank_name: true, 
        account_number: true, 
        account_name: true, 
        action: true
      }),

      modal: {form: null, confirm: null},
      modalTitle: null,
      isEditing: false,
      editUrl: null,
      deleteUrl: null,
      loadingTable: false,
    };
  },
  watch: {
    filter: {
      handler: _.debounce(function (state, old) {
        let query = {};
        if (state.search) query.search = state.search;
        if (state.per_page) query.per_page = state.per_page;
        
        this.getbankaccounts(query);
      }, 1000),
      deep: true,
    },
  },
  mounted() {
    let form = document.querySelector("#company_bank_account_create_modal");
    let confirm = document.querySelector("#confirmModel");
    this.modal.form = new bootstrap.Modal(form);
    this.modal.confirm = new bootstrap.Modal(confirm);
  },
  methods: {
    getbankaccounts(filter = {}) {
      this.loadingTable = true;
      Inertia.get(this.route("company.bank.index"), filter, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
        onSuccess: () => {
          this.loadingTable = false;
        },
        onError: error => {
          this.loadingTable = false;
          let message = '';
          for(let key in error){
            message += error[key] + ' ';
          }
          toast.add({
            type: 'error',
            message
          })
        }
      });
    },
    showModal(data = null) {
      this.isEditing = data !== null;
      this.modalTitle = data == null ? "Create BankAccount" : "Update BankAccount";
      this.form.clearErrors();
      if (this.isEditing) {
        this.editUrl = data.edit_url;
                  this.form.bank_name = data.bank_name;
          this.form.account_number = data.account_number;
          this.form.account_name = data.account_name;
      } else {
        this.form.reset();
      }
      this.modal.form.show();
    },
    submit() {
      this.form.clearErrors();
      var url = this.isEditing ? this.editUrl : route("company.bank.store");
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
    deletebankaccount(url) {
      this.deleteUrl = url;
      this.modal.confirm.show();
      Inertia.on('finish', () => {
        this.deleteUrl = null
        this.modal.confirm.hide()
      })
    },
  },
};
</script>

