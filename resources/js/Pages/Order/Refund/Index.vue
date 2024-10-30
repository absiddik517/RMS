<template>
  <Content>
    <div class="row">
      <div class="col-12">
        <Card varient="gray">
          <template #title>
            Refunds <i v-show="loadingTable" class="fa fa-spinner fa-spin"></i>
          </template>
          <template #title_right>
            <Link :href="route('order.refund.create')" >Create</Link>
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
                id="filterrefundDropdown"
              >
                <template #btn>
                  <i class="fa fa-filter"></i>
                </template>

                <div class="px-2 py-1">
                  filters
                </div>
              </Dropdown>
            </div>
          </div>

          <div class="mt-2">
            <div
              class="item d-flex justify-content-between"
              v-for="(refund, index) in refunds.data"
            >
              <div class="refund flex-1 d-flex flex-column font-quicksand t-base">
                <span class="font-krona">
                  <i class="fa fa-user"></i> {{ refund.customer.name }}</span>
                <span class="">
                  <i class="fa fa-map-marker"></i> {{ refund.customer.address }}</span>
                <span class="font-space">
                  <i class="fa fa-phone"></i> {{ refund.customer.phone }}</span>
                <span class="">
                  <i class="fa fa-envelope"></i> {{ refund.customer.email }}</span>
              </div>
              <div
                class="d-flex flex-column justify-content-between align-items-end"
              >
                <Dropdown
                  stay
                  :header="refund.customer.name"
                >
                  <Button
                    btnDropdown
                    type="button"
                    
                  >
                    <i class="fa fa-trash"></i> Delete
                  </Button>
                  <Button
                    btnDropdown
                    type="button"
                    
                  >
                    <i class="fa fa-edit"></i> Edit
                  </Button>
                </Dropdown>
                <div class="counter">
                  <span> #{{ refund.id }} </span>
                </div>
              </div>
            </div>
          </div>

          <div class="p-2">
            <Pagination @traped="loadingTable = true" :items="refunds" />
          </div>
        </Card>
      </div>
    </div>
  </Content>
</template>

<script>
import { Content, AdminLayout, Card, Dropdown, Pagination, Button } from '@/Components';
import { reactive } from 'vue'
export default {
  name: 'Index',
  layout: AdminLayout,
  components: {Content, Card, Dropdown, Pagination, Button},
  props: {
    refunds: Object,
    params: Object
  },
  data(){
    return {
      filter: reactive({
        search: this.params.search,
        per_page: this.params.per_page
      }),
      loadingTable: false,
    }
  }
}
</script>