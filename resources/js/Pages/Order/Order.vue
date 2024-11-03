<template>
  <Content>
    <div class="row font-quicksand">
      <div class="col-12">
        <Card varient="gray" body-class="p-0">
          <template #title>
            Order <i v-show="loadingTable" class="fa fa-spinner fa-spin"></i>
          </template>
          <template #title_right>
            <Link 
              :href="route('order.order.create')"  
              class="nav-link"
            >
              Create new
            </Link>
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
              <Dropdown animate stay header="Filter" id="filterorderDropdown">
                <template #btn>
                  <i class="fa fa-filter"></i>
                </template>

                <div class="px-2 py-1">
                  <label for="hasDue" class="d-flex justify-content-between py-2 px-2 bg-gray-1 rounded">
                    <span>Has due</span>
                    <input type="checkbox" v-model="filter.has_due" name="" id="hasDue">
                  </label>
                  
                  <label for="hasNotDue" class="d-flex justify-content-between py-2 px-2 bg-gray-1 rounded">
                    <span>Amount paid</span>
                    <input type="checkbox" name="" id="hasNotDue">
                  </label>
                  
                  <label for="hasDeliveryDue" class="d-flex justify-content-between py-2 px-2 bg-gray-1 rounded">
                    <span>Has Delivery Due</span>
                    <input type="checkbox" name="" id="hasDeliveryDue">
                  </label>
                  
                  <Input v-model="filter.due_ref" field="due_ref" label="Due Referance" without-label :form="filter" autocomplete="orders.due_ref"/>
                  
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
                <th>#</th>
                <th>Customer</th>
                <th>Order</th>
                <th>Payments</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(order, index) in orders.data">
                <td v-show="columns.id">
                  <div class="d-flex justify-content-between"><span class="text-muted">ID:</span> <strong>{{ order.id }}</strong></div>
                  <div class="d-flex justify-content-between"><span class="text-muted">REF:</span> <strong>{{ order.ref }}</strong></div>
                  <div class="d-flex justify-content-between"><span class="text-muted">Date:</span> <strong class="ml-2">{{ order.date }}</strong></div>
                </td>
                <td>
                  <div class="font-krona"> <i class="fa fa-user"></i> <span v-highlight="filter.search">{{ order.name }}</span></div>
                  <div> <i class="fa fa-map-marker"></i> <span v-highlight="filter.search">{{ order.address }}</span></div>
                  <div> <i class="fa fa-phone"></i> {{ order.phone }}</div>
                </td>
                
                <td>
                  <div v-for="item in order.items" class="d-flex justify-content-between" :title="'Deliveryable: ' + (item.quantity - item.deliveried)">
                    <div class="flex-1 mr-3" :class="{'text-success': item.quantity == item.deliveried}">{{ item.product.name }} <i class="fa fa-check-circle text-success t-sm" v-show="item.quantity == item.deliveried"></i></div>
                    <div class="flex-1" :class="{'text-success': item.quantity == item.deliveried}">{{ item.quantity }} / <span class="text-success text-bold">{{ item.deliveried }}</span> {{ item.product.unit }}</div>
                  </div>
                </td>
                
                <td>
                  <div class="d-flex justify-content-between">
                    <div class="flex-1 mr-3">Total</div>
                    <div class="flex-1 mr-3 text-right">{{ order.amount }}</div>
                  </div>
                  <div class="d-flex justify-content-between">
                    <div class="flex-1 mr-3">Paid</div>
                    <div class="flex-1 mr-3 text-right">{{ sum(order.payments, 'amount') }}</div>
                  </div>
                  <div class="d-flex justify-content-between" style="border-top: 1px solid #000;" v-if="order.amount != sum(order.payments, 'amount')"> 
                    <div class="flex-1 mr-3">Due</div>
                    <div class="flex-1 mr-3 text-right">{{ order.amount - sum(order.payments, 'amount') }}</div>
                  </div>
                  <div style="border: 1px solid #b71009; background: #f49b9b; color: #ff0000; padding:2px; border-radius: 4px;" v-show="order.amount - sum(order.payments, 'amount') > 0">
                    <div class="d-flex justify-content-between">Via: <strong>{{ order.due_ref }}</strong></div>
                    <div class="d-flex justify-content-between">Date: <strong>{{ order.due_date }}</strong></div>
                  </div>
                </td>
                <td v-show="columns.action" class="text-right">
                  <Dropdown
                    stay
                    :header="order.name"
                    :id="'orderindex' + index"
                  >
                    <Button
                      btnDropdown
                      type="button"
                      @click="deleteorder(order.delete_url)"
                    >
                      <i class="fa fa-eye" style="width: 20px;"></i> View detail
                    </Button>
                    <Button
                      btnDropdown
                      type="button"
                      @click="deleteorder(order.delete_url)"
                    >
                      <i class="fa fa-trash" style="width: 20px;"></i> Delete
                    </Button>
                    <Button btnDropdown type="button" @click="showModal(order)">
                      <i class="fa fa-edit" style="width: 20px;"></i> Edit
                    </Button>
                    <Link 
                      :href="route('order.delivery.create', {ref: order.ref})"  
                      class="dropdown-item"
                    >
                      <i class="fa fa-truck" style="width: 20px;"></i>
                      Delivery Now
                    </Link>
                    <Link 
                      v-show="hasDeliveries(order.items)"
                      :href="route('order.delivery.deliveries', {order_id: order.id})"  
                      class="dropdown-item"
                    >
                      <i class="fa fa-list" style="width: 20px;"></i>
                      Deliveries
                    </Link>
                  </Dropdown>
                </td>
              </tr>
            </tbody>
          </table>
          </div>
          
          <div class="bg-gray-1 p-2 d-flex justify-content-between align-items-center">
            <span>Summary</span>
            <button @click="getSummary" class="btn btn-dark btn-sm"><i class="fa fa-refresh" :class="{'fa-spin': summary.isLoading}"></i></button>
          </div>
          <div class="px-2 py-2">
            <div class="row">
              <div class="col-6">
                <span>Over all</span>
                <ul class="p-0">
                  <li v-if="summary.data.products" v-for="product in summary.data.products" class="d-flex justify-content-between">
                    <span class="text-muted">{{ product.name }}</span> 
                    {{ product.total_sold ?? 0 }} 
                  </li>
                  <li class="d-flex justify-content-between">
                    <span class="text-muted">Total amount</span> 
                    {{ summary && summary.data && summary.data.orders && summary.data.orders.total_amount }}
                  </li>
                  <li class="d-flex justify-content-between">
                    <span class="text-muted">Paid</span> 
                    {{ summary && summary.data && summary.data.orders && summary.data.orders.total_paid }}
                  </li>
                  <li class="d-flex justify-content-between">
                    <span class="text-muted">Due</span> 
                    {{ (summary && summary.data && summary.data.orders && summary.data.orders.total_amount) 
                     - (summary && summary.data && summary.data.orders && summary.data.orders.total_paid) }}
                  </li>
                  <!--
                  -->
                </ul>
              </div>
              <div class="col-6">
                Current Page
                <ul class="p-0">
                  
                  <li class="d-flex justify-content-between" v-for="(product, key) in productsSold">
                    <span class="text-muted">{{ key }}</span> 
                    {{ product }}
                  </li>
                  
                  <li class="d-flex justify-content-between">
                    <span class="text-muted">Total amount</span> 
                    {{ sum(orders.data, 'amount') }}
                  </li>
                  <li class="d-flex justify-content-between">
                    <span class="text-muted">Paid</span> 
                    {{ total_paid_in_this_page }}
                  </li>
                  <li class="d-flex justify-content-between">
                    <span class="text-muted">Due</span> 
                    {{ total_due_in_this_page }}
                  </li>
                </ul>
              </div>
            </div>
          </div>
          
        </Card>
      </div>
    </div>
  </Content>
  <Modal id="order_order_create_modal" :title="modalTitle" varient="light">
    <template #body>
      <form @submit.prevent="submit" novalidate="novalidate">
        <div class="row">
          <Input v-model="form.customer_id" :form="form" field="customer_id" autocomplete="customers.name" group-class="col-6"/>
          <Input v-model="form.ref" :form="form" field="ref" group-class="col-6" type="number"/>
          <div>
            <div class="row item-row" v-for="(item, index) in form.items" :key="index">
              <div class="d-flex justify-content-between">
                <h5>Item #{{ index + 1 }}</h5>
                <div class="d-flex justify-content-end">
                  <button v-show="form.items.length > 1" @click="removeItem(index)" type="button" class="btn btn-danger btn-sm mr-2"><i class="fa fa-times"></i></button>
                  <button @click="addItem" type="button" class="btn btn-success btn-sm"><i class="fa fa-plus"></i></button>
                </div>
              </div>
              <Input v-model="item.product_id" @changed="handelChange(index)" :form="form" field="product_id" label="Product" group-class="col-6" type="select" :options="products"/>
              <Input v-model="item.quantity" @changed="calculateItems" :form="form" field="quantity" group-class="col-6" type="number"/>
              <Input v-model="item.rate" @changed="calculateItems" :form="form" group-class="col-6" field="rate" type="number"/>
              <Input v-model="item.transport_rate" @changed="calculateItems" group-class="col-6" :form="form" field="transport_rate" type="number"/>
              <Input v-model="item.product_price" :disable-if="true" group-class="col-6" :form="form" field="product_price" type="number"/>
              <Input v-model="item.transport" :disable-if="true" group-class="col-6" :form="form" field="transport" type="number"/>
              <Input v-model="item.discount" @changed="calculateItems" group-class="col-6" :form="form" field="discount" type="number"/>
              <Input v-model="item.amount" :disable-if="true" group-class="col-6" :form="form" field="amount" type="number"/>
            </div>
          </div>
          <Input v-model="form.amount" :form="form" field="amount" type="number"/>
          <Input v-model="form.paid" @changed="handelPaidChange" :form="form" field="paid" type="number"/>
          <div v-show="formHasDue">
            <Input v-model="form.due" :form="form" field="due" :disable-if="true" type="number"/>
            <div class="d-flex justify-content-between">
              <Input v-model="form.due_ref" :form="form" field="due_ref" group-class="col-6 pl-0" autocomplete="orders.due_ref"/>
              <Input v-model="form.due_date" :form="form" field="due_date" type="date" group-class="col-6 pr-0" />
            </div>
          </div>
          <Input v-model="form.note" :form="form" field="note" autocomplete="orders.note"/>
        </div>
      </form>
    </template>

    <template #action>
      <Button :isLoading="form.processing" :hideLabel="true" @click="submit">
        <i class="fa fa-save"></i>
      </Button>
    </template>
  </Modal>
  <DeleteConfirm :deleteUrl="deleteUrl" item="order" />
