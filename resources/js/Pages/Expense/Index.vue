<template>
  <Content>
    <div class="row">
      <div class="col-12">
        <Card varient="gray">
          <template #title>Expenses <i v-show="loadingTable" class="fa fa-spinner fa-spin"></i> </template>
          <template #title_right>
            <Link :href="route('expense.create')">Create</Link>
          </template> 
          
          
          <div class="row mb-3">
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
              <Dropdown stay w-full header="Filter" id="filter_dropdown">
                <div class="px-3">
                  <Switch v-model="filter.modules" v-for="module in available_modules" :value="module" :label="module" />
                </div>
                <div class="px-3 py-4">
                  <div class="form-group">
                    <label for="date_range">Date Range</label>
                    <div class="d-flex justify-content-between align-items-center">
                      <div class="flex-1">
                        <input type="date" placeholder="From" name="" id="" class="form-control">
                      </div>
                      <div style="width: 10px;"></div>
                      <div class="flex-1">
                        <input type="date" placeholder="To" name="" id="" class="form-control">
                      </div>
                    </div>
                  </div>
                </div>
              </Dropdown>
            </div>
          </div>
          
          <div>
            <div v-for="expense in expenses.data" class="user d-flex justify-content-between align-items-center mb-3">
              <div class="d-flex flex-column">
                <strong class="text-muted"><i class="fa-solid fa-eye" style="width: 20px;"></i> {{ expense.record }}</strong>
                <strong class="font-raleway"> <i class="fa fa-pencil" style="width: 20px;"></i> {{ expense.description }}</strong>
                <strong class="font-raleway" v-show="expense.through != 'null'"> <i class="fa fa-user" style="width: 20px;"></i> {{ expense.through }}</strong>
                <span class="font-quicksand"> <i class="fa fa-clock" style="width: 20px;"></i> {{ expense.date }}</span>
              </div>
              <div class="text-right d-flex align-items-center flex-column">
                <Dropdown
                    stay
                    :header="expense.record + ' ('+ expense.amount + ')'"
                    :id="expense.record+'_'+expense.id"
                  >
                    <Button
                      btnDropdown
                      type="button"
                    >
                      <i class="fa fa-trash"></i> Delete
                    </Button>
                  </Dropdown>
                <span>{{ expense.amount }}</span>
              </div>
            </div>
          </div>
          
          <div v-if="expenses.data.length" class="p-2">
            <Pagination @traped="loadingTable = true" :items="expenses" />
          </div>
          
          <div class="bg-gray-1 p-2 d-flex justify-content-between align-items-center">
            <span>Summary</span>
          </div>
          <div class="px-2 py-2">
            <div class="row">
              <div class="col-6">
                <span>Over all</span>
                <ul class="p-0">
                  <li v-for="(item, key) in summary" class="d-flex justify-content-between">
                    <span class="text-muted">{{ key }}</span> 
                    {{ item ?? 0 }} 
                  </li>
                </ul>
              </div>
              <div class="col-6">
                Current Page
                <ul class="p-0">
                  
                  <li v-for="(item, key) in total_in_this_page" class="d-flex justify-content-between">
                    <span class="text-muted">{{ key }}</span> 
                    {{ item ?? 0 }} 
                  </li>
                </ul>
              </div>
            </div>
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
  Input,
  Switch,
  Card,
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
import { reactive } from "vue";
import { round, sum } from "@/Composable/functions";

export default {
  name: "UserIndex",
  layout: AdminLayout,
  components: {
    Spinner,
    Modal,
    Content,
    DeleteConfirm,
    Card,
    Input,
    Switch,
    Button,
    Dropdown,
    Filterth,
    Pagination,
  },
  props: {
    expenses: Object,
    available_modules: Array,
    params: Object,
    summary: [Object, Array],
    cost_types: [Object, Array],
  },
  data(){
    return {
      loadingTable: false,
      filter: reactive({
        search: this.params.search,
        per_page: this.params.per_page,
        modules: this.params.modules,
      })
    }
  },
  
  watch: {
    filter: {
      handler: _.debounce(function (state, old) {
        let query = {};
        if (state.search) query.search = state.search;
        if (state.per_page) query.per_page = state.per_page;
        query.modules = state.modules;

        this.getUsers(query);
      }, 1000),
      deep: true,
    },
  },
  computed: {
    total_in_this_page() {
      let products = {}
      for(let item in this.cost_types){
        products[this.cost_types[item]] = sum(this.expenses.data, 'amount', ['record', this.cost_types[item]]);
      }
      let total = 0;
      for(let index in products){
        total += products[index];
      }
      products['Total'] = total;
      return products
    }
  },
  methods: {
    getUsers(filter = {}) {
      this.loadingTable = true;
      Inertia.get(this.route("expense.index"), filter, {
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
    
  }
};
</script>

<style scoped>

  .user{
    position: relative;
    border: 1px solid #eee;
    border-radius: 3px;
    padding: 8px 9px;
    background: #fafafa;
    color: #000;
    box-shadow: 2px 1px 6px 2px rgba(0,0,0,0.1);
  }
  
  .photo-container{
    --photo-width: 60px;
    overflow: hidden;
    border-radius: 50%;
    margin-right: 10px;
    width: var(--photo-width);
    height: var(--photo-width);
  }
  .photo-container img {
    width:100%;
    height: 100%;
  }
</style>
