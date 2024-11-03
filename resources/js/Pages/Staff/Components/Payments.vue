<template>
  <Card varient="danger" body-class="p-0">
    <template #title>Payments <i @click="getPayments" class="fa fa-refresh" :class="{'fa-spin': fetching}"></i></template>
    <template #title_right>
      <Button class="mr-2" type="button" varient="light" @click="showModal(null)">
        Create
      </Button>
    </template>
    <div class="row p-2">
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
        <span class="mr-1">Show</span>
        <select
          v-model="filter.per_page"
          class="select_per_page"
          id="per_page"
        >
          <option disabled value="null">🔻</option>
          <option v-for="index in 100" :value="index * 5">
            {{ index * 5 }}
          </option>
          <option value="all">All</option>
        </select>
      </div>
    </div>
    <div class="table-responsive">
      <table class="table table-border table-hover table-striped text-nowrap">
        <thead>
          <tr>
            <th>#</th>
            <th>Date</th>
            <th v-show="!staff_id">Name</th>
            <th>Description</th>
            <th>Amount</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(payment, index) in payments?.data">
            <td>{{ index + 1 }}</td>
            <td>{{ payment.date }}</td>
            <td v-show="!staff_id">{{ payment.name }}</td>
            <td>{{ payment.description }}</td>
            <td class="text-right">{{ payment.amount }}</td>
            <td></td>
          </tr>
        </tbody>
        <tfoot>
          <tr>
            <th colspan="3">Total</th>
            <th class="text-right">{{ total_payment }}</th>
            <th></th>
          </tr>
        </tfoot>
      </table>
    </div>
    
    <pre>{{ user }}</pre>
    
    <!--
    <div v-if="payments?.data?.length" class="p-2">
      <Pagination @traped="fetching = true" :items="payments" />
    </div>
    -->
  </Card>
  
  <Modal id="staff_payment_modal" :title="modal.title" varient="light">
    <template #body>
      <form @submit.prevent="submit" novalidate="novalidate">
        <Input v-model="form.staff_id" field="staff_id" label="Staff" :form="form" type="text"/>
        <Input v-model="form.description" optional field="description" label="Description" :form="form"/>
        <Input v-model="form.amount" field="amount" label="Amount" :form="form" type="number"/>
      </form>
    </template>

    <template #action>
      <Button :isLoading="form.processing" :hideLabel="true" @click="submit">
        <i class="fa fa-save"></i>
      </Button>
    </template>
  </Modal>
</template>
<script>
import { Card, Pagination, Button, Modal, Input, useValidateForm } from '@/Components';
import _ from "lodash";
import { reactive } from 'vue'
import { sum } from '@/Composable/functions'
import { Inertia } from "@inertiajs/inertia";
import { isRequired } from "intus/rules";

export default {
  name: 'Payments',
  components: { Card, Pagination, Button, Modal, Input },
  props:{
    payments: Object,
    user: [Array, Object],
    staff_id: {
      type: [String, Number],
      default: false
    },
    modelValue: [String, Number],
  },
  data(){
    return {
      total_payment: 0,
      fetching: false,
      filter: {
        search: '',
        per_page: 5,
      },
      form: useValidateForm({
        staff_id: '',
        description: '',
        amount: '',
      }),
      modal: {
        form: null,
        isEditing: false,
        title: '',
        editUrl: null,
      },
    }
  },
  mounted(){
    this.getPayments();
    let form = document.querySelector("#staff_payment_modal");
    //let confirm = document.querySelector("#confirmModel");
    this.modal.form = new bootstrap.Modal(form);
    //this.modal.confirm = new bootstrap.Modal(confirm);
  },
  methods: {
    async getPayments(query={}) {
      this.fetching = true;
      query.staff_id = this.staff_id;
      
      await Inertia.get(this.route("staff.payment"), query, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
        onSuccess: () => {
          this.fetching = false;
        },
        onError: error => {
          this.fetching = false;
          let message = '';
          for(let key in error){
            message += error[key] + ' ';
          }
          toast.add({
            type: 'error',
            message
          })
        }
      });
    },
    
    async submit() {
      var url = this.isEditing ? this.editUrl : route("api.staff.payment.store");
      await axios.post(url, this.form)
        .then(response => {
          console.log(response.json());
        })
        .catch(error => {
          console.log(error)
        })
    },
    showModal(data = null) {
      this.modal.isEditing = data !== null;
      this.modal.title = data == null ? "Create Staff Payment" : "Update Staff Payment";
      //this.form.clearErrors();
      if (this.modal.isEditing) {
        this.modal.editUrl = data.edit_url;
        this.form.staff_id = data.staff_id;
        this.form.description = data.description;
        this.form.amount = data.amount;
      } else {
        //this.form.reset();
      }
      this.modal.form.show();
    },
  },
  
  watch: {
    filter: {
      handler: _.debounce(function (state, old) {
        let query = {};
        if (state.search) query.search = state.search;
        if (state.per_page) query.per_page = state.per_page;
        
        this.getPayments(query);
      }, 1000),
      deep: true,
    },
  },
  
  
}
</script>