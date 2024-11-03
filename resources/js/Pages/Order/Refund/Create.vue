<template>
  <Content>
    <div class="row">
      <div class="col-12">
        <Card varient="dark" title="Create Refund">
          <div>
            <form @submit.prevent="submit">
              <Select v-model="form.customer_id" label-text="Customer" from="order.refund.api.customer"/>
              <Select v-model="form.order_id" label-text="Order reference" from="order.refund.api.order" :depend-on="{customer_id: form.customer_id}"/>
              <Select v-model="form.order_item_id" @change="resetProductQuantity" :get-row="setItem" label-text="Product" :additional="['refundable']" include="refundable" from="order.refund.api.product" :depend-on="{order_id: form.order_id}"/>
              <Input v-model="form.quantity" type="number" :disable-if="!form.order_item_id" :max="item.refundable" @input="calculateAmount" field="quantity" label="Quantity" :form="form"/>
              <Input v-model="form.amount" :disable-if="!form.order_item_id" type="number" field="amount" label="Amount" :form="form"/>
              <Switch label="Create due payment of same amount" />
              <Switch label="Create cost of same amount" />
              <div class="form-group text-right">
                <Button>
                  <i class="fa" :class="{'fa-spinner fa-spin': form.processing, 'fa-save': !form.processing}"></i>
                  Save
                </Button>
              </div>
            </form>
          </div>
        </Card>
      </div>
    </div>
  </Content>
</template>

<script>
import { Content, AdminLayout, Card, Button, Input, Select, Switch } from '@/Components';
import { Inertia } from "@inertiajs/inertia";
import {useForm} from "@inertiajs/inertia-vue3";
export default {
  name: 'Index',
  layout: AdminLayout,
  components: {Content, Card, Button, Input, Select, Switch},
  data(){
    return {
      form: useForm({
        customer_id: '',
        order_id: '',
        order_item_id: '',
        product_id: '',
        quantity: '',
        amount: ''
      }),
      item: {},
    }
  },
  methods: {
    submit(){
      this.form.clearErrors();
      var url = route("order.refund.store");
      this.form.post(url, {
        preserveScroll: true,
        preserveState: true,
        onSuccess: (res) => {
          if(res.props.toast.type == 'success') {
            this.form.reset();
          }
        },
      });
    },
    setItem(item){
      console.log(item)
      this.item = item
      this.form.product_id = item.product_id
    },
    calculateAmount() {
      this.form.amount = Math.floor(this.form.quantity * this.item.rate)
    },
    resetProductQuantity(){
      this.form.quantity = ''
      this.form.amount = ''
    }
  }
}
</script>