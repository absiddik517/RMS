<template>
  <Content>
    <div class="row">
      <div class="col-12">
        <Card varient="gray">
          <template #title>
            Add Cost
          </template>
          <template #title_right>
            <Link :href="route('expense.index')">Back to expenses</Link>
          </template>
          <div class="row">
            <form @submit.prevent="submit">
              <div class="form-group">
                <label for="cost_type">Cost Type</label>
                <select v-model="form.cost_type" @change="handelChange" id="cost_type" class="form-control">
                  <option v-for="type in cost_types" :value="type">{{ type }}</option>
                </select>
              </div>
              <div v-show="!loading.form">
                <Input v-for="(field, key) in fields" v-model="form[key]" :type="field.type" :field="key" :label="field.label" :form="form" :options="field.options" :autocomplete="field.autocomplete ?? false" :optional="field.optional"/>
              </div>
              
              <div v-show="loading.form">
                <Skeleton :items="2"/>
              </div>
              
              <div class="form-group text-right" v-show="form.cost_type">
                <button class="btn btn-dark" type="submit" :disabled="!form.isDirty">
                  <i v-show="form.processing" class="fa fa-spinner fa-spin" style="width: 18px;"></i>
                  <i v-show="!form.processing" class="fa fa-save" style="width: 18px;"></i>
                  Save
                </button>
              </div>
            </form>
          </div>
        </Card>
        <pre>{{ form }}</pre>
        <pre>{{ fields }}</pre>
        
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
  Spinner,
  Button,
  Content,
  useValidateForm,
} from "@/Components";
import Skeleton from '@/Components/Skeletons/Skeleton.vue';
import {useForm, usePage} from "@inertiajs/inertia-vue3";
import { reactive } from 'vue'
export default {
  layout: AdminLayout,
  props:{
    cost_types: [Array, Object],
  },
  components: {
    Content,
    Card,
    Button,
    Switch,
    Input,
    Skeleton,
  },
  data(){
    return {
      form: useForm({
        cost_type: this.cost_types[0],
        description: '',
        amount: '',
        through: '',
        bank_account_id: '',
        worker_id: '',
        staff_id: '',
      }),
      fields: {},
      loading: reactive({
        form: false
      }),
    }
  },
  mounted() {
    this.handelChange()
  },
  methods: {
    async handelChange(){
      if(this.form.cost_type == '') return;
      this.loading.form = true;
      this.form.clearErrors();
      this.resetForm();
      await axios.post(this.route('expense.form', {
        cost_type: this.form.cost_type
      }))
      .then(response => {
        this.fields = response.data;
        const fields = Object.keys(response.data);
        for(let i = 0; i < fields.length; i++){
          this.form[fields[i]] = response.data[fields[i]].default ?? '';
        }
        this.loading.form = false;
      })
      .catch(err => {
        console.log(err)
        this.loading.form = false;
      });
    },
    
    resetForm(){
      this.form.description = ''
      this.form.amount = ''
      this.form.through = ''
      this.form.bank_account_id = ''
      this.form.worker_id = ''
      this.form.staff_id = ''
    },
    
    submit() {
      this.form.clearErrors();
      this.form.post(route('expense.store'), {
        preserveScroll: true,
        onSuccess: (response) => {
          if(response.props.toast.type == 'success') this.resetForm();
        },
        onError: error => {
          console.log(error)
        }
      });
    },
  }
  
}
</script>