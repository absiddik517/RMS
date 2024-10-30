<template>
  <Content>
    <form @submit.prevent="submit">
    <div class="bill-wrapper"> <!-- wrapper -->
      <div v-show="false" class="bill-pad"> <!-- company pad -->
        <div class="logo d-flex justify-content-center align-items-center">
          <img src="../../../../storage/app/public/images/AdminLTELogo.png" alt="">
        </div>
        <div class="flex-1 text-center">
          <h1>Company Name</h1>
          <p style="">Company address will goes here.<br/>
          Phone: multiple phone number will goes here. <br/>
          Email: multiple phone number will goes here.</p>
          <span class="bill-title">Memo</span>
        </div>
        <div class="logo"></div>
      </div>
      
      <div class="customer my-3 px-3"> <!-- customer info -->
        <div class="d-flex justify-content-between">
          <div>
            <label for="ref_input" class="text-muted" style="font-weight: normal;">Memo no.:</label>
            <input v-model="form.ref" type="text" class="is-invalid" placeholder="Memo no." style="width: 80px;"/>
            <div class="inline-flex text-red">{{ form.errors.ref }}</div>
          </div>
          
          <div>
            <!-- Default checked -->
            <div class="custom-control custom-switch">
              <input v-model="form.newCustomer" type="checkbox" class="custom-control-input" id="new_customer">
              <label class="custom-control-label" for="new_customer">New customer</label>
            </div>
          </div>
        </div>
        
        <div class="d-flex align-items-center">
          <label for="customer_name_input" class="text-muted">Name: </label>
          <div>
            <input 
              @input="fetchAutocomplete" 
              @keydown.down="heighlightNext"
              @keydown.up="heighlightPrevious"
              @keydown.enter="selectHeighlighted"
              v-model="form.name"
              ref="name"
              id="customer_name_input"
              type="text" class="flex-1" placeholder="Customer name">
            <div ref="list" v-show="autocomplete.data && autocomplete.showSuggestion && !form.newCustomer" class="autocomplete-container">
              <ul class="autocomplete-list">
                <li v-for="(item, index) in autocomplete.data" :class="{ selected: index === autocomplete.heighlightIndex}" @click="selectHeighlighted" @mouseenter="autocomplete.heighlightIndex = index" :key="index">{{ item.name }}</li>
              </ul>
            </div>
            <div>{{ form.errors.name }}</div>
          </div>
        </div>
        
        <div class="row">
          <div class="col-sm-12 col-md-6">
            <span v-show="!form.newCustomer"><span class="text-muted">Address:</span> 
            {{ form.address }}
            </span>
            <label v-show="form.newCustomer" for="customer_address" class="text-muted">Address</label>
            <input 
              @input="fetchAddress" 
              @keydown.down="heighlightNext"
              @keydown.up="heighlightPrevious"
              @keydown.enter="selectHeighlightedAddress"
              ref="address"
              v-show="form.newCustomer" 
              v-model="form.address" 
              type="text" 
              placeholder="Address">
            <div ref="list" v-show="autocomplete.data && autocomplete.showSuggestion && form.newCustomer" class="autocomplete-container">
              <ul class="autocomplete-list">
                <li v-for="(item, index) in autocomplete.data" :class="{ selected: index === autocomplete.heighlightIndex}" @click="selectHeighlighted('address')" @mouseenter="autocomplete.heighlightIndex = index" :key="index">{{ item }}</li>
              </ul>
            </div>
          </div>
          <div class="col-sm-12 col-md-6">
            <span v-show="!form.newCustomer"><span class="text-muted">Phone:</span> {{ form.phone }}</span>
            <label v-show="form.newCustomer" for="customer_address" class="text-muted">Phone</label>
            <input v-show="form.newCustomer" v-model="form.phone" type="text" placeholder="01xxxxxxxxx">
          </div>
        </div>
      </div>
      
      <div class="px-3"> <!-- items -->
        <table>
          <thead>
            <tr>
              <th>SL</th>
              <th>Product</th>
              <th>Quantity</th>
              <th>Rate</th>
              <th>Amount</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(item, index) in form.items">
              <td>
                <Dropdown
                  animate
                  stay
                  header="Transport"
                  id="filterColumnToggle"
                >
                  <template #btn>
                    {{ index + 1 }}
                  </template>
                  <Button @click="removeItem(index)" btnDropdown classes="d-flex justify-content-between" type="Button">
                    Remove <i class="fa fa-trash"></i>
                  </Button>
                  
                  <label :for="'transport'+index" class="mx-3 d-flex justify-content-between icheck-success">
                    With transport
                    <input v-model="item.with_transport" @check="alert('ok')" type="checkbox" :id="'transport'+index">
                  </label>
                </Dropdown>
              </td>
              <td>
                <select class="is-invalid" v-model="item.product_id" style="width: 80px;" @change="handelProductChange(index)">
                  <option value="">Product</option>
                  <option v-for="(product, id) in products" :value="id">{{ product.name }}</option>
                </select>
                <div>{{ form.errors['items.'+index+'.product_id'] }}</div>
              </td>
              <td>
                <input v-model="item.quantity" @input="calculateItems" type="number" placeholder="Quantity" style="width: 80px;">
              </td>
              <td><input v-model="item.rate" @input="calculateItems" type="text" placeholder="Rate" style="width: 60px;"></td>
              <td class="text-right">{{ item.product_price ?? '' }}</td>
            </tr>
            <tr v-for="(item, index) in form.items" v-show="item.with_transport">
              <td class="text-center">
                <i class="fa fa-user"></i>
              </td>
              <td class="text-center">
                {{ item.product_id ? products[item.product_id].name : '' }}
              </td>
              <td class="text-center">
                {{ item.quantity ? item.quantity : '' }}
              </td>
              <td><input v-model="item.transport_rate" @input="calculateItems" type="text" placeholder="Transport rate" style="width: 60px;"></td>
              <td class="text-right"> {{ item.transport ?? '' }}</td>
            </tr>
            
            <tr v-for="index in (Object.keys(products).length + 1 - form.items.length)" :key="index">
              <td v-if="index == 1 && form.items.length < Object.keys(products).length" class="text-center"><button @click="addItem" class="btn btn-dark btn-sm"><i class="fa fa-plus"></i></button></td>
              <td v-else>&nbsp;</td>
              <td></td>
              <td></td>
              <td></td>
              <td></td>
            </tr>
            
            <tr class="bt">
              <td colspan="4" class="text-center">Sub total</td>
              <td class="text-right">{{ form.item_total }}</td>
            </tr>
            <tr class="bt">
              <td colspan="4" class="text-center">Discount <span v-show="form.discount_p > 0">({{ form.discount_p }}%)</span> </td>
              <td class="text-right">
                <input @input="handelDiscountChange" v-model="form.discount" type="text" style="width: 90px; text-align: right;">
              </td>
            </tr>
            <tr class="bt">
              <td colspan="4" class="text-center">Total</td>
              <td class="text-right">{{ form.amount }}</td>
            </tr>
            <tr class="bt">
              <td colspan="4" class="text-center">Paid</td>
              <td class="text-right">
                <input v-model="form.paid" max="form.amount" @input="handelPaidChange" type="text" style="width: 90px; text-align: right;">
              </td>
            </tr>
            <tr class="bt">
              <td colspan="4" class="text-center">Due</td>
              <td class="text-right">{{ form.due > 0 ? form.due : '' }} <i v-show="form.due < 1" class="fa fa-times"></i></td>
            </tr>
          </tbody>
        </table>
      </div>
      
      <div class="px-3"> <!-- footer -->
        <div class="py-2">
          <div class="d-flex justify-content-between">
            <Input v-model="form.due_ref" group-class="flex-1" without-label :form="form" field="due_ref" autocomplete="customers.name"/>
            <Input type="date" v-model="form.due_date" group-class="flex-1" without-label :form="form" field="due_date"/>
          </div>
          <div class="d-flex justify-content-end">
            <Button @click="submit" :isLoading="form.processing">
              <i class="fa fa-save"></i>
            </Button>
          </div>
          {{ form }}
        </div>
      </div>
    </div>
    </form>
  </Content>
