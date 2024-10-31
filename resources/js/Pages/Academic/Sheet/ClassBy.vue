<template>
  <Content>
    <div class="row">
      <div class="col-12">
        <Card varient="gray" body-class="p-0" title="Sheet" :loading="loading">
          <template #title_right>
            <Button @click="printSheet"><i class="fa fa-print"></i></Button>
            &nbsp;<Button @click="genPDF">PDF</Button>
          </template>
          <div class="row p-2 gy-2">
            <Select
              v-model="filter.exam_id"
              label-text="Exam"
              :options="exams"
              withoutLabel
              placeholder="Exam"
              groupClass="col-6"
              @change="get_result"
            />
            <Select
              v-model="filter.class_id"
              label-text="Class"
              :options="classes"
              withoutLabel
              placeholder="Class"
              groupClass="col-6"
              @change="get_result"
            />
          </div>
          <div class="">
            <div class="p-2" ref="printableArea" v-if="data.thead">
              <div class="text-center bbwp">
                <h1>স্কুলের নাম</h1>
                <h3>{{ get_exam_name }}</h3>
                <h3>{{ get_class_name }}</h3>
              </div>
              <div>
                <table class="table table-bordered">
                  <thead>
                    <tr>
                      <th rowspan="2">Roll</th>
                      <th rowspan="2">Name</th>

                      <th
                        v-for="(item, index) in data.thead"
                        :colspan="item.criteria.length + 1"
                        :key="'item' + index"
                      >
                        {{ item.subject_name }}
                      </th>

                      <th rowspan="2">Total</th>
                      <th rowspan="2">Grade</th>
                      <th rowspan="2">Point</th>
                    </tr>

                    <tr>
                      <template v-for="(item, key) in data.thead" :key="key">
                        <th
                          v-for="(title, index) in item.criteria"
                          :key="'cri' + index"
                        >
                          {{ title }}
                        </th>
                        <th>Total</th>
                      </template>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="(item, index) in data.result">
                      <td>{{ item.student_roll }}</td>
                      <td>{{ item.student_name }}</td>
                      <template
                        v-for="(subject, key) in item.subjects"
                        :key="key"
                      >
                        <td
                          v-for="(mark, cri) in subject.result"
                          :key="cri + '-' + index"
                          :class="{ 'text-danger': mark.status === 0 }"
                        >
                          {{ mark.mark_obtain }}
                        </td>
                        <td :class="{ 'text-danger': subject.point === 0 }">
                          <strong>{{ subject.total_mark_obtain }}</strong>
                        </td>
                      </template>
                      <td>{{ calculateTotalMarks(item) }}</td>
                      <td>{{ calculateGrade(item) }}</td>
                      <td>{{ calculatePoints(item).toFixed(2) }}</td>
                    </tr>
                  </tbody>
                </table>
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
  Spinner,
  Input,
  Select,
  Content,
  Card,
  Button,
} from "@/Components";
import toast from "@/Store/toast.js";
import { Inertia } from "@inertiajs/inertia";
import { reactive, ref } from "vue";
import jsPDF from "jspdf";
import SolaimanLipi from "../../../Components/Fonts/SolaimanLipi.js";

export default {
  name: "ClassBy",
  layout: AdminLayout,
  components: {
    Spinner,
    Input,
    Select,
    Content,
    Card,
    Button,
  },
  props: {
    classes: Object,
    exams: Object,
  },
  data() {
    return {
      filter: reactive({
        class_id: null,
        exam_id: null,
      }),
      data: reactive({
        thead: undefined,
        result: undefined,
      }),
      loading: false,
    };
  },
  mounted() {
    console.log("m");
  },
  methods: {
    async get_result() {
      if (!this.filter.exam_id || !this.filter.class_id) return;
      try {
        this.loading = true;
        console.log(
          route("sheet.data", {
            exam_id: this.filter.exam_id,
            class_id: this.filter.class_id,
          })
        );
        const response = await axios.get(
          route("sheet.data", {
            exam_id: this.filter.exam_id,
            class_id: this.filter.class_id,
          })
        );
        this.data.thead = response.data.head;
        this.data.result = response.data.result;
      } catch (error) {
        console.log("Error on getSubjects", error);
      } finally {
        this.loading = false;
      }
    },
    printSheet() {
      const printContents = this.$refs.printableArea.innerHTML;
      const originalContents = document.body.innerHTML;
      document.body.innerHTML = printContents;
      window.print();
      document.body.innerHTML = originalContents;
      //location.reload(); // Optional: reload to reset the page after printing
    },
    genPDF() {
      const doc = new jsPDF({
        orientation: "landscape",
        unit: "mm",
        format: "legal"
      });

      // Reference the HTML content
      const pdfContent = this.$refs.printableArea;

      // Use the html method to convert the element to PDF
      doc.html(pdfContent, {
        callback: (doc) => {
          // Save the PDF after content is added
          doc.save("sample.pdf");
        },
        x: 10,
        y: 10 // Adjust the x and y coordinates as needed
      });
    },
    calculateTotalMarks(student) {
      return Object.values(student.subjects).reduce((total, subject) => {
        return total + subject.total_mark_obtain;
      }, 0);
    },
    calculatePoints(student) {
      let failed_in = Object.values(student.subjects).filter(
        (subject) => subject.point === 0
      ).length;
      let total_point = Object.values(student.subjects).reduce(
        (total, subject) => {
          return total + subject.point;
        },
        0
      );
      let total_subjects = Object.keys(student.subjects).length;
      return failed_in === 0 ? total_point / total_subjects : 0.0;
    },
    calculateGrade(student) {
      const point = this.calculatePoints(student);
      if (point >= 5) return "A+";
      else if (point >= 4) return "A";
      else if (point >= 3.5) return "A-";
      else if (point >= 3) return "B";
      else if (point >= 2) return "C";
      else if (point >= 1) return "D";
      else return "F"; // for points below 1
    },
  },
  computed: {
    get_exam_name() {
      const item = this.exams.find(
        (element) => element.value === this.filter.exam_id
      );
      return item ? item.label : null;
    },
    get_class_name() {
      const item = this.classes.find(
        (element) => element.value === this.filter.class_id
      );
      return item ? item.label : null;
    },
  },
};
</script>

<style scoped>
@media print {
  @page {
    size: 24in 8.5in; /* Legal size */
    margin: 1in; /* Optional: Adjust margins */
  }
  
  body {
    transform: scale(0.9); /* Optional: Adjust content scaling */
    transform-origin: top left; /* Optional: Adjust origin for scaling */
  }

  /* Optional: Hide elements that should not be printed */
  .no-print {
    display: none;
  }
}
.bbwp {
  padding-bottom: 5px;
  border-bottom: 1.5px solid #ddd;
}

table thead tr th {
  text-align: center; /* Horizontal centering */
  vertical-align: middle; /* Vertical centering */
}
</style>
