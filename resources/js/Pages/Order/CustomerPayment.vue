<template>
  <Content>
    <div class="row">
      <div class="col-12">
        <Card :isTable="true" varient="gray">
          <template #title>
            CustomerPayment
            <i v-show="loadingTable" class="fa fa-spinner fa-spin"></i>
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
              <Dropdown
                animate
                stay
                header="Filter"
                id="filtercustomerpaymentDropdown"
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
          
          <table class="table table-hover y-middle">
            <div v-show="loadingTable" class="overlays">
              <span>Loading... <i class="fa fa-spin fa-spinner"></i></span>
            </div>
            <thead class="bg-gray-1">
              <tr>
                <th v-show="columns.id">
                  <Filterth
                    field="id"
                    searchable
                    sortable
                    :label-click="
                      function () {
                        filter.id.isActive = true;
                        filter.search = null;
                      }
                    "
                    :close-search-fn="
                      function () {
                        filter.id.isActive = false;
                        filter.id.value = null;
                      }
                    "
                    :filter="filter"
                    :set-order="setOrder"
                    v-model="filter.id.value"
                  />
                </th>
                <th v-show="columns.customer_id">
                  <Filterth
                    field="customer_id"
                    label="Customer"
                    searchable
                    sortable
                    v-model="filter.customer_id.value"
                    :label-click="
                      function () {
                        filter.customer_id.isActive = true;
                        filter.search = null;
                      }
                    "
                    :close-search-fn="
                      function () {
                        filter.customer_id.isActive = false;
                        filter.customer_id.value = null;
                      }
                    "
                    :filter="filter"
                    :set-order="setOrder"
                  />
                </th>
                <th v-show="columns.order_ref">
                  <Filterth
                    field="order_ref"
                    label="Ref"
                    searchable
                    sortable
                    v-model="filter.order_ref.value"
                    :label-click="
                      function () {
                        filter.order_ref.isActive = true;
                        filter.search = null;
                      }
                    "
                    :close-search-fn="
                      function () {
                        filter.order_ref.isActive = false;
                        filter.order_ref.value = null;
                      }
                    "
                    :filter="filter"
                    :set-order="setOrder"
                  />
                </th>
                <th v-show="columns.payment_type">
                  <Filterth
                    field="payment_type"
                    label="Type"
                    searchable
                    :options="payment_types"
                    sortable
                    v-model="filter.payment_type.value"
                    :label-click="
                      function () {
                        filter.payment_type.isActive = true;
                        filter.search = null;
                      }
                    "
                    :close-search-fn="
                      function () {
                        filter.payment_type.isActive = false;
                        filter.payment_type.value = null;
                      }
                    "
                    :filter="filter"
                    :set-order="setOrder"
                  />
                </th>
                <th v-show="columns.description">
                  <Filterth
                    field="description"
                    label="Description"
                    searchable
                    sortable
                    v-model="filter.description.value"
                    :label-click="
                      function () {
                        filter.description.isActive = true;
                        filter.search = null;
                      }
                    "
                    :close-search-fn="
                      function () {
                        filter.description.isActive = false;
                        filter.description.value = null;
                      }
                    "
                    :filter="filter"
                    :set-order="setOrder"
                  />
                </th>
                <th v-show="columns.amount">
                  <Filterth
                    field="amount"
                    label="Amount"
                    searchable
                    sortable
                    v-model="filter.amount.value"
                    :label-click="
                      function () {
                        filter.amount.isActive = true;
                        filter.search = null;
                      }
                    "
                    :close-search-fn="
                      function () {
                        filter.amount.isActive = false;
                        filter.amount.value = null;
                      }
                    "
                    :filter="filter"
                    :set-order="setOrder"
                  />
                </th>
                <th v-show="columns.action" style="width: 30px"></th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(customerpayment, index) in customerpayments.data">
                <td v-show="columns.id">{{ customerpayment.id }}</td>
                <td v-show="columns.customer_id">
                  {{ customerpayment.customer.name }}
                </td>
                <td v-show="columns.order_ref">
                  {{ customerpayment.order_ref }}
                </td>
                <td v-show="columns.payment_type">
                  <span class="badge badge-success">{{ customerpayment.payment_type }}</span>
                </td>
                <td v-show="columns.description">
                  {{ customerpayment.description }}
                </td>
                <td v-show="columns.amount">{{ customerpayment.amount }}</td>
                <td v-show="columns.action" class="text-right">
                  <Dropdown
                    stay
                    :header="customerpayment.name"
                    :id="'customerpaymentindex' + index"
                  >
                    <Button
                      btnDropdown
                      type="button"
                      @click="deletecustomerpayment(customerpayment.delete_url)"
                    >
                      <i class="fa fa-trash"></i> Delete
                    </Button>
                    <Button
                      btnDropdown
                      type="button"
                      @click="showModal(customerpayment)"
                    >
                      <i class="fa fa-edit"></i> Edit
                    </Button>
                  </Dropdown>
                </td>
              </tr>
            </tbody>
          </table>
          <div class="p-2">
            <Pagination
              @traped="loadingTable = true"
              :items="customerpayments"
            />
          </div>
        </Card>
      </div>
    </div>
  </Content>
  <Modal
    id="order_customer_payment_create_modal"
    :title="modalTitle"
    varient="light"
  >
    <template #body>
      <form @submit.prevent="submit" novalidate="novalidate">
        <div class="form-group">
          <label for="form_input_customer_id">Customer</label>

          <Select
            v-model="form.customer_id"
            :disabled="form.processing"
            id="form_input_customer_id"
            from="order.customer.payment.customers"
            track-by="id"
            label="name"
            @change="getOrderRefs"
          />

          <span id="form_input_customer_id-error" class="text-red t-sm">
            {{ form.errors.customer_id }}
          </span>
        </div>
        <div class="form-group">
          <label for="form_input_order_ref">Order Ref</label>
          <Select
            v-model="form.order_ref"
            :disabled="form.processing || !form.customer_id"
            id="form_input_order_ref"
            placeholder="Reference"
            :loading="isFetchingRef"
            searchable
            :options="order_references"
          />
          <span
            id="form_input_order_ref-error"
            class="text-red t-sm"
            >{{ form.errors.order_ref }}</span
          >
        </div>
        <div class="form-group">
          <label for="form_input_payment_type">Payment Type</label>
          <Select
            v-model="form.payment_type"
            :disabled="form.processing"
            id="form_input_payment_type"
            :searchable="false"
            :options="payment_types"
          />
          <span
            id="form_input_payment_type-error"
            class="text-red t-sm"
            >{{ form.errors.payment_type }}</span
          >
        </div>
        <Input v-model="form.description" :form="form" field="description" autocomplete="customer_payments.description"/>
        <Input v-model="form.amount" :form="form" field="amount"/>
        
      </form>
    </template>

    <template #action>
      <Button :isLoading="form.processing" :hideLabel="true" @click="submit">
        <i class="fa fa-save"></i>
      </Button>
    </template>
  </Modal>
  <DeleteConfirm :deleteUrl="deleteUrl" item="customerpayment" />
