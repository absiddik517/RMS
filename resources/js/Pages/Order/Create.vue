<template>
  <Content>
    <Card
    title="Create Order"
    >
      <template #title_right>
        <div class="custom-control custom-switch">
          <input v-model="form.new_customer" type="checkbox" class="custom-control-input" id="new_customer">
          <label class="custom-control-label" for="new_customer">New customer</label>
        </div>
      </template>
      <form @submit.prevent="submit">
        <div class="row">
          <Input v-model="form.ref" field="ref" group-class="col-6" label="Reference" :form="form"/>
          <Input v-show="form.new_customer" v-model="form.customer.name" group-class="col-6" field="customer.name" label="Name" :form="form" autocomplete="customers.name"/>
          <Input v-show="!form.new_customer" v-model="form.customer.name" @input="resetCustomer" group-class="col-6" field="customer.name" label="Name" :form="form" autocomplete="customers.name" autocomplete-route="autocomplete.customers" :autocomplete-additional-fields="['id', 'address', 'phone']" :getRow="getCustomer"/>
        </div>
        <div class="row">
          <Input v-model="form.customer.address" group-class="col-6" field="customer.address" label="Address" :form="form" autocomplete="customers.address" :disable-if="!form.new_customer"/>
          <Input v-model="form.customer.phone" group-class="col-6" field="customer.phone" label="Phone" :form="form" :disable-if="!form.new_customer"/>
        </div>
        <div class="row p-0">
          <div class="items_container col-sm-12 col-md-6" v-for="(item, index) in form.items">
          <div class="item_header d-flex justify-content-between align-items-center">
            <span>Item {{ index + 1 }}</span>
            <div>
              <Button v-show="form.items.length == (index + 1) && form.items.length != products.length" varient="success" @click="addItem" type="button">Add</Button>
              <Button v-show="form.items.length > 1" class="ml-2" varient="danger" type="button" @click="removeItem(index)">Remove</Button>
            </div>
          </div>
          <div class="item row">
            <Input v-model="item.product_id" @change="handelProductChange(index)" :field="'items.'+index+'.product_id'" label="Product" :form="form" type="select" :options="products" group-class="col-6"/>
            <Input v-model="item.quantity" :disable-if="!item.product_id" @input="calculateItems" :field="'items.'+index+'.quantity'" label="Quantity" :form="form" group-class="col-6"/>
            <Input v-model="item.rate" :disable-if="!item.product_id" @input="calculateItems" :field="'items.'+index+'.rate'" label="Rate" :form="form" group-class="col-6"/>
            <Input v-model="item.transport_rate" :disable-if="!item.product_id" @input="calculateItems" :field="'items.'+index+'.transport_rate'" label="Transport rate" :form="form" group-class="col-6"/>
            <Input v-model="item.product_price" :disable-if="true" :field="'items.'+index+'.product_price'" :form="form" label="Product price" group-class="col-6"/>
            <Input v-model="item.transport" :disable-if="true" :field="'items.'+index+'.transport'" label="Transport" :form="form" group-class="col-6"/>
            <Input v-model="item.amount" :disable-if="true" :field="'items.'+index+'.amount'" label="Amount" :form="form" group-class="col-6"/>
            <Input v-model="item.destination" :getRow="getDest" :disable-if="!item.product_id" :field="'items.'+index+'.destination'" label="Destination" :form="form" group-class="col-6" autocomplete="order_items.destination" />
          </div>
        </div>
        </div>
        
        <Input v-model="form.sub_total" field="sub_total" :form="form" :disable-if="true"/>
        <Input v-model="form.discount" @input="handelDiscountChange" field="discount" :form="form"/>
        <Input v-model="form.amount" field="amount" :form="form" :disable-if="true"/>
        <Input v-model="form.payment.amount" @input="handelPaidChange" field="payment.amount" label="Paid" :form="form"/>
        <Input v-model="form.due" field="due" :form="form" :disable-if="true"/>
        <div v-show="form.due > 0" class="row">
          <Input v-model="form.due_ref" field="due_ref" :form="form" autocomplete="orders.due_ref" group-class="col-6"/>
          <Input v-model="form.due_date" :min="today" field="due_date" :form="form" type="date" group-class="col-6"/>
        </div>
        <div class="form-group text-right">
          <Button :is-loading="form.processing" hide-label><i class="fa fa-save"></i></Button>
        </div>
        
        {{ today }}
      </form>
    </Card>
  </Content>