</template>

<script>
import {
  AdminLayout,
  Modal,
  Switch,
  Input,
  Spinner,
  Dropdown,
  Button,
  Content,
} from "@/Components";
import {useForm} from "@inertiajs/inertia-vue3";
import { round, toWord } from "@/Composable/functions";
import { reactive } from 'vue'

export default {
  name: "Order",
  layout: AdminLayout,
  components: {
    AdminLayout, Modal, Input, Switch, Spinner, Dropdown, Button, Content,
  },
  props: {
    products: [Array, Object],
    errors: Object,
  },
  data() {
    return {
      form: useForm({
        newCustomer: false,
        ref: null,
        customer_id: null,
        name: '',
        address: '',
        phone: '',
        amount: null,
        note: null,
        due_ref: null,
        due_date: null,
        items: [
          {
            product_id: '',
            rate: null,
            with_transport: false,
            transport_rate: null,
            quantity: null,
            product_price: null,
            transport: null,
            amount: null,
          }
        ],
        item_total: null,
        discount: null,
        paid: null,
        due: null,
        hasDue: false,
        discount_p: null,
        inWord: null,
      }),
      autocomplete: reactive({
        data: null,
        heighlightIndex: 0,
        fetchingData: false,
        showSuggestion: true,
      })
    }
  },
  mounted(){
    this.$refs.name.addEventListener('focus', () => {
      this.autocomplete.showSuggestion = true
    })
    this.$refs.name.addEventListener('blur', () => {
      if(this.form.newCustomer) return
      if(!this.form.customer_id){
        this.form.name = null
      }
    })
    document.addEventListener("click", event => {
      if(!this.$refs.list.contains(event.target)){
        this.autocomplete.showSuggestion = false
      }
    });
  },
  methods: {
    submit() {
      this.form.clearErrors();
      var url = route("order.order.store");
      this.form.post(url, {
        preserveScroll: true,
        onSuccess: () => {
          this.form.reset();
          this.modal.form.hide();
        },
      });
    },
    handelProductChange(index) {
      let item = this.form.items[index];
      item.rate = this.products[item.product_id].rate
      if(item.with_transport) item.transport_rate = this.products[item.product_id].transport_rate
      this.calculateItems()
    },
    calculateItems(){
      let item_amount = 0;
      for(let index in this.form.items){
        let item = this.form.items[index];
        item.product_price = round(item.rate * (item.quantity ?? 0))
        if(item.with_transport){
          item.transport = round((item.transport_rate ?? 0) * (item.quantity ?? 0));
        }else {
          item.transport = 0;
        }
        item.amount = round(item.product_price + item.transport)
        item_amount += item.amount
      }
      this.form.item_total = round(item_amount)
      this.form.amount = round(item_amount - (this.form.discount ?? 0))
      this.form.paid = round(this.form.amount)
      this.form.due = null;  this.form.hasDue = false,
      this.form.inWord = toWord(this.form.amount)
    },
    handelPaidChange(){
      this.form.due = this.form.amount - this.form.paid
      if(this.form.due > 0) {
        this.form.hasDue = true
      }else {
        this.form.hasDue = false
      }
    },
    handelDiscountChange(){
      this.calculateItems()
      let discount_persent = round((this.form.discount / this.form.item_total) * 100, 2)
      this.form.discount_p = discount_persent
    },
    addItem(){
      if(this.form.items.length > Object.keys(this.products).length){
        alert('No more items!');
        return;
      }
      this.form.items.push({
        product_id: '',
        rate: null,
        with_transport: false,
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
      this.calculateItems();
    },
    
    async fetchAutocomplete(event){
      if(this.form.newCustomer) return
      let value = event.target.value
      if(!value) return;
      this.autocomplete.fetchingData = true;
      this.form.address = null
      this.form.phone = null
      this.form.customer_id = null
      let data = {
        v: value
      };
      await axios.get(this.route('autocomplete.customers', data))
      .then(response => {
        this.autocomplete.heighlightIndex = 0;
        this.autocomplete.data = response.data
        this.autocomplete.showSuggestion = true
        this.autocomplete.fetchingData = false
      });
    },
    async fetchAddress(event){
      let value = event.target.value
      if(!value) return;
      this.autocomplete.fetchingData = true;
      let data = {
        t: 'customers',
        f: 'address',
        v: value
      };
      await axios.get(this.route('autocomplete', data))
      .then(response => {
        this.autocomplete.heighlightIndex = 0;
        this.autocomplete.data = response.data
        this.autocomplete.showSuggestion = true
        this.autocomplete.fetchingData = false
      });
    },
    
    selectHeighlighted(event) {
      event.preventDefault()
      if(this.autocomplete.data.length == 0) return
      if(this.form.newCustomer) {
        this.form.address = this.autocomplete.data[this.autocomplete.heighlightIndex]
        this.autocomplete.showSuggestion = false
        return
      }
      let item = this.autocomplete.data[this.autocomplete.heighlightIndex]
      this.form.name = item.name
      this.form.address = item.address
      this.form.phone = item.phone
      this.form.customer_id = item.id
      this.autocomplete.showSuggestion = false;
    },
    selectHeighlightedAddress(event) {
      event.preventDefault()
      if(this.autocomplete.data.length == 0) return
      let item = this.autocomplete.data[this.autocomplete.heighlightIndex]
      this.form.address = item
      this.autocomplete.showSuggestion = false;
    },
    heighlightNext(event) {
      event.preventDefault()
      if(this.autocomplete.data.length > this.autocomplete.heighlightIndex + 1){
        this.autocomplete.heighlightIndex++;
      }
    },
    heighlightPrevious(event) {
      event.preventDefault()
      if(this.autocomplete.heighlightIndex > 0){
        this.autocomplete.heighlightIndex--;
      }
    },
  }
};
</script>

<style scoped>
  .bill-wrapper {
      border: 1px solid #ddd;
      border-radius: 4px;
      box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2);
      padding-top: 15px;
      background: rgb(194,194,194);
      background: -webkit-linear-gradient(bottom left, rgba(194,194,194,1) 0%, rgba(255,255,255,1) 50%, rgba(194,194,194,1) 100%);
      background: -moz-linear-gradient(bottom left, rgba(194,194,194,1) 0%, rgba(255,255,255,1) 50%, rgba(194,194,194,1) 100%);
      background: -o-linear-gradient(bottom left, rgba(194,194,194,1) 0%, rgba(255,255,255,1) 50%, rgba(194,194,194,1) 100%);
      background: linear-gradient(to top right, rgba(194,194,194,1) 0%, rgba(255,255,255,1) 50%, rgba(194,194,194,1) 100%);
    }
  
  .bill-pad {
    display: flex;
    border-bottom: 1px solid #ddd;
    padding-bottom: 15px;
  }
  
  .bill-pad .logo, .bill-pad .blank {
    width: 150px;
  }
  
  .bill-pad .content {
    width: auto;
  }
  
  .bill-title {
   padding: 8px 25px;
   background: #7de5ff;
   color: #0d83a1;
   border-radius: 0.4rem;
  }
  
  .bill-wrapper input:focus {
    outline: none;
    border-bottom: 1px dashed #4bff5d;
  }
  .bill-wrapper input {
    background: transparent;
    border: none;
    border-bottom: 1px dashed #ddd;
    padding-top: 2px;
    padding-bottom: 2px;
    padding-left: 8px;
    width: auto;
  }
  
  .bill-wrapper table {
    display: table;
    width: 100%;
  }
  
  .bill-wrapper table thead tr th{
    text-align: center;
    border-top: 1px solid #000;
    border-bottom: 1px solid #000;
  }
  .bill-wrapper table thead tr th, 
  .bill-wrapper table tbody tr td {
    border-left: 1px solid #000;
    border-right: 1px solid #000;
  }
  
  .bt {
    border-top: 1px solid #000;
  }
  
  .bill-wrapper table tbody tr:last-child td {
    border-bottom: 1px solid #000;
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