</template>

<script>
import {
  AdminLayout,
  Select,
  Modal,
  Card,
  DeleteConfirm,
  Spinner,
  Input,
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
import { reactive, ref } from "vue";
import { isRequired } from "intus/rules";
import axios from 'axios'

export default {
  name: "CustomerPayment",
  layout: AdminLayout,
  components: {
    Select,
    Spinner,
    Modal,
    Content,
    DeleteConfirm,
    Card,
    Input,
    Button,
    Dropdown,
    Filterth,
    Pagination,
  },
  props: {
    customerpayments: Object,
    params: Object,
    customers: Object,
    payment_types: Object,
  },
  data() {
    return {
      form: useValidateForm({
        customer_id: [null, [isRequired()]],
        order_ref: [null, [isRequired()]],
        payment_type: [null, [isRequired()]],
        description: [null, [isRequired()]],
        amount: [null, [isRequired()]],
      }),
      
      order_references: null,
      isFetchingRef: false,
      
      filter: reactive({
        id: {
          isActive: this.params.id != null,
          value: this.params.id ?? null,
        },
        customer_id: {
          isActive: this.params.customer_id != null,
          value: this.params.customer_id ?? null,
        },
        order_ref: {
          isActive: this.params.order_ref != null,
          value: this.params.order_ref ?? null,
        },
        payment_type: {
          isActive: this.params.payment_type != null,
          value: this.params.payment_type ?? null,
        },
        description: {
          isActive: this.params.description != null,
          value: this.params.description ?? null,
        },
        amount: {
          isActive: this.params.amount != null,
          value: this.params.amount ?? null,
        },
        order: {
          field: this.params.orderBy ?? null,
          direction: this.params.orderDirection ?? null,
        },
        search: this.params.search ?? null,
        per_page: this.params.per_page ?? 5,
      }),
      columns: reactive({
        id: true,
        customer_id: true,
        order_ref: true,
        payment_type: true,
        description: true,
        amount: true,
        action: true,
      }),

      modal: { form: null, confirm: null },
      modalTitle: null,
      isEditing: false,
      editUrl: null,
      deleteUrl: null,
      loadingTable: false,
      previousFilter: null
    };
  },
  watch: {
    filter: {
      handler: _.debounce(function (state, old) {
        let query = {};
        if (state.search) {
          for (const field in this.filter) {
            if (["order", "search", "per_page"].indexOf(field) != -1) continue;
            this.filter[field].isActive = false;
            this.filter[field].value = null;
          }
        }
        for (const field in state) {
          if (["order", "search", "per_page"].indexOf(field) != -1) continue;
          if (state[field].value) {
            query[field] = state[field].value;
          }
        }
        if (state.order.field && state.order.direction) {
          query.orderBy = state.order.field;
          query.orderDirection = state.order.direction;
        }
        if (state.search) query.search = state.search;
        if (state.per_page) query.per_page = state.per_page;
        if(this.previousFilter == query) return
        this.getcustomerpayments(query);
      }, 1000),
      deep: true,
    },
  },
  mounted() {
    let form = document.querySelector("#order_customer_payment_create_modal");
    let confirm = document.querySelector("#confirmModel");
    this.modal.form = new bootstrap.Modal(form);
    this.modal.confirm = new bootstrap.Modal(confirm);
    
  },
  methods: {
    setOrder(field) {
      if (this.filter.order.field != field && this.filter.order.direction) {
        this.filter.order.direction = "asc";
      } else {
        this.filter.order.direction = !this.filter.order.direction
          ? "asc"
          : this.filter.order.direction == "asc"
          ? "desc"
          : this.filter.order.direction == "desc"
          ? null
          : null;
      }
      this.filter.order.field = field;
    },
    getcustomerpayments(filter = {}) {
      this.loadingTable = true;
      Inertia.get(this.route("order.customer.payment.index"), filter, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
        onSuccess: () => {
          this.loadingTable = false;
          this.previousFilter = filter;
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
      this.isEditing = data !== null;
      this.modalTitle =
        data == null ? "Create CustomerPayment" : "Update CustomerPayment";
      this.form.clearErrors();
      if (this.isEditing) {
        this.editUrl = data.edit_url;
        this.form.customer_id = data.customer_id;
        this.form.order_ref = data.order_ref;
        this.form.payment_type = data.payment_type;
        this.form.description = data.description;
        this.form.amount = data.amount;
      } else {
        this.form.reset();
      }
      this.modal.form.show();
    },
    submit() {
      this.form.clearErrors();
      var url = this.isEditing
        ? this.editUrl
        : route("order.customer.payment.store");
      this.form.post(url, {
        preserveScroll: true,
        onSuccess: data => {
          if(data.props.toast.type == 'success'){
            this.form.reset();
            this.modal.form.hide();
          }
        },
        onError: error => {
          console.log(error)
        }
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
        onError: error => {
          console.log(error);
        }
      });
    },
    deletecustomerpayment(url) {
      this.deleteUrl = url;
      this.modal.confirm.show();
      Inertia.on("finish", () => {
        this.deleteUrl = null;
        this.modal.confirm.hide();
      });
    },
    async getOrderRefs(customer_id){
      if(! customer_id ) return 
        this.isFetchingRef = true
        console.log('getting data customer id = '+customer_id)
        await axios.get(`${this.route('order.customer.refs')}?customer_id=${customer_id}`)
        .then(response => {
          this.order_references = response.data;
          console.log(this.order_references)
        });
        this.isFetchingRef = false
    }
  },
};
</script>
