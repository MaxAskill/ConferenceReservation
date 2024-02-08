<template>
  <Head title="Reservation Monitoring" />

  <DashboardLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Reservation Monitoring
      </h2>
    </template>

    <div class="p-10">
      <div class="bg-white p-6 rounded-xl drop-shadow-lg rounded-md">
        <div class="flex space-x-reverse">
          <div class="w-96 mb-4">
            <el-input
              v-model="searchInput"
              @input="onInputSearch"
              placeholder="Search..."
            />
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
          <el-table-column prop="date" label="Date" min-width="150" />
          <el-table-column prop="time" label="Time" min-width="170" />
          <el-table-column
            prop="conferenceRoom"
            label="Conference Room"
            min-width="200"
            :filters="[
              { text: 'AT HOME', value: 'AT HOME Conference Room' },
              { text: 'BARBIZON', value: 'BARBIZON Conference Room' },
              { text: 'MONALISA', value: 'MONALISA Conference Room' },
              { text: 'SASSA', value: 'SASSA Conference Room' },
              { text: 'SWIMLAB', value: 'SWIMLAB Conference Room' },
            ]"
            filter-placement="bottom-end"
          />
          <el-table-column prop="name" label="Name" min-width="180" />
          <el-table-column prop="email" label="Email" min-width="260" />

          <el-table-column prop="department" label="Department" min-width="140" />
          <el-table-column
            label="Status"
            width="120"
            :filters="[
              { text: 'Reserved', value: 'Reserved' },
              { text: 'Completed', value: 'Completed' },
              { text: 'Canceled', value: 'Canceled' },
            ]"
            filter-placement="bottom-end"
          >
            <template #default="props">
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
              <p
                v-else
                class="text-red-600 font-bold bg-red-200 text-center rounded-full"
              >
                {{ props.row.status }}
              </p>
            </template>
          </el-table-column>
          <el-table-column fixed="right" label="Operations" width="120">
            <template #default="scope">
              <el-button
                type="primary"
                size="small"
                circle
                @click="viewReservation(scope.row)"
              >
                <el-icon><List /></el-icon>
              </el-button>
              <el-button
                v-if="
                  !(scope.row.status === 'Completed' || scope.row.status === 'Canceled')
                "
                type="warning"
                size="small"
                circle
                @click="editReservation(scope.row)"
              >
                <el-icon><Edit /> </el-icon
              ></el-button>
              <el-popconfirm
                width="210"
                confirm-button-text="Confirm"
                cancel-button-text="Cancel"
                :icon="WarningFilled"
                icon-color="#c45656"
                title="Are you sure you want to cancel this reservation?"
                @confirm="cancelReservation(scope.row, true)"
                @cancel="cancelReservation(scope.row, false)"
                ref="popconfirmCancel"
              >
                <template #reference>
                  <el-button
                    v-if="
                      !(
                        scope.row.status === 'Completed' ||
                        scope.row.status === 'Canceled'
                      )
                    "
                    type="danger"
                    size="small"
                    circle
                    @click="handleCancel(scope.row)"
                  >
                    <el-icon><CircleClose /></el-icon>
                  </el-button>
                </template>
              </el-popconfirm>
            </template>
          </el-table-column>
        </el-table>
      </div>
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
import { ElMessage } from "element-plus";
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
      propsToSearch: [
        // "id",
        "conferenceRoom",
        "date",
        "time",
        "name",
        "email",
        "department",
        "status",
      ],
      pagination: {
        perPage: 10,
        currentPage: 1,
        perPageOptions: [10, 20, 50, 100],
        total: 0,
      },
      todayFilter: [],
      filterByStatus: [],
      filterByConfRoom: [],
      temp_tableData: [],
      filter_tableData: [],
      filter_tableData_4: [],
      search_tableData: [],
      display_tableData: [],
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
      // if (!this.searchInput) {
      //   this.pagination.total = this.tableData.length;
      //   return this.pagedData;
      // }
      // let result = this.tableData.filter((row) => {
      //   for (let key of this.propsToSearch) {
      //     if (
      //       !this.searchInput ||
      //       row[key].toString().toLowerCase().includes(this.searchInput.toLowerCase())
      //     ) {
      //       return true; // Return true if the condition is met
      //     }
      //   }
      //   return false; // Return false if the condition is not met for any key
      // });

      // this.pagination.total = result.length;
      // return result.slice(this.from, this.to);

      if (
        !this.searchInput &&
        this.filterByStatus.length == 0 &&
        this.filterByConfRoom.length == 0
      ) {
        this.pagination.total = this.tableData.length;
        return this.pagedData;
      }

      this.pagination.total = this.display_tableData.length;
      return this.display_tableData.slice(this.from, this.to);
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

    var today = new Date();
    var formattedDate = today.toLocaleDateString("en-US", {
      year: "numeric",
      month: "long",
      day: "numeric",
      timeZone: "Asia/Manila",
    });
    this.todayFilter = [{ text: "Today", value: formattedDate }];
    // console.log(this.todayFilter[0].value);
  },
  watch: {},
  methods: {
    onFilterChange(filters) {
      if (filters["el-table_1_column_8"]) {
        let tempFBS = true;
        if (filters["el-table_1_column_8"].length != 0)
          this.filterByStatus = filters["el-table_1_column_8"];
        else {
          this.filterByStatus = ["Completed", "Reserved", "Canceled"];
          tempFBS = false;
        }
        this.filter_tableData = [];
        if (!this.searchInput && this.filterByConfRoom.length == 0)
          this.filter_tableData = this.tableData.filter((row) =>
            this.filterByStatus.includes(row.status)
          );
        else if (this.searchInput && this.filterByConfRoom.length == 0)
          this.filter_tableData = this.search_tableData.filter((row) =>
            this.filterByStatus.includes(row.status)
          );
        else if (!this.searchInput && this.filterByConfRoom.length != 0)
          if (tempFBS)
            this.filter_tableData = this.filter_tableData_4.filter((row) =>
              this.filterByStatus.includes(row.status)
            );
          else
            this.filter_tableData = this.tableData.filter((row) =>
              this.filterByConfRoom.includes(row.conferenceRoom)
            );
        else
          this.filter_tableData = this.temp_tableData.filter((row) =>
            this.filterByStatus.includes(row.status)
          );
        this.temp_tableData = this.filter_tableData;
        this.display_tableData = this.temp_tableData;
        if (!tempFBS) this.filterByStatus = [];
      }

      if (filters["el-table_1_column_4"]) {
        let tempFBCF = true;
        if (filters["el-table_1_column_4"].length != 0)
          this.filterByConfRoom = filters["el-table_1_column_4"];
        else {
          this.filterByConfRoom = [
            "AT HOME Conference Room",
            "BARBIZON Conference Room",
            "MONALISA Conference Room",
            "SASSA Conference Room",
            "SWIMLAB Conference Room",
          ];
          tempFBCF = false;
        }
        this.filter_tableData_4 = [];
        if (!this.searchInput && this.filterByStatus.length == 0)
          this.filter_tableData_4 = this.tableData.filter((row) =>
            this.filterByConfRoom.includes(row.conferenceRoom)
          );
        else if (this.searchInput && this.filterByStatus.length == 0)
          this.filter_tableData_4 = this.search_tableData.filter((row) =>
            this.filterByConfRoom.includes(row.conferenceRoom)
          );
        else if (!this.searchInput && this.filterByStatus.length != 0)
          if (tempFBCF)
            this.filter_tableData_4 = this.filter_tableData.filter((row) =>
              this.filterByConfRoom.includes(row.conferenceRoom)
            );
          else
            this.filter_tableData_4 = this.tableData.filter((row) =>
              this.filterByStatus.includes(row.status)
            );
        else
          this.filter_tableData_4 = this.temp_tableData.filter((row) =>
            this.filterByConfRoom.includes(row.conferenceRoom)
          );
        this.temp_tableData = this.filter_tableData_4;
        this.display_tableData = this.temp_tableData;
        if (!tempFBCF) this.filterByConfRoom = [];
      }

      // if (filters["el-table_1_column_1"]) {
      //   let tempFBD = true;
      //   if (filters["el-table_1_column_1"].length != 0)
      //     this.filterByDate = filters["el-table_1_column_1"];
      //   else {
      //     this.filterByDate = [this.todayFilter[0].value];
      //     tempFBD = false;
      //   }
      //   this.filter_tableData_1 = [];
      //   if (
      //     !this.searchInput &&
      //     this.filterByStatus.length == 0 &&
      //     this.filterByConfRoom.length == 0
      //   )
      //     this.filter_tableData_1 = this.tableData.filter((row) =>
      //       this.filterByDate.includes(row.date)
      //     );
      //   else if (
      //     this.searchInput &&
      //     this.filterByStatus.length == 0 &&
      //     this.filterByConfRoom.length == 0
      //   )
      //     this.filter_tableData_1 = this.search_tableData.filter((row) =>
      //       this.filterByDate.includes(row.date)
      //     );
      //   else if (
      //     !this.searchInput &&
      //     this.filterByStatus.length != 0 &&
      //     this.filterByConfRoom.length == 0
      //   ) {
      //     if (tempFBD)
      //       this.filter_tableData_1 = this.filter_tableData.filter((row) =>
      //         this.filterByDate.includes(row.date)
      //       );
      //     else
      //       this.filter_tableData_1 = this.tableData.filter((row) =>
      //         this.filterByStatus.includes(row.status)
      //       );
      //   } else if (
      //     !this.searchInput &&
      //     this.filterByStatus.length == 0 &&
      //     this.filterByConfRoom.length != 0
      //   ) {
      //     if (tempFBD)
      //       this.filter_tableData_1 = this.filter_tableData_4.filter((row) =>
      //         this.filterByDate.includes(row.date)
      //       );
      //     else
      //       this.filter_tableData_1 = this.tableData.filter((row) =>
      //         this.filterByConfRoom.includes(row.conferenceRoom)
      //       );
      //   } else
      //     this.filter_tableData_1 = this.temp_tableData.filter((row) =>
      //       this.filterByDate.includes(row.date)
      //     );
      //   this.temp_tableData = this.filter_tableData_1;
      //   this.display_tableData = this.temp_tableData;
      //   if (!tempFBD) this.filterByDate = [];
      // }
    },
    onInputSearch() {
      if (this.searchInput) {
        this.search_tableData = [];
        if (this.filterByStatus.length == 0 && this.filterByConfRoom.length == 0)
          this.search_tableData = this.tableData.filter((row) => {
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
        else if (this.filterByStatus.length != 0 && this.filterByConfRoom.length == 0)
          this.search_tableData = this.filter_tableData.filter((row) => {
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
        else if (this.filterByStatus.length == 0 && this.filterByConfRoom.length != 0)
          this.search_tableData = this.filter_tableData_4.filter((row) => {
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
        else
          this.search_tableData = this.temp_tableData.filter((row) => {
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
        this.display_tableData = this.search_tableData;
      } else this.display_tableData = this.temp_tableData;
    },
    viewReservation(reservationDetails) {
      let result = this.tempTableData.find((item) => item.id === reservationDetails.id);

      this.reservationInformation = result;

      this.isView = true;
    },
    editReservation(reservationDetails) {
      if (this.checkTime(reservationDetails.startTime, reservationDetails.date)) {
        ElMessage({
          message:
            "You are unable to edit your reservation within 1 hour prior to the reserved start time.",
          duration: 6000,
          showClose: true,
        });
      } else {
        this.reservationInformations.conferenceRoom = reservationDetails.conferenceRoom;
        this.reservationInformations.date = this.changeDateFormat(
          reservationDetails.date
        );
        this.reservationInformations.startTime = reservationDetails.startTime;
        this.reservationInformations.endTime = reservationDetails.endTime;
        this.reservationInformations.department = reservationDetails.department;
        this.reservationInformations.email = reservationDetails.email;
        this.reservationInformations.equipment = reservationDetails.equipment.split(", ");
        this.reservationInformations.id = reservationDetails.id;
        this.reservationInformations.name = reservationDetails.name;
        this.reservationInformations.status = reservationDetails.status;
        this.reservationInformations.purpose = reservationDetails.purpose;
        this.reservationInformations.arrangement = reservationDetails.arrangement;

        this.isEdit = true;
      }
    },
    cancelReservation(reservationDetails, confirm) {
      if (confirm)
        axios
          .post("/cancelReservation", {
            id: reservationDetails.id,
          })
          .then((response) => {
            ElMessage.error("The reservation has been cancelled.");
            this.fetchReservations();
          })
          .catch((error) => {
            console.error(error);
          });
    },
    fetchReservations() {
      let tempTableData = [];
      axios
        .get("fetchAllReservations", {
          params: {
            position: this.$page.props.auth.user.position,
            email: this.$page.props.auth.user.email,
          },
        })
        .then((response) => {
          response.data.forEach((data) => {
            let obj = {
              id: data.id,
              date: data.date,
              time: data.timeSlot,
              startTime: data.timeStart,
              endTime: data.timeEnd,
              conferenceRoom: data.conferenceRoom,
              name: data.name,
              email: data.email,
              department: data.department,
              status: data.status,
              equipment: data.equipment,
              purpose: data.purpose,
              arrangement: data.arrangement,
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
    checkTime(timeInput, dateInput) {
      const specificDate = new Date(dateInput);

      const timeString = timeInput;
      const [time, period] = timeString.split(" ");
      const [hours, minutes] = time.split(":");
      const isPM = period === "PM";

      let hours24 = parseInt(hours, 10);

      if (isPM && hours24 !== 12) {
        hours24 += 12;
      } else if (!isPM && hours24 === 12) {
        hours24 = 0;
      }

      const timeDate = new Date();
      timeDate.setHours(hours24);
      timeDate.setMinutes(parseInt(minutes, 10));
      timeDate.setSeconds(0);

      specificDate.setHours(timeDate.getHours());
      specificDate.setMinutes(timeDate.getMinutes());

      const currentTime = new Date();
      const timeDiff = specificDate - currentTime;
      const oneHourInMillis = 60 * 60 * 1000;

      if (timeDiff < oneHourInMillis && timeDiff > -oneHourInMillis) {
        return true;
      } else {
        return false;
      }
    },
    handleCancel(row) {
      if (this.checkTime(row.startTime, row.date)) {
        this.$refs.popconfirmCancel.onClose();
        ElMessage({
          message:
            "You are unable to cancel your reservation within 1 hour prior to the reserved start time.",
          duration: 6000,
          showClose: true,
        });
      } else {
        console.log("The specified time is NOT within an hour of the current time.");
      }
    },
  },
};
</script>
