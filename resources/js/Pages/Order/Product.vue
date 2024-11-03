<template>
  <Content>
    <div class="row">
      <div class="col-12">
        <Card body-class="p-0" varient="gray">
          <template #title>
            Product <i v-show="loadingTable" class="fa fa-spinner fa-spin"></i>
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
              <Dropdown animate stay header="Filter" id="filterproductDropdown">
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
                    @input="
                      (value) => {
                        if (typeof value == 'string') filter.id.value = value;
                      }
                    "
                  />
                </th>
                <th v-show="columns.name">
                  <Filterth
                    field="name"
                    label="Name"
                    searchable
                    sortable
                    @input="
                      (value) => {
                        if (typeof value == 'string') filter.name.value = value;
                      }
                    "
                    :label-click="
                      function () {
                        filter.name.isActive = true;
                        filter.search = null;
                      }
                    "
                    :close-search-fn="
                      function () {
                        filter.name.isActive = false;
                        filter.name.value = null;
                      }
                    "
                    :filter="filter"
                    :set-order="setOrder"
                  />
                </th>
                <th v-show="columns.unit">
                  <Filterth
                    field="unit"
                    label="Unit"
                    searchable
                    sortable
                    @input="
                      (value) => {
                        if (typeof value == 'string') filter.unit.value = value;
                      }
                    "
                    :label-click="
                      function () {
                        filter.unit.isActive = true;
                        filter.search = null;
                      }
                    "
                    :close-search-fn="
                      function () {
                        filter.unit.isActive = false;
                        filter.unit.value = null;
                      }
                    "
                    :filter="filter"
                    :set-order="setOrder"
                  />
                </th>
                <th v-show="columns.rate">
                  <Filterth
                    field="rate"
                    label="Rate"
                    searchable
                    sortable
                    @input="
                      (value) => {
                        if (typeof value == 'string') filter.rate.value = value;
                      }
                    "
                    :label-click="
                      function () {
                        filter.rate.isActive = true;
                        filter.search = null;
                      }
                    "
                    :close-search-fn="
                      function () {
                        filter.rate.isActive = false;
                        filter.rate.value = null;
                      }
                    "
                    :filter="filter"
                    :set-order="setOrder"
                  />
                </th>
                <th v-show="columns.transport_rate">
                  <Filterth
                    field="transport_rate"
                    label="Transport Rate"
                    searchable
                    sortable
                    @input="
                      (value) => {
                        if (typeof value == 'string')
                          filter.transport_rate.value = value;
                      }
                    "
                    :label-click="
                      function () {
                        filter.transport_rate.isActive = true;
                        filter.search = null;
                      }
                    "
                    :close-search-fn="
                      function () {
                        filter.transport_rate.isActive = false;
                        filter.transport_rate.value = null;
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
              <tr v-for="(product, index) in products.data">
                <td v-show="columns.id">{{ product.id }}</td>
                <td v-show="columns.name">{{ product.name }}</td>
                <td v-show="columns.unit">{{ product.unit }}</td>
                <td v-show="columns.rate">{{ product.rate }}</td>
                <td v-show="columns.transport_rate">
                  {{ product.transport_rate }}
                </td>
                <td v-show="columns.action" class="text-right">
                  <Dropdown
                    stay
                    :header="product.name"
                    :id="'productindex' + index"
                  >
                    <Button
                      btnDropdown
                      type="button"
                      @click="deleteproduct(product.delete_url)"
                    >
                      <i class="fa fa-trash"></i> Delete
                    </Button>
                    <Button
                      btnDropdown
                      type="button"
                      @click="showModal(product)"
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
            <Pagination @traped="loadingTable = true" :items="products" />
          </div>
        </Card>
      </div>
    </div>
  </Content>
  <Modal id="order_product_create_modal" :title="modalTitle" varient="light">
    <template #body>
      <form @submit.prevent="submit" novalidate="novalidate">
        <Input v-model="form.name" :form="form" field="name" autocomplete="products.name"/>
        <Input v-model="form.unit" :form="form" field="unit" autocomplete="products.unit"/>
        <Input v-model="form.rate" :form="form" field="rate"/>
        <Input v-model="form.transport_rate" :form="form" field="transport_rate"/>
      </form>
    </template>

    <template #action>
      <Button :isLoading="form.processing" :hideLabel="true" @click="submit">
        <i class="fa fa-save"></i>
      </Button>
    </template>
  </Modal>
  <DeleteConfirm :deleteUrl="deleteUrl" item="product" />
</template>

<script>
import {
  AdminLayout,
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

export default {
  name: "Product",
  layout: AdminLayout,
  components: {
    Spinner,
    Modal,
    Content,
    DeleteConfirm,
    Input,
    Card,
    Button,
    Dropdown,
    Filterth,
    Pagination,
  },
  props: {
    products: Object,
    params: Object,
  },
  data() {
    return {
      form: useValidateForm({
        name: [null, [isRequired()]],
        unit: [null, [isRequired()]],
        rate: [null, [isRequired()]],
        transport_rate: [null, [isRequired()]],
      }),

      filter: reactive({
        id: {
          isActive: this.params.id != null,
          value: this.params.id ?? null,
        },
        name: {
          isActive: this.params.name != null,
          value: this.params.name ?? null,
        },
        unit: {
          isActive: this.params.unit != null,
          value: this.params.unit ?? null,
        },
        rate: {
          isActive: this.params.rate != null,
          value: this.params.rate ?? null,
        },
        transport_rate: {
          isActive: this.params.transport_rate != null,
          value: this.params.transport_rate ?? null,
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
        name: true,
        unit: true,
        rate: true,
        transport_rate: true,
        action: true,
      }),

      modal: { form: null, confirm: null },
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

        this.getproducts(query);
      }, 1000),
      deep: true,
    },
  },
  mounted() {
    let form = document.querySelector("#order_product_create_modal");
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
    getproducts(filter = {}) {
      this.loadingTable = true;
      Inertia.get(this.route("order.product.index"), filter, {
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
      this.isEditing = data !== null;
      this.modalTitle = data == null ? "Create Product" : "Update Product";
      this.form.clearErrors();
      if (this.isEditing) {
        this.editUrl = data.edit_url;
        this.form.name = data.name;
        this.form.unit = data.unit;
        this.form.rate = data.rate;
        this.form.transport_rate = data.transport_rate;
      } else {
        this.form.reset();
      }
      this.modal.form.show();
    },
    submit() {
      this.form.clearErrors();
      var url = this.isEditing ? this.editUrl : route("order.product.store");
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
    deleteproduct(url) {
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
