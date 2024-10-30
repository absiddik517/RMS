<template>
  hello payments
  <pre>
    {{ user }}
  </pre>
</template>

<script>
import { Card, Pagination, Button, Modal, Input, useValidateForm } from '@/Components';
import _ from "lodash";
import { reactive } from 'vue'
import { sum } from '@/Composable/functions'
import { Inertia } from "@inertiajs/inertia";
export default {
  name: 'Payment', 
  props: {
    payments: Object,
  },
  mounted(){
    this.getPayments()
  }, 
  methods: {
    async getPayments(query={}) {
      this.fetching = true;
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
  }
}
</script>