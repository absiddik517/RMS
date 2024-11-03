<template>
  <Content>
    <div class="row">
      <div class="col-12">
        <Card varient="gray" body-class="p-0 no-print" title="Sheet" :loading="loading">
          <template #title_right>
            <Button @click="printSheet"><i class="fa fa-print"></i></Button>
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
            <Select
              v-model="filter.student_id"
              label-text="Student"
              withoutLabel
              :dependOn="{class_id: filter.class_id}"
              from="sheet.get.student" 
              placeholder="Select Student"
              groupClass="col-12"
              @change="get_result"
            />
          </div>
        </Card>
        
        <div class="" v-if="data.loaded">
          <div class="p-2" ref="printableArea">
            <div class="text-center bbwp">
              <h1>{{ institute.name }}</h1>
              <h3>{{ institute.established_at }}</h3>
              <h3>{{ institute.address }}</h3>
              <h3>{{ get_exam_name ? get_exam_name : 'Dami exam name' }}</h3>
            </div>
            <div>
              <div class="row student mt-3">
                <div class="col-12">নাম: &nbsp; &nbsp; <strong>{{ data.result.student_name }}</strong></div>
                <div class="col-6">শ্রেণি: &nbsp; &nbsp; <strong>{{ get_class_name }}</strong></div>
                <div class="col-1"></div>
                <div class="col-5">রোল নং: &nbsp; &nbsp; <strong>{{ data.result.student_roll }}</strong></div>
              </div>

              <!-- result table -->
              <div class="mt-5 table-responsive">
                <table class="table table-bordered">
                  <thead style="background: #ddd;">
                    <tr class="text-center">
                      <th colspan="2">বিষয়</th>
                      <th>পূর্ণমান</th>
                      <th v-for="(short, title) in data.thead" :key="short">{{ title }}</th>
                      <th>মোট</th>
                      <th>গ্রেড</th>
                    </tr>
                  </thead>

                  <tbody>
                    <tr v-for="(subject, index) in
                    Object.values(data.result.subjects)">
                      <td style="width: 10px">{{ index + 1 }}</td>
                      <td>{{ subject.subject_name }}</td>
                      <td class="text-right">{{ subject.full_mark }}</td>
                      <td v-for="(short, title) in data.thead" :key="short" class="text-right"
                      :class="{'fail': subject.result[short] && subject.result[short].status !== 1 }">
                        {{ subject.result[short]?.mark_obtain }}
                      </td>
                      <td class="text-right">{{ subject.total_mark_obtain }}</td>
                      <td class="text-center">{{ calculateGradeOf(subject.point) }}</td>
                    </tr>
                  </tbody>
                  <tfoot>
                    <tr>
                      <th colspan="2" class="text-center">মোট</th>
                      <th class="text-right">{{ calculateFullMarks(data.result) }}</th>
                      <th colspan="4" class="text-right">{{ calculateTotalMarks(data.result) }}</th>
                      <th></th>
                    </tr>
                  </tfoot>
                </table>
              </div>
              <!-- result summary -->
              <div class="mt-5">
                <table class="table table-bordered">
                  <tbody>
                    <tr>
                      <td style="width: 33.33%">
                        গ্রেড: <strong>{{ calculateGrade(data.result) }}</strong>
                      </td>
                      <td style="width: 33.33%" class="text-center">
                        GPA: <strong>{{ calculatePoints(data.result) }}</strong>
                      </td>
                      <td style="width: 33.33%" class="text-right">
                        শতকরা: {{ (calculateTotalMarks(data.result)*100/calculateFullMarks(data.result)).toFixed(0) }}%
                      </td>
                    </tr>
                    <tr>
                      <td colspan="3">
                        <div style="min-height: 80px" contenteditable="true">
                          শ্রেণি শিক্ষকের মন্তব্য: 
                        </div>
                      </td>
                    </tr>
                    <tr>
                      <td class="text-center">
                        <div style="min-height: 100px">
                          শ্রেণি শিক্ষকের সাক্ষর 
                        </div>
                      </td>
                      <td class="text-center">
                        <div style="min-height: 100px">
                          প্রধান শিক্ষকের সাক্ষর 
                        </div>
                      </td>
                      <td class="text-center">
                        <div style="min-height: 100px">
                          অভিভাবকের সাক্ষর
                        </div>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
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
import "../../../Components/Fonts/SolaimanLipi.js";

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
    institute: Object,
    classes: Object,
    exams: Object,
    students: Object
  },
  data() {
    return {
      filter: reactive({
        class_id: null,
        exam_id: null,
      }),
      data: reactive({
        result: undefined,
        thead: undefined,
        loaded: false,
      }),
      pages: reactive({
        width: 215.9,
        height: 279.4,
        margin: 0.5,
        unit: "mm",
      }),
      loading: false,
    };
  },
  mounted() {
    console.log("m");
  },
  methods: {
    async get_students() {
      if (!this.filter.class_id) return;
      try {
        this.loading = true;
        console.log(
          route("sheet.data", {
            class_id: this.filter.class_id,
          })
        );
        const response = await axios.get(
          route("sheet.data", {
            class_id: this.filter.class_id,
          })
        );
        this.students = response.data;
      } catch (error) {
        console.log("Error on getSubjects", error);
      } finally {
        this.loading = false;
      }
    },
    async get_result() {
      console.log(this.institute);
      if (!this.filter.exam_id || !this.filter.class_id || !this.filter.student_id) return;
      try {
        this.loading = true;
        this.loaded = false;
        console.log(
          route("sheet.individual.data", {
            exam_id: this.filter.exam_id,
            class_id: this.filter.class_id,
            student_id: this.filter.student_id,
          })
        );
        const response = await axios.get(
          route("sheet.individual.data", {
            exam_id: this.filter.exam_id,
            class_id: this.filter.class_id,
            student_id: this.filter.student_id,
          })
        );
        this.data.result = response.data.result;
        this.data.thead = response.data.head;
        this.data.loaded = true
      } catch (error) {
        console.log("Error on getSubjects", error);
      } finally {
        this.loading = false;
      }
    },
    printSheet() {
      console.log("printing sheet");
      const printContents = this.$refs.printableArea.innerHTML;
      const originalContents = document.body.innerHTML;
      document.body.innerHTML = printContents;
      window.print();
      document.body.innerHTML = originalContents;
      location.reload(); // Optional: reload to reset the page after printing
    },
    calculateTotalMarks(student) {
      return Object.values(student.subjects).reduce((total, subject) => {
        return total + subject.total_mark_obtain;
      }, 0);
    },
    calculateFullMarks(student) {
      return Object.values(student.subjects).reduce((total, subject) => {
        return total + subject.full_mark;
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
      return failed_in === 0 ? (total_point / total_subjects).toFixed(2) : 0.0;
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
    calculateGradeOf(mark) {
      if (mark >= 5) return "A+";
      else if (mark >= 4) return "A";
      else if (mark >= 3.5) return "A-";
      else if (mark >= 3) return "B";
      else if (mark >= 2) return "C";
      else if (mark >= 1) return "D";
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
    size: A4;
    margin: 10mm; /* Adjust margins as needed */
  }

  /* Optional styling for the print view */
  body {
    width: 210mm;
    height: 297mm;
    -webkit-print-color-adjust: exact;
    print-color-adjust: exact;
  }

  /* Apply custom styles for print */
  .content {
    padding: 10mm;
    font-size: 12pt; /* Adjust font size for better readability on A4 */
  }

  .bbwp {
    display: block !important;
  }

  /* Optional: Hide elements that should not be printed */
  .no-print {
    display: none;
  }
}
.bbwp {
  padding-bottom: 5px;
  border-bottom: 1.5px solid #ddd;
  display: none;
}

.student .col-12,
.student .col-6,
.student .col-5 {
  border-bottom: 1px dotted #000;
  font-size: 22px;
}

.student div{
  padding:10px 8px;
}
.fail{
  background: #ddd;
  color: red;
}
</style>