</template>

<script>

import {
  AdminLayout,
  Switch,
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
    DeleteConfirm,
    Card,
    Button,
    Dropdown,
    Filterth,
    Pagination,
  },
  props: {
    products: [Object, Array]
  },
  
  data(){
    return {
      form: useForm({
        new_customer: false,
        ref: '',
        customer_id: '',
        items: [{
          product_id: '',
          rate: '',
          transport_rate: '',
          quantity: '',
          product_price: '',
          transport: '',
          amount: '',
          destination: '',
        }],
        customer: {
          name: '',
          address: '',
          phone: '',
        },
        payment: {
          amount: '',
        },
        sub_total: '',
        discount: '',
        amount: '',
        due: '',
        due_ref: '',
        due_date: '',
        note: ''
      }),
    }
  },
  computed: {
    today(){
      const date = new Date();
      return Date.now();
      return date.getDate()+'-'+date.getMonth()+'-'+date.getYear()
    }
  },
  
  methods: {
    getCustomer(row){
      this.form.customer.address = row.address
      this.form.customer.phone = row.phone
    },
    resetCustomer(){
      this.form.customer.address = ''
      this.form.customer.phone = ''
    },
    getDest(row){
      console.log(row)
    },
    submit() {
      this.form.clearErrors();
      var url = route("order.order.store");
      this.form.post(url, {
        preserveScroll: true,
        preserveState: true,
        onSuccess: (res) => {
          if(res.props.toast.type == 'success') {
            this.form.reset();
            this.getNewRef()
          }
          this.getNewRef();
          //this.form.reset();
        },
      });
    },
    async getNewRef() {
      await axios.get(this.route('order.order.ref'))
      .then(response => {
        this.form.ref = response.data;
        console.log(response.data);
      });
    },
    handelProductChange(index) {
      let item = this.form.items[index];
      if(item.product_id == ''){
        item.rate = '',
        item.transport_rate = ''
      }else{
        item.rate = this.products[item.product_id].rate
        item.transport_rate = this.products[item.product_id].transport_rate
      }
      this.calculateItems()
    },
    calculateItems(){
      let item_amount = 0;
      for(let index in this.form.items){
        let item = this.form.items[index];
        item.product_price = round(item.rate * (item.quantity ?? 0))
        item.transport = round((item.transport_rate ?? 0) * (item.quantity ?? 0));
        
        item.amount = round(item.product_price + item.transport)
        item_amount += item.amount
      }
      this.form.sub_total = round(item_amount)
      this.form.amount = round(item_amount - (this.form.discount ?? 0))
      this.form.payment.amount = round(this.form.amount)
      this.form.due = '';
    },
    handelPaidChange() {
      this.form.due = this.form.amount - this.form.payment.amount
    },
    handelDiscountChange() {
      this.form.amount = this.form.sub_total - this.form.discount
      this.form.payment.amount = this.form.amount
      this.form.due = ''
    },
    addItem(){
      if(this.form.items.length > Object.keys(this.products).length){
        toast.add({
          type: 'error',
          message: 'No more item!'
        });
        return;
      }
      this.form.items.push({
          product_id: '',
          rate: '',
          transport_rate: '',
          quantity: '',
          product_price: '',
          transport: '',
          amount: '',
      })
    },
    removeItem(index){
      if(this.form.items.length == 1) return;
      this.form.items.splice(index, 1);
      this.calculateItems();
    },
    
    
  },
  mounted(){
    this.getNewRef()
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
    overflow: visible;
  }
</style>