<template>
  <Card varient="success" body-class="p-0">
    <template #title>Seleries <i @click="getSeleries" class="fa fa-refresh" :class="{'fa-spin': fetching}"></i></template>
    <div class="table-responsive">
      <table class="table table-border table-hover table-striped text-nowrap">
        <thead>
          <tr>
            <th>#</th>
            <th>Date</th>
            <th>Description</th>
            <th>Amount</th>
            <th></th>
          </tr>
        </thead>
      </table>
    </div>
  </Card>
</template>
<script>
import { Card } from '@/Components'
export default {
  name: 'Seleries',
  components: {Card},
  props:{
    staff_id: [String, Number],
  },
  data(){
    return {
      seleries: {},
      fetching: false,
    }
  },
  mounted(){
    this.getSeleries();
  },
  methods: {
    async getSeleries() {
      return true;
      this.fetching = true;
      await axios.get(this.route('staff.seleries', {staff_id: this.staff_id}))
        .then(response => {
          this.fetching = false;
        })
        .catch(err => {
          this.fetching = false;
        });
    },
  }
  
  
}
</script>