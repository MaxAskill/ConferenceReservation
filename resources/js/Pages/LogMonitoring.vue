<template>
  <Head title="Log Monitoring" />

  <DashboardLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Reservation Monitoring
      </h2>
    </template>

    <div class="bg-white p-6 m-10 rounded-md">
      <div class="flex space-x-reverse">
        <div class="w-96 mb-4">
          <el-input v-model="searchInput" placeholder="Search..." />
        </div>

        <div class="demo-pagination-block ml-auto">
          <div class="flex items-center justify-center">
            <el-pagination
              v-model:current-page="pagination.currentPage"
              v-model:page-size="pagination.perPage"
              :page-sizes="[10, 20, 50, 100]"
              :small="small"
              :disabled="disabled"
              :background="background"
              layout="sizes, prev, pager, next"
              :total="pagination.total"
            />
          </div>
        </div>
      </div>
      <el-table
        :data="queriedTableData"
        style="width: 100%"
        @filter-change="onFilterChange"
      >
        <el-table-column width="50">
          <template #default="scope">
            <span>{{
              (pagination.currentPage - 1) * pagination.perPage + scope.$index + 1
            }}</span>
          </template>
        </el-table-column>
        <!-- <el-table-column type="index" :index="indexMethod" /> -->
        <el-table-column prop="date" label="Date" width="150" />
        <el-table-column prop="time" label="Time" width="150" />
        <el-table-column prop="name" label="Name" width="150" />
        <el-table-column prop="action_type" label="Action Type" width="150" />
        <el-table-column prop="table_affected" label="Table Affected" width="150" />
        <el-table-column prop="old_data" label="Old Data" min-width="200" />

        <el-table-column prop="new_data" label="New Data" min-width="200" />
        <!-- <el-table-column
          label="Status"
          width="120"
          :filters="[
            { text: 'Reserved', value: 'Reserved' },
            { text: 'Completed', value: 'Completed' },
            { text: 'Canceled', value: 'Canceled' },
          ]"
          filter-placement="bottom-end"
        > -->
        <!-- <template #default="props">
            <p
              v-if="props.row.status === 'Completed'"
              class="text-green-600 text font-bold bg-green-200 text-center rounded-full"
            >
              {{ props.row.status }}
            </p>
            <p
              v-else-if="props.row.status === 'Reserved'"
              class="text-orange-600 font-bold bg-orange-200 text-center rounded-full"
            >
              {{ props.row.status }}
            </p>
            <p v-else class="text-red-600 font-bold bg-red-200 text-center rounded-full">
              {{ props.row.status }}
            </p>
          </template>
        </el-table-column> -->
      </el-table>
    </div>
  </DashboardLayout>
  <EditReservation
    v-if="isEdit"
    :reservationInformation="reservationInformations"
    @updated="fetchReservations"
    @close="isEdit = false"
  >
  </EditReservation>
  <ReservationDetails
    v-if="isView"
    :reservationInformation="reservationInformation"
    @close="isView = false"
  >
  </ReservationDetails>
  <!-- <ConfirmCancellation
    v-if="isCancel"
    :reservationInformation="reservationInformation"
    @close="isCancel = false"
  >
  </ConfirmCancellation> -->
</template>

<script>
import DashboardLayout from "@/Layouts/DashboardLayout.vue";
import { Head } from "@inertiajs/vue3";
import EditReservation from "./EditReservationModal.vue";
import ReservationDetails from "./ReservationDetailsModal.vue";
// import ConfirmCancellation from "./ConfirmCancellationModal.vue";

export default {
  components: {
    DashboardLayout,
    EditReservation,
    ReservationDetails,
    // ConfirmCancellation,
  },
  data() {
    return {
      isEdit: false,
      isView: false,
      // isCancel: false,
      reservationInformation: [],
      reservationInformations: {
        conferenceRoom: "",
        date: "",
        department: "",
        email: "",
        equipment: [],
        id: "",
        name: "",
        status: "",
        startTime: "",
        endTime: "",
        purpose: "",
        arrangement: "",
      },
      tableData: [],
      tempTableData: [],
      searchInput: "",
      filteredByStatus: "",
      propsToSearch: [
        "action_type",
        "date",
        "name",
        "new_data",
        "old_data",
        "table_affected",
        "time",
      ],
      pagination: {
        perPage: 10,
        currentPage: 1,
        perPageOptions: [10, 20, 50, 100],
        total: 0,
      },
      filteredTable: [],
    };
  },
  computed: {
    pagedData() {
      return this.tableData.slice(this.from, this.to);
    },
    /***
     * Searches through table data and returns a paginated array.
     * Note that this should not be used for table with a lot of data as it might be slow!
     * Do the search and the pagination on the server and display the data retrieved from server instead.
     * @returns {computed.pagedData}
     */
    queriedTableData() {
      if (!this.searchInput) {
        this.pagination.total = this.tableData.length;
        return this.pagedData;
      }
      let result = this.tableData.filter((row) => {
        for (let key of this.propsToSearch) {
          if (
            !this.searchInput ||
            row[key].toString().toLowerCase().includes(this.searchInput.toLowerCase())
          ) {
            return true; // Return true if the condition is met
          }
        }
        return false; // Return false if the condition is not met for any key
      });

      this.pagination.total = result.length;
      return result.slice(this.from, this.to);
    },
    to() {
      let highBound = this.from + this.pagination.perPage;
      if (this.total < highBound) {
        highBound = this.total;
      }
      return highBound;
    },
    from() {
      return this.pagination.perPage * (this.pagination.currentPage - 1);
    },
    total() {
      this.pagination.total = this.tableData.length;
      return this.tableData.length;
    },
  },
  mounted() {
    this.fetchReservations();
  },
  watch: {},
  methods: {
    onFilterChange(filters) {
      console.log("FILTERED STATUS: ", filters["el-table_1_column_8"][0]);
      this.filteredByStatus = filters["el-table_1_column_8"][0];
    },
    fetchReservations() {
      let tempTableData = [];
      axios
        .get("getLogs")
        .then((response) => {
          response.data.forEach((data) => {
            let obj = {
              date: data.date,
              time: data.time,
              name: data.name,
              old_data: data.old_data == null ? "" : data.old_data,
              new_data: data.new_data,
              action_type: data.action_type,
              table_affected: data.table_affected,
            };
            tempTableData.push(obj);
          });
          this.tableData = tempTableData;
          this.tempTableData = response.data;
        })
        .catch((error) => {
          console.error(error);
        });
    },
    changeDateFormat(date) {
      const inputDate = new Date(date);
      const year = inputDate.getFullYear();
      const month = String(inputDate.getMonth() + 1).padStart(2, "0"); // Months are zero-based, so we add 1 and pad with leading zeros if needed
      const day = String(inputDate.getDate()).padStart(2, "0"); // Pad with leading zeros if needed
      return `${year}-${month}-${day}`;
    },
  },
};
</script>
