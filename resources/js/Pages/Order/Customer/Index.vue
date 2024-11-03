<template>
  <Content>
    <div class="row">
      <div class="col-12">
        <Card varient="gray">
          <template #title>
            Customer <i v-show="loadingTable" class="fa fa-spinner fa-spin"></i>
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

          <div class="row gy-2">
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
                id="filtercustomerDropdown"
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
                      <i class="fa fa-eye"></i> Data visibility
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

          <div class="mt-2">
            <div
              class="item d-flex justify-content-between"
              v-for="(customer, index) in customers.data"
            >
              <div
                class="customer flex-1 d-flex flex-column font-quicksand t-base"
              >
                <span v-show="columns.name" class="font-krona">
                  <i class="fa fa-user"></i> {{ customer.name }}</span
                >
                <span v-show="columns.address" class="">
                  <i class="fa fa-map-marker"></i> {{ customer.address }}</span
                >
                <span v-show="columns.phone && customer.phone" class="font-space">
                  <i class="fa fa-phone"></i> {{ customer.phone }}</span
                >
                <span v-show="columns.email && customer.email" class="">
                  <i class="fa fa-envelope"></i> {{ customer.email }}</span
                >
              </div>
              <div
                class="d-flex flex-column justify-content-between align-items-end"
              >
                <Dropdown
                  v-show="columns.action"
                  stay
                  :header="customer.name"
                  :id="'permissionindex' + index"
                >
                  <Button
                    btnDropdown
                    type="button"
                    @click="deletecustomer(customer.delete_url)"
                  >
                    <i class="fa fa-trash"></i> Delete
                  </Button>
                  <Button
                    btnDropdown
                    type="button"
                    @click="showModal(customer)"
                  >
                    <i class="fa fa-edit"></i> Edit
                  </Button>
                  <Link :href="route('order.customer.trade', customer.id)" class="dropdown-item">
                    <i class="fa fa-eye"></i> Trades
                  </Link>
                </Dropdown>
                <div v-show="columns.id" class="counter">
                  <span> #{{ customer.id }} </span>
                </div>
              </div>
            </div>
          </div>

          <div class="p-2">
            <Pagination @traped="loadingTable = true" :items="customers" />
          </div>
        </Card>
      </div>
    </div>
  </Content>
  <Modal id="order_customer_create_modal" :title="modalTitle" varient="light">
    <template #body>
      <form @submit.prevent="submit" novalidate="novalidate">
        <Input v-model="form.name" :form="form" field="name"/>
        <Input v-model="form.address" :form="form" field="address" autocomplete="customers.address"/>
        <Input v-model="form.phone" :form="form" field="phone" autocomplete="customers.phone"/>
        <Input v-model="form.email" :form="form" field="email" autocomplete="customers.email"/>
      </form>
    </template>

    <template #action>
      <Button :isLoading="form.processing" :hideLabel="true" @click="submit">
        <i class="fa fa-save"></i>
      </Button>
    </template>
  </Modal>
  <DeleteConfirm :deleteUrl="deleteUrl" item="customer" />
</template>

<script>
import {
  AdminLayout,
  Modal,
  Card,
  DeleteConfirm,
  Spinner,
  Dropdown,
  Input,
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
  name: "Index",
  layout: AdminLayout,
  components: {
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
    customers: Object,
    params: Object,
  },
  data() {
    return {
      form: useValidateForm({
        name: [null, [isRequired()]],
        address: [null, [isRequired()]],
        phone: [null, [isRequired()]],
        email: [null, [isRequired()]],
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
        address: {
          isActive: this.params.address != null,
          value: this.params.address ?? null,
        },
        phone: {
          isActive: this.params.phone != null,
          value: this.params.phone ?? null,
        },
        email: {
          isActive: this.params.email != null,
          value: this.params.email ?? null,
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
        address: true,
        phone: true,
        email: true,
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

        this.getcustomers(query);
      }, 1000),
      deep: true,
    },
  },
  mounted() {
    let form = document.querySelector("#order_customer_create_modal");
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
    getcustomers(filter = {}) {
      this.loadingTable = true;
      Inertia.get(this.route("order.customer.index"), filter, {
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
      this.modalTitle = data == null ? "Create Customer" : "Update Customer";
      this.form.clearErrors();
      if (this.isEditing) {
        this.editUrl = data.edit_url;
        this.form.name = data.name;
        this.form.address = data.address;
        this.form.phone = data.phone;
        this.form.email = data.email;
      } else {
        this.form.reset();
      }
      this.modal.form.show();
    },
    submit() {
      this.form.clearErrors();
      var url = this.isEditing ? this.editUrl : route("order.customer.store");
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
    deletecustomer(url) {
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

<style lang="scss">
.item {
  background: #fff;
  margin-bottom: 0.5rem;
  border-radius: 0.3rem;
  padding: 0.4rem 0.7rem;
  overflow: hidden;
  border: 1px solid #eee;
  box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);
  .customer {
    i {
      width: 1rem;
    }
  }
}
.counter {
  span {
    color: rgba(0, 0, 0, 0.2);
    font-size: 2rem;
  }
}
</style>
