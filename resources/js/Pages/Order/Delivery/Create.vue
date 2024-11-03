<template>
  <Content>
    <Card
    varient="dark"
    title="Create Order Delivery"
    >
      <template #title_right>
        <Button @click="modal.show()" varient="light"><i class="fa fa-search"></i></Button>
      </template>
      <form @submit.prevent="submit">
        <div class="row">
          <Input v-model="form.dref" field="dref" id="dref" group-class="col-6" label="Delivery No." :form="form" :loading="loading.dref"/>
          <Input v-model="form.order_ref" :loading="loading.order_ref" id="memo_number" field="order_ref" group-class="col-6" label="Memo No." :form="form" autocomplete="orders.ref"/>
        </div>
        <div class="row" v-show="form.customer.name">
          <Input v-model="form.customer.name" group-class="col-6" optional field="customer.name" label="Name" :form="form" :disable-if="true"/>
          <Input v-model="form.customer.address" group-class="col-6" optional field="customer.address" label="Address" :form="form" :disable-if="true"/>
        </div>
        <div class="row" v-show="form.customer.real_due > 0 || form.customer.new_due > 0">
          <Input v-model="form.customer.real_due" group-class="col-6" optional field="customer.real_due" label="Real Due" :form="form" :disable-if="true"/>
          <Input v-model="form.customer.new_due" group-class="col-6" optional field="customer.new_due" label="New Due" :form="form" :disable-if="true"/>
        </div>
        
        <div class="row">
          <Select v-model="form.product_id" group-class="col-6" :options="order_product" :disable-if="!form.customer.name" label-text="Product" track-by="product_id" label="product_name" :searchable="false" />
          <Input v-model="form.quantity" :help-text="deliveryableHelpline" type="number" :disable-if="!form.product_id" :max="deliveryable" group-class="col-6" field="quantity" label="Quantity" :form="form" @input="calculate_new_due" />
          <Input v-model="form.driver" :disable-if="!form.product_id" group-class="col-6" field="driver" label="Driver" :form="form" autocomplete="order_deliveries.driver"/>
          <Input v-model="form.destination" :disable-if="!form.product_id" group-class="col-6" field="destination" label="Destination" :form="form" autocomplete="order_deliveries.destination"/>
        </div>
        <div class="form-group text-right">
          <Button :is-loading="form.processing" hide-label><i class="fa fa-save"></i></Button>
        </div>
        
      </form>
    </Card>
  </Content>
  <Modal id="find_customer" title="Find Customer" varient="light">
    <template #body>
      <form @submit.prevent="submit" novalidate="novalidate">
        <Input v-model="filter_customer_form.name" :form="filter_customer_form" field="name" autocomplete="customers.name"/>
        <Input v-show="filter_customer.address" v-model="filter_customer_form.address" :form="filter_customer_form" field="address" autocomplete="customers.address"/>
        <Input v-show="filter_customer.phone" v-model="filter_customer_form.phone" :form="filter_customer_form" field="phone" autocomplete="customers.phone"/>
        <Input v-show="filter_customer.email" v-model="filter_customer_form.email" :form="filter_customer_form" field="email" autocomplete="customers.email"/>
      </form>
      <div>
        <label v-for="customer in filtered_customers.data" class="customer d-flex justify-content-between align-items-center">
          <div class="flex-1 d-flex flex-column">
            <span class="text-bold"><i class="fa fa-user"></i> {{ customer.name }}</span>
            <span class="text-normal"><i class="fa fa-map-marker"></i> {{ customer.address }}</span>
            <span class="text-normal"><i class="fa fa-phone"></i> {{ customer.phone }}</span>
            <span class="text-normal"><i class="fa fa-envalop"></i> {{ customer.email }}</span>
          </div>
          <div><input v-model="filtered_customers.id" :disabled="customer.orders_count == 0" :value="customer.id" type="radio" name="" id=""></div>
        </label>
      </div>
      
      <div>
        <div @click="useRef(order.ref)" class="customer d-flex justify-content-between align-items-center" v-for="order in filtered_customers.orders">
          <div class="d-flex flex-column">
            <span>{{ order.date }}</span>
            <span>{{ order.ref }}</span>
          </div>
          <div class="d-flex flex-column">
            <span class="bordered" v-for="item in order.items">
              {{ item.name }} : {{ item.quantity }} - {{ item.deliveried }} = <span v-if="item.quantity - item.deliveried == 0"><i class="fa fa-check-circle text-blue"></i></span><span v-else>{{ item.quantity - item.deliveried }}</span>
            </span>
          </div>
        </div>
      </div>
    </template>

    <template #action>
      <Button @click="get_customer_filter_data">
        <i class="fa" :class="{'fa-spinner fa-spin': loading.filter_customer, 'fa-filter': !loading.filter_customer}"></i> Filter
      </Button>
      <Button @click="get_customer_orders">
        <i class="fa" :class="{'fa-spinner fa-spin': loading.order, 'fa-eye': !loading.order}"></i> Orders
      </Button>
    </template>
  </Modal>
  
