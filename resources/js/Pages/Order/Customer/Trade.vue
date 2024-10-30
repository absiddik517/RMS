<template>
  <Content>
    <div class="row">
      <div class="col-12">
        <Card varient="gray">
          <template #title>
            Customer
          </template>
          
          <div class="d-flex justify-content-between">
            <div class="text-muted"><i class="fa fa-user"></i> Name</div>
            <div>{{ customer.name }}</div>
          </div>
          <div class="d-flex justify-content-between">
            <div class="text-muted"><i class="fa fa-map-marker"></i> Address</div>
            <div>{{ customer.address }}</div>
          </div>
          <div class="d-flex justify-content-between">
            <div class="text-muted"><i class="fa fa-phone"></i> Phone</div>
            <div>{{ customer.phone }}</div>
          </div>
          <div class="d-flex justify-content-between">
            <div class="text-muted"><i class="fa fa-clock"></i> First Trade At</div>
            <div>{{ customer.created_at }}</div>
          </div>
          <div class="d-flex justify-content-between">
            <div class="text-muted"><i class="fa fa-dollar-sign"></i> Total Trade</div>
            <div>{{ trade_total }}</div>
          </div>
          <div class="d-flex justify-content-between">
            <div class="text-muted"><i class="fa fa-dollar-sign"></i> Payment Recieved</div>
            <div>{{ trade_paid }}</div>
          </div>
          <div class="d-flex justify-content-between">
            <div class="text-muted"><i class="fa fa-dollar-sign"></i> Deliveried product worth</div>
            <div>{{ deivery_product_worth }}</div>
          </div>
          <div class="d-flex justify-content-between">
            <div class="text-muted"><i class="fa fa-dollar-sign"></i> Real due</div>
            <div>{{ deivery_product_worth - trade_paid }}</div>
          </div>
          <div class="d-flex justify-content-between">
            <div class="text-muted"><i class="fa fa-dollar-sign"></i> Due as listed</div>
            <div>{{ trade_total - trade_paid }}</div>
          </div>
        </Card>
        <Card varient="gray">
          <template #title>
            Trades
          </template>
          
          <div> <!-- wrapper -->
            <div class="item d-flex justify-content-between align-items-center" v-for="trade in trades">
              <div class="d-flex flex-column mr-2">
                <span>{{ trade.date }}</span>
                <span>Ref: {{ trade.ref }}</span>
              </div>
              <div>
                <div class="d-flex flex-column bordered" v-for="item in trade.items">
                  <div class="d-flex align-items-center justify-content-between">
                    <span class="mr-2">{{ item.name }}</span>
                    <div class="d-flex flex-column text-right">
                      <span>{{ item.quantity }} <i class="fa fa-times"></i> {{ item.rate }} = {{ item.product_price }}</span>
                      <span><i class="fa fa-truck text-muted"></i> {{ item.quantity }} <i class="fa fa-times"></i> {{ item.transport_rate }} = {{ item.transport }}</span>
                      <span><i class="fa fa-check-circle" :class="{'text-danger': item.quantity != item.deliveried, 'text-success': item.quantity == item.deliveried}"></i> <span class="text-muted">Deliveried : </span> {{ item.deliveried }}</span>
                    </div>
                  </div>
                </div>
              </div>
              
              <div class="flex-1 d-flex flex-column justify-content-between align-items-end">
                <span class="text-info">{{ trade.sub_total}}</span>
                <span class="text-danger"> (-) {{ trade.discount}}</span>
                <span class="text-success">{{ trade.amount}}</span>
              </div>
            </div>
          </div> <!-- wrapper -->
          
          <div class="item d-flex justify-content-between">
            <span>Total</span>
            <span>{{ trade_total }}</span>
          </div>
          
        </Card>
        <Card varient="gray">
          <template #title>
            Payments
          </template>
          
          <div> <!-- wrapper -->
            <div class="item d-flex align-items-center" v-for="payment in payments">
              <span class="mr-2">{{ payment.date }}</span>
              <span class="mr-2">{{ payment.description }}</span>
              <span class="text-right flex-1">{{ payment.amount }}</span>
            </div>
          </div> <!-- wrapper -->
          
          <div class="item d-flex justify-content-between">
            <span>Total</span>
            <span>{{ trade_paid }}</span>
          </div>
          
        </Card>
      </div>
    </div>
  </Content>
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
import { sum } from '@/Composable/functions'

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
    customer: Object,
    trades: Object,
    payments: Object,
  },
  computed:{
    trade_total() {
      return sum(this.trades, 'amount')
    },
    trade_paid() {
      return sum(this.payments, 'amount')
    },
    deivery_product_worth(){
      let output = [];
      for(let index in this.trades){
        const trade_discount = this.trades[index].discount
        const trade_sub_total = this.trades[index].sub_total
        const discount_rate = trade_discount * 100 / trade_sub_total
        
        for(let item in this.trades[index].items) {
          const worth = this.trades[index].items[item].deliveried * (this.trades[index].items[item].rate + this.trades[index].items[item].transport_rate)
          const with_discount = worth - ((worth / 100) * discount_rate);
          output.push(with_discount)
        }
      }
      
      let delivery_worth = 0;
      for(let index in output){
        delivery_worth += output[index];
      }
      return Math.round(delivery_worth)
    }
  },
  
  methods:{
    sum: sum,
    discount_rate: function(total, amount) {
      return amount*100/total
    }
  }
};
</script>

<style scoped>
  .fa{
    width: 20px;
  }
  
  .fa-times{
    width: auto;
  }
  
.item {
  background: #fff;
  margin-bottom: 0.5rem;
  border-radius: 0.3rem;
  padding: 0.4rem 0.7rem;
  overflow: hidden;
  border: 1px solid #eee;
  box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);
}

.bordered {
  border: 1px solid #ddd;
  padding: 2px 5px;
  margin-bottom: 5px;
  border-radius: 4px;
}
</style>
