<template>
  <Content>
    <div class="row">
      <div class="col-12">
        <Card varient="gray" body-class="p-0">
          <template #title>
            Delivery <i v-show="loadingTable" class="fa fa-spinner fa-spin"></i>
          </template>
          <template #title_right>
            <Link :href="route('order.delivery.create')" tag="button" class="btn btn-dark">
              Create
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
              <Dropdown
                stay
                full
                header="Filter"
                id="filterdeliveryDropdown"
              >
                <template #btn>
                  <i class="fa fa-filter"></i>
                </template>
                
                <Input v-model="filter.order_ref" field="order_ref" group-class="px-2 py-1" label="Order Referance" :form="filter" autocomplete="order_deliveries.order_ref"/>
                <Input v-model="filter.driver" field="driver" group-class="px-2 py-1" label="Driver" :form="filter" autocomplete="order_deliveries.driver"/>
                <Input v-model="filter.destination" field="destination" group-class="px-2 py-1" label="Destination" :form="filter" autocomplete="order_deliveries.destination"/>
                <Input v-model="filter.from" type="date" field="from" group-class="px-2 py-1" label="From" :form="filter"/>
                <Input v-model="filter.to" type="date" field="to" group-class="px-2 py-1" label="To" :form="filter"/>
            
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
                <th>Product</th>
                <th>Quantity</th>
                <th>Driver & Destination</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(delivery, index) in deliveries.data">
                <td>
                  <div class="d-flex justify-content-between"><span class="text-muted">DREF:</span> <strong class="ml-2">{{ delivery.dref }}</strong></div>
                  <div class="d-flex justify-content-between"><span class="text-muted">REF:</span> <strong class="ml-2">{{ delivery.order_ref }}</strong></div>
                  <div class="d-flex justify-content-between"><span class="text-muted">Date:</span> <strong class="ml-2">{{ delivery.date }}</strong></div>
                </td>
                <td>
                  <div><i class="fa fa-user"></i> <Link :href="route('order.delivery.index', {customer_id: delivery.customer.id})"> {{ delivery.customer.name }} </Link></div>
                  <div><i class="fa fa-map-marker"></i> {{ delivery.customer.address }}</div>
                  <div><i class="fa fa-phone"></i> {{ delivery.customer.phone }}</div>
                </td>
                <td>{{ delivery.product }}</td>
                <td>{{ delivery.quantity }}</td>
                <td>
                  <div><i class="fa fa-user"></i> {{ delivery.driver }}</div>
                  <div><i class="fa fa-map-marker"></i> {{ delivery.destination }}</div>
                </td>
                <td class="text-right">
                  <Dropdown
                    stay
                    :header="delivery.name"
                    :id="'deliveryindex' + index"
                  >
                    <Button
                      btnDropdown
                      type="button"
                      @click="deletedelivery(delivery.delete_url)"
                    >
                      <i class="fa fa-trash"></i> Delete
                    </Button>
                    <Button
                      btnDropdown
                      type="button"
                      @click="showModal(delivery)"
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
            <Pagination @traped="loadingTable = true" :items="deliveries" />
          </div>
          
          <div class="bg-gray-1 p-2 d-flex justify-content-between align-items-center">
            <span>Summary</span>
            <button @click="getSummary" class="btn btn-dark btn-sm"><i class="fa fa-refresh" :class="{'fa-spin': summary.loading}"></i></button>
          </div>
          <div class="px-2 py-2">
            <div class="row">
              <div class="col-6">
                <span>Over all</span>
                <ul class="p-0">
                  <li v-if="summary.data" v-for="product in summary.data" class="d-flex justify-content-between">
                    <span class="text-muted">{{ product.name }}</span> 
                    {{ product.deliveried ?? 0 }} 
                  </li>
                </ul>
              </div>
              <div class="col-6">
                Current Page
                <ul class="p-0">
                  <li v-for="(value, key, index) in onPageSummary" class="d-flex justify-content-between">
                    <span class="text-muted">{{ key }}</span> 
                    {{ value }} 
                  </li>
                </ul>
              </div>
            </div>
          </div>
          
        </Card>
      </div>
    </div>
  </Content>
  <DeleteConfirm :deleteUrl="deleteUrl" item="delivery" />
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
  useValidateForm,
} from "@/Components";
import toast from "@/Store/toast.js";
import _ from "lodash";
import { Inertia } from "@inertiajs/inertia";
import { reactive, ref } from "vue";
import { isRequired } from "intus/rules";

export default {
  name: "Delivery",
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
    deliveries: Object,
    params: Object,
  },
  data() {
    return {
      filter: {
        errors: {},
        search: this.params.search ?? '',
        order_ref: this.params.order_ref ?? '',
        driver: this.params.driver ?? '',
        destination: this.params.destination ?? '',
        from: this.params.from ?? '',
        to: this.params.to ?? '',
        per_page: this.params.per_page ?? 5,
      },
      editUrl: null,
      deleteUrl: null,
      loadingTable: false,
      modal: {confirm: null},
      summary: reactive({
        data: null,
        loading: false
      }),
    };
  },
  watch: {
    filter: {
      handler: _.debounce(function (state, old) {
        let query = {};
        if (state.search) query.search = state.search;
        if (state.order_ref) query.order_ref = state.order_ref;
        if (state.driver) query.driver = state.driver;
        if (state.destination) query.destination = state.destination;
        if (state.from) query.from = state.from;
        if (state.to) query.to = state.to;
        if (state.per_page) query.per_page = state.per_page;
        this.getdeliveries(query);
      }, 1000),
      deep: true,
    },
  },
  mounted() {
    let confirm = document.querySelector("#confirmModel");
    this.modal.confirm = new bootstrap.Modal(confirm);
    this.getSummary();
  },
  computed: {
    onPageSummary() {
      if(!this.summary.data) this.getSummary();
      let summary = {}
      for(let product in this.summary.data){
        let loop_product = this.summary.data[product]
        for(let delivery in this.deliveries.data) {
          if(this.deliveries.data[delivery].product_id == loop_product.id){
            if(summary.hasOwnProperty(loop_product.name)){
              summary[loop_product.name] += this.deliveries.data[delivery].quantity
            }else{
              summary[loop_product.name] = this.deliveries.data[delivery].quantity
            }
          }
        }
      }
      return summary
    }
  },
  methods: {
    getdeliveries(filter = {}) {
      this.loadingTable = true;
      Inertia.get(this.route("order.delivery.index"), filter, {
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
      this.summary.loading = true
      await axios.get(this.route('order.delivery.api.summary'))
      .then(response => {
        this.summary.data = response.data
        this.summary.loading = false
      })
      .catch(err => {
        this.summary.loading = false
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
    deletedelivery(url) {
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
