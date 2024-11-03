<template>
  <Content>
    <div class="row">
      <div class="col-12">
        <Card varient="dark">
          <template #title>Staff Detail</template>
          <div>
            <div class="d-flex justify-content-between align-items-center mb-2">
              <span class="text-muted">Name</span>
              <strong>{{ staff.name }}</strong>
            </div>
            <div class="d-flex justify-content-between align-items-center mb-2">
              <span class="text-muted">Address</span>
              <strong>{{ staff.address }}</strong>
            </div>
            <div class="d-flex justify-content-between align-items-center mb-2">
              <span class="text-muted">Phone</span>
              <a :href="'tel:'+ staff.phone"><strong>{{ staff.phone }}</strong></a>
            </div>
            <div class="d-flex justify-content-between align-items-center mb-2">
              <span class="text-muted">Designation</span>
              <strong>{{ staff.designation }}</strong>
            </div>
            <div class="d-flex justify-content-between align-items-center mb-2">
              <span class="text-muted">Selery</span>
              <strong>{{ 0 }}</strong>
            </div>
            <div class="d-flex justify-content-between align-items-center mb-2">
              <span class="text-muted">Paid</span>
              <strong>{{ summary.payments }}</strong>
            </div>
            <div class="d-flex justify-content-between align-items-center mb-2">
              <span class="text-muted">Balance</span>
              <strong>{{ 0 }}</strong>
            </div>
          </div>
        </Card>
        <Seleries :staff_id="staff.id" />
        <Payment />
      </div>
    </div>
  </Content>
</template>

<script>
import {
  AdminLayout, Modal, Card, DeleteConfirm, Spinner, Dropdown, Pagination, Filterth, Button, Content, useValidateForm,
} from "@/Components";
import Seleries from '@/Pages/Staff/Components/Seleries.vue';
import Payment from '@/Pages/Staff/Components/Payment.vue';
import _ from "lodash";
import { Inertia } from "@inertiajs/inertia";
import { reactive } from "vue";

export default {
  name: "Account",
  layout: AdminLayout,
  components: {
    Seleries, Payment, Content, Card,
    Spinner, Modal, DeleteConfirm, Button, Dropdown, Pagination,
  },
  props: {
    staff: Object,
    params: Object,
  },
  data() {
    return {
      summary: reactive({
        payments: 0,
      })
    }
  }
};
</script>

