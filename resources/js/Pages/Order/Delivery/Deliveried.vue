<template>
  <Content>
    <Card varient="gray" body-class="p-0" title="Deliveries">
      <template #title_right>
        <Button @click="print_page"><i class="fa fa-print"></i> Print</Button>
      </template>
      <div class="print-hide p-2 gy-2 d-flex justify-content-between align-items-center">
        <div class="print-hide">
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
        </div>
        <div class="d-flex justify-content-end align-items-center print-hide">
          <Dropdown animate stay header="Filter" id="filterorderDropdown">
            <template #btn>
              <i class="fa fa-filter"></i>
            </template>

            <div class="px-2 py-1">
              <Switch v-model="showProduct" v-for="(product, id) in products" :value="id" :label="product" />
            </div>
          </Dropdown>
        </div>
      </div>
      
      <div class="table-responsive">
        <table class="table table-border table-hover table-striped text-nowrap y-middle">
          <thead>
            <tr>
              <th>#</th>
              <th>Date</th>
              <th v-show="false">Customer</th>
              <th>Product</th>
              <th>Driver</th>
              <th class="text-right">Quantity</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(delivery, index) in deliveries.data" v-show="is_visiable(delivery.product_id)">
              <td>{{ index + 1 }}</td>
              <td>
                {{ delivery.date }}
              </td>
              <td v-show="false">
                <div class="d-flex flex-column">
                  <span><i class="fa fa-user text-muted" style="width: 18px;"></i> {{ delivery.customer.name }}</span>
                  <span><i class="fa fa-map-marker text-muted" style="width: 18px;"></i> {{ delivery.customer.address }}</span>
                  <span><i class="fa fa-phone text-muted" style="width: 18px;"></i> {{ delivery.customer.phone }}</span>
                </div>
              </td>
              <td>{{ delivery.product }}</td>
              <td>{{ delivery.driver }}</td>
              <td class="text-right">{{ delivery.quantity }}</td>
            </tr>
          </tbody>
          <tfoot>
            <tr v-for="(quantity, product) in total_in_this_page">
              <th colspan="4" class="text-center">{{ product }}</th>
              <th class="text-right">{{ quantity }}</th>
            </tr>
          </tfoot>
        </table>
      </div>
    </Card>
  </Content>
    
</template>

<script>
import {
  Content,
  AdminLayout,
  Card,
  Input,
  Dropdown,
  Switch,
  Button,
  Modal,
  DeleteConfirm,
  Spinner,
  Pagination,
} from "@/Components";
import { round, sum } from "@/Composable/functions";
export default {
  name: 'Deliveried', 
  layout: AdminLayout,
  components: {Content, Card, Dropdown, Input, Switch, Button,},
  props: {
    deliveries: Object,
    params: Object,
    products: Object,
  },
  data() {
    return {
      filter: {
        search: this.params.search ?? '',
        per_page: this.params.per_page ?? 5,
      },
      showProduct: [],
    }
  },
  mounted() {
    for(let id in this.products){
      this.showProduct.push(id)
      console.log(id)
    }
    this.is_visiable(1)
  },
  computed: {
    total_in_this_page() {
      let products = {}
      for(let id in this.products){
        products[this.products[id]] = sum(this.deliveries.data, 'quantity', ['product_id', id]);
      }
      return products
    }
  },
  
  methods: {
    is_visiable(id){
      for(let index in this.showProduct){
        if(this.showProduct[index] == id){
          return true;
        }
      }
      return false;
    },
    print_page() {
      window.print()
    }
  }
  
}
</script>