</template>

<script>
import {
  AdminLayout,
  Modal,
  Card,
  Input,
  DeleteConfirm,
  Spinner,
  Dropdown,
  Pagination,
  Filterth,
  Button,
  Content,
} from "@/Components";
import toast from "@/Store/toast.js";
import _ from "lodash";
import { Inertia } from "@inertiajs/inertia";
import {useForm} from "@inertiajs/inertia-vue3";
import { reactive, ref } from "vue";
import { isRequired } from "intus/rules";
import { round, sum } from "@/Composable/functions";

export default {
  name: "Order",
  layout: AdminLayout,
  components: {
    Spinner,
    Modal,
    Content,
    Input,
    DeleteConfirm,
    Card,
    Button,
    Dropdown,
    Filterth,
    Pagination,
  },
  props: {
    orders: Object,
    params: Object,
    products: [Array, Object]
  },
  data() {
    return {
      filter: reactive({
        search: this.params.search,
        due_ref: '',
        per_page: this.params.per_page ?? 10,
        has_due: this.params.has_due == 1,
        errors: {}
      }),
      form: useForm({
        ref: null,
        customer_id: null,
        amount: 0,
        note: null,
        due_ref: null,
        due_date: null,
        items: [
          {
            product_id: null,
            rate: null,
            transport_rate: null,
            quantity: null,
            product_price: null,
            transport: null,
            discount: null,
            amount: null,
          }
        ],
        paid: null,
        due: null,
      }),
      summary: reactive({
        data: {
          products: [],
          order: []
        },
        isLoading: false
      }),
      columns: reactive({
        id: true,
        ref: true,
        customer_id: true,
        amount: true,
        note: true,
        due_ref: true,
        due_date: true,
        action: true,
      }),

      modal: { form: null, confirm: null },
      modalTitle: null,
      isEditing: false,
      editUrl: null,
      deleteUrl: null,
      loadingTable: false,
      formHasDue: true,
    };
  },
  computed: {
    total_paid_in_this_page() {
      let amount = 0;
      this.orders.data.forEach(order => {
        amount += this.sum(order.payments, 'amount')
      })
      return amount;
    },
    total_due_in_this_page() {
      let paid = this.total_paid_in_this_page;
      let total = this.sum(this.orders.data, 'amount')
      return total - paid;
    },
    productsSold() {
      let products = {}
      for(let product in this.products){
        let loop_product = this.products[product]
        let item_total = 0;
        
        for(let order in this.orders.data) {
          for(let item in this.orders.data[order].items) {
            if(this.orders.data[order].items[item].product_id == loop_product.id) {
              item_total += this.orders.data[order].items[item].quantity
            }
          }
        }
        products[loop_product.name] = item_total
      }
      
      return products
    }
  },
  watch: {
    filter: {
      handler: _.debounce(function (state, old) {
        let query = {};
        if (state.search) query.search = state.search;
        if (state.due_ref) query.due_ref = state.due_ref;
        if (state.has_due) query.has_due = 1;
        if (state.per_page) query.per_page = state.per_page;

        this.getorders(query);
      }, 1000),
      deep: true,
    },
  },
  mounted() {
    let form = document.querySelector("#order_order_create_modal");
    let confirm = document.querySelector("#confirmModel");
    this.modal.form = new bootstrap.Modal(form);
    this.modal.confirm = new bootstrap.Modal(confirm);
    this.getSummary();
  },
  methods: {
    handelChange(index) {
      let item = this.form.items[index];
      if(!item.product_id) return
      let product = this.products[item.product_id];
      item.transport_rate = product.transport_rate
      item.rate = product.rate
      this.calculateItems()
    },
    calculateItems(){
      let total_amount = 0;
      for(let index in this.form.items){
        let item = this.form.items[index];
        item.product_price = round(item.rate * (item.quantity ?? 0), 2)
        item.transport = round((item.transport_rate ?? 0) * (item.quantity ?? 0), 2);
        item.amount = round(item.product_price + item.transport - item.discount)
        total_amount += item.amount
      }
      this.form.amount = total_amount
      this.form.paid = total_amount
    },
    handelPaidChange(){
      this.form.due = (this.form.amount ?? 0) - (this.form.paid ?? 0)
      if(this.form.due > 0) this.formHasDue = true
    },
    addItem(){
      this.form.items.push({
        product_id: null,
        rate: null,
        transport_rate: null,
        quantity: null,
        product_price: null,
        transport: null,
        discount: null,
        amount: null,
      })
    },
    removeItem(index){
      this.form.items.splice(index, 1);
    },
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
    getorders(filter = {}) {
      this.loadingTable = true;
      Inertia.get(this.route("order.order.index"), filter, {
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
    async getSummary(){
      this.summary.isLoading = true;
      await axios.get(this.route('order.order.summary'))
      .then(response => {
        this.summary.isLoading = false;
        this.summary.data = response.data;
      });
    },
    showModal(data = null) {
      this.isEditing = data !== null;
      this.modalTitle = data == null ? "Create Order" : "Update Order";
      this.form.clearErrors();
      if (this.isEditing) {
        this.editUrl = data.edit_url;
        this.form.ref = data.ref;
        this.form.customer_id = data.customer_id;
        this.form.amount = data.amount;
        this.form.note = data.note;
        this.form.due_ref = data.due_ref;
        this.form.due_date = data.due_date;
      } else {
        this.form.reset();
      }
      this.modal.form.show();
    },
    submit() {
      this.form.clearErrors();
      var url = this.isEditing ? this.editUrl : route("order.order.store");
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
    deleteorder(url) {
      this.deleteUrl = url;
      this.modal.confirm.show();
      Inertia.on("finish", () => {
        this.deleteUrl = null;
        this.modal.confirm.hide();
      });
    },
    sum: sum,
    hasDeliveries(items) {
      let total = 0;
      for(let index in items){
        total += items[index].deliveried
      }
      return true;
      return total > 0;
    }
    
  },
};
</script>

<style scoped>
  .item-row{
    border: 1px solid #eee;
    padding: 2px;
    margin-bottom: 2px;
  }
  i.fa {
    width: 20px;
  }
  
  .highlight{
    color: red!important;
  }
</style>