</template>

<script>

import {
  AdminLayout,
  Switch,
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
} from "@/Components";
import { Inertia } from "@inertiajs/inertia";
import {useForm} from "@inertiajs/inertia-vue3";
import { round, toWord } from "@/Composable/functions";
import { reactive } from 'vue'
import toast from '@/Store/toast.js'
export default {
  layout: AdminLayout,
  components: {
    Spinner,
    Switch,
    Modal,
    Content,
    Input,
    Select,
    DeleteConfirm,
    Card,
    Button,
    Dropdown,
    Filterth,
    Pagination,
  },
  props: {
    products: [Object, Array],
    ref_no: [String, Number]
  },
  
  data(){
    return {
      form: useForm({
        dref: '',
        order_ref: this.ref_no,
        order_id: '',
        product_id: '',
        order_item_id: '',
        quantity: '',
        destination: '',
        driver: '',
        customer: {
          name: '',
          address: '',
          real_due: '',
          new_due: ''
        },
      }),
      filter_customer_form: useForm({
        name: '',
        address: '',
        phone: '',
        email: ''
      }),
      filter_customer: reactive({
        address: false,
        phone: false,
        email: false,
      }),
      filtered_customers: {
        data: {},
        orders: {},
        id: '',
      },
      filter: reactive({
        order_ref: null,
        customer_name: null,
      }),
      loading: reactive({
        order_ref: false,
        dref: false,
        filter_customer: false,
        order: false,
      }),
      order_product: null,
      customer: null,
      deliveryable: 420,
      modal: null
    }
  },
  
  computed: {
    deliveryableHelpline() {
      console.log(this.order_product)
      if(!this.form.product_id) return '';
      let text = `${this.deliveryable} ${this.products[this.form.product_id].unit} ${this.products[this.form.product_id].name} remaining to be delivered. `
      let quantity = Math.floor((this.form.customer.real_due * -1) / (this.order_product[this.form.product_id].rate - this.customer.discount_rate/100));
      if(quantity < this.deliveryable && quantity > 0) {
        text += `${quantity} ${this.products[this.form.product_id].unit} is safe and due free.`
      }
      return text
    }
  },
  
  methods: {
    submit() {
      this.form.clearErrors();
      var url = route("order.delivery.store");
      this.form.post(url, {
        preserveScroll: true,
        preserveState: true,
        onSuccess: (res) => {
          if(res.props.toast.type == 'success') {
            this.form.reset();
            this.getNewDref()
          }
        },
      });
    },
    async get_customer_filter_data () {
      this.filtered_customers.orders = {}
      this.filtered_customers.id = null
      const data = {
        name: this.filter_customer_form.name,
        address: this.filter_customer_form.address,
        phone: this.filter_customer_form.phone,
        email: this.filter_customer_form.email,
      }
      this.loading.filter_customer = true;
      await axios.post(this.route('order.customer.filter'), data)
      .then(response => {
        console.log(response.data);
        this.loading.filter_customer = false;
      })
      .catch(error => {
        console.log(error)
        this.loading.filter_customer = false;
        this.filtered_customers.data = error.response.data;
        if(error.response.status == 501) {
          for(let index in data) {
            if(data[index] == '') {
              this.filter_customer[index] = true;
              return;
            }
          }
        }
      });
    },
    async get_customer_orders () {
      this.loading.order = true;
      await axios.get(this.route('order.customer.filter.order', this.filtered_customers.id))
      .then(response => {
        console.log(response.data)
        this.filtered_customers.orders = response.data
        this.loading.order = false;
      })
      .catch(error => {
        console.log(error)
        this.loading.order = false;
      });
    },
    setOrderItemId() {
      if(!this.form.product_id) {
        this.form.order_item_id = 'nai'
      }else{
        this.form.order_item_id = 'ase'
        console.log(this.order_product[this.form.product_id].order_item_id);
      }
      return true;
    },
    async getNewDref() {
      this.loading.dref = true;
      await axios.get(this.route('order.delivery.api.dref'))
      .then(response => {
        this.form.dref = response.data;
        this.loading.dref = false
      })
      .catch(error => {
        this.loading.dref = false;
      });
    },
    
    // find customer
    async findCostomer() {
      await axios.get(this.route('order.order.order_ref'))
      .then(response => {
        this.form.customer.name = response.data.customer.name;
        this.form.customer.address = response.data.customer.address;
        console.log(response.data);
      });
    },
    async getProducts(order_ref) {
      this.form.errors.order_ref = null
      this.loading.order_ref = true;
      await axios.get(this.route('order.delivery.api.products', order_ref))
      .then(response => {
        this.form.customer.name = response.data.customer.name;
        this.form.customer.address = response.data.customer.address;
        this.form.customer.real_due = response.data.customer.real_due;
        this.form.order_id = response.data.customer.order_id;
        this.loading.order_ref = false
        this.order_product = response.data.products
        this.customer = response.data.customer
        console.log(response.data);
      })
      .catch(error => {
        this.form.customer.name = '';
        this.form.customer.address = '';
        this.form.order_id = '';
        this.order_product = null;
        this.form.errors.order_ref = error.response.data.message;
        this.loading.order_ref = false
        console.log(error.response.data.message)
      });
    },
    useRef(ref){
      let count = 0;
      for(let index in this.filtered_customers.orders){
        if(this.filtered_customers.orders[index].ref == ref) {
          for(let item in this.filtered_customers.orders[index].items){
            count += this.filtered_customers.orders[index].items[item].quantity - this.filtered_customers.orders[index].items[item].deliveried
          }
        }
      }
      if(count > 0){
        this.form.order_ref = ref;
        this.getProducts(ref);
        this.modal.hide()
      }
    },
    calculate_new_due() {
      //if(this.form.quantity > this.deliveryable) this.form.quantity = this.deliveryable
      const product = this.order_product[this.form.product_id];
      const worth = this.form.quantity * product.rate 
      const due = worth - (worth / 100) * this.customer.discount_rate;
      this.form.customer.new_due = Math.round(due + this.form.customer.real_due)
    },
  },
  mounted(){
    if(this.ref_no) this.getProducts(this.form.order_ref)
    this.getNewDref()
    let find_customer_model = document.querySelector("#find_customer");
    this.modal = new bootstrap.Modal(find_customer_model);
    
    $('#memo_number').on('blur', () => {
      if(!this.form.order_ref) return
      this.getProducts(this.form.order_ref)
    })
    $('#memo_number').on('input', () => {
      this.form.customer.name = '';
      this.form.customer.address = '';
      this.form.customer.real_due = '';
      this.form.customer.new_due= '';
      this.form.product_id = '';
      this.order_product = null
    })
    
    $('#dref').on('focus', this.getNewDref)
    
    $('#product_id').on('change', () => {
      console.log(this.order_product)
      console.log(this.form.product_id)
      this.form.order_item_id = this.order_product[this.form.product_id].order_item_id
      this.form.destination = this.order_product[this.form.product_id].destination
      if(this.form.product_id){
        this.deliveryable = this.order_product[this.form.product_id].deliveryable;
      }else{
        this.deliveryable = 0;
      }
    })
  },
}
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
  }
  .text-normal {
    font-weight: normal;
  }
  
.customer {
  background: #fff;
  margin-bottom: 0.5rem;
  border-radius: 0.3rem;
  padding: 0.4rem 0.7rem;
  overflow: hidden;
  border: 1px solid #eee;
  box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);
}
  
  
  .autocomplete-container{
    width: 100%;
    position: relative;
  }
  .autocomplete-list {
    position: absolute;
    width: 100%;
    z-index: 999;
    padding: 0;
    box-shadow: 2px 2px 5px gray;
  }
  .autocomplete-list li {
    padding: 10px;
    cursor: pointer;
    background-color: #f9f9f9;
    border-bottom: 1px solid #ddd;
    list-style: none;
    margin: 0;
  }
  .autocomplete-list li:hover, .selected{
    background-color: #85e7ff!important;
  }
</style>