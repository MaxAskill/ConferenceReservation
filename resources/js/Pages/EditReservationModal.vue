<template>
  <div
    class="overflow-x-hidden overflow-y-auto fixed inset-0 z-50 outline-none focus:outline-none md:justify-center md:items-center flex bg-black bg-opacity-50 py-12"
  >
    <div class="relative my-auto mx-auto max-w-full px-2 md:px-0">
      <div
        class="border-0 rounded-lg shadow-lg relative flex flex-col w-full bg-white outline-none focus:outline-none"
      >
        <div
          class="flex items-center justify-center px-2 md:px-5 py-3 border-b border-solid border-slate-200 rounded-t"
        >
          <h1 class="text-lg font-bold px-10">UPDATE CONFERENCE RESERVATION</h1>
        </div>
        <div class="relative px-2 md:px-5 py-3 flex-auto">
          <div class="grid grid-cols-1 gap-y-2">
            <div class="relative">
              <label class="text-gray-500 text-sm">Conference Room</label>
              <el-select
                v-model="reservationInformation.conferenceRoom"
                class="w-full"
                placeholder="Select Conference Room"
                required
                :disabled="isDisable"
              >
                <el-option
                  v-for="item in listConferenceRoom"
                  :key="item"
                  :label="item"
                  :value="item"
                />
              </el-select>
            </div>
            <div class="relative">
              <div class="">
                <label class="text-gray-500 text-sm">Date</label><br />
                <el-date-picker
                  style="width: 100%"
                  v-model="reservationInformation.date"
                  @change="checkTimeRange"
                  @blur="retrieveData('date')"
                  type="date"
                  placeholder="Pick a Date"
                  format="YYYY-MM-DD"
                  value-format="YYYY-MM-DD"
                  :disabled-date="disabledDate"
                />
              </div>
            </div>
            <div class="relative">
              <label class="text-gray-500 text-sm">Time Range</label>
              <div class="grid grid-cols-2 gap-3">
                <el-time-select
                  v-model="reservationInformation.startTime"
                  @change="checkTimeRange"
                  @blur="retrieveData('startTime')"
                  :max-time="reservationInformation.endTime"
                  :min-time="minTimeForStartTime"
                  class="mr-4"
                  placeholder="Start time"
                  start="07:30"
                  step="00:30"
                  end="19:00"
                  format="hh:mm A"
                />
                <el-time-select
                  v-model="reservationInformation.endTime"
                  @change="checkTimeRange"
                  @blur="retrieveData('endTime')"
                  :min-time="minTimeForEndTime"
                  placeholder="End time"
                  start="08:00"
                  step="00:30"
                  end="19:30"
                  format="hh:mm A"
                />
              </div>
              <el-text v-if="isOccupied" class="px-2" type="danger" size="small"
                >Selected Time is Already Occupied.
              </el-text>
            </div>
            <div class="relative">
              <label class="text-gray-500 text-sm">Requestor Name</label>
              <el-input
                v-model="reservationInformation.name"
                @blur="retrieveData('name')"
                placeholder="Enter your name"
                required
                :disabled="isDisable"
              />
            </div>
            <div class="relative">
              <label class="text-gray-500 text-sm">Email</label>
              <el-input
                v-model="reservationInformation.email"
                @blur="retrieveData('email')"
                type="email"
                placeholder="Please input"
                required
                :disabled="isDisable"
              />
            </div>
            <div class="relative">
              <label class="text-gray-500 text-sm">Department</label>
              <el-select
                v-model="reservationInformation.department"
                class="w-full"
                placeholder="Select Department"
                required
                :disabled="isDisable"
              >
                <el-option
                  v-for="item in listDepartment"
                  :key="item"
                  :label="item"
                  :value="item"
                />
              </el-select>
            </div>
            <div class="relative">
              <label class="text-gray-500 text-sm">Purpose</label>
              <el-input
                v-model="reservationInformation.purpose"
                @blur="retrieveData('purpose')"
                :autosize="{ minRows: 3, maxRows: 5 }"
                maxlength="100"
                show-word-limit
                type="textarea"
                placeholder="Please input"
                required
                :disabled="isDisable"
              />
            </div>
            <div class="relative">
              <label class="text-gray-500 text-sm">IT Equipment</label>
              <el-select
                v-model="reservationInformation.equipment"
                @blur="retrieveData('equipment')"
                multiple
                collapse-tags
                collapse-tags-tooltip
                :max-collapse-tags="10"
                placeholder="Select"
                class="w-full"
                required
                :disabled="isDisable"
              >
                <el-option
                  v-for="item in listITEquipment"
                  :key="item"
                  :label="item"
                  :value="item"
                />
              </el-select>
            </div>
            <div class="relative">
              <div class="my-2 flex items-center text-sm">
                <label>Chairs and Tables Arrangement?</label>
                <el-radio-group
                  v-model="reservationInformation.arrangement"
                  class="ml-4"
                  :disabled="isDisable"
                >
                  <el-radio label="Yes">Yes</el-radio>
                  <el-radio label="No">No</el-radio>
                </el-radio-group>
              </div>
            </div>
          </div>
        </div>
        <div
          class="flex items-center justify-center px-5 py-3 border-t border-solid border-slate-200 rounded-b"
        >
          <div class="flex gap-3">
            <el-tooltip
              effect="dark"
              content="Edit this reservation on the home page with the calendar view."
              placement="bottom"
            >
              <el-link href="http://192.168.0.7:89/" type="primary" @click="setID">
                Edit with Calendar view
              </el-link>
            </el-tooltip>
            <el-button
              type="warning"
              @click="updateReservation"
              plain
              :loading-icon="Eleme"
              :loading="isLoadingBtn"
              style="margin: 0px"
            >
              Save Changes
            </el-button>
            <el-button style="margin: 0px" type="info" @click="$emit('close')" plain>
              <el-icon><CircleClose /></el-icon>
            </el-button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
<script>
import { Link } from "@inertiajs/vue3";
import { ElButton, ElMessage } from "element-plus";
import { Eleme } from "@element-plus/icons-vue";
import FullCalendar from "@fullcalendar/vue3";
import axios from "axios";

export default {
  components: {
    FullCalendar,
    Link,
  },
  props: ["reservationInformation"],
  data() {
    return {
      isDisable: true,
      tempData: {},
      isOccupied: false,
      isLoadingBtn: false,
      listDepartment: [
        "Office of the President",
        "EPC Merchandising",
        "EPC Sales",
        "NBFI Merchandising",
        "NBFI Sales",
        "Finance",
        "Human Resource",
        "Marketing",
        "MIS",
        "Operations",
      ],

      listConferenceRoom: [
        "AT HOME Conference Room",
        "BARBIZON Conference Room",
        "MONALISA Conference Room",
        "SASSA Conference Room",
        "SWIMLAB Conference Room",
      ],
      listITEquipment: ["None", "Projector", "Laptop & mouse", "Pointer"],
    };
  },
  created() {
    setInterval(this.dateTime, 1000);
    window.addEventListener("resize", this.handleViewportResize);
  },
  mounted() {
    this.tempData = { ...this.reservationInformation };
    console.log("MOUNTED EDIT::", this.tempData);
    this.checkLogin();
  },
  computed: {
    minTimeForStartTime() {
      var today = new Date();
      var date = today.toISOString().split("T")[0];
      var hours = today.getHours();
      var minutes = today.getMinutes();
      if (this.reservationInformation.date == date) {
        var formattedTime = hours + ":" + (minutes < 10 ? "0" : "") + minutes;
        return formattedTime;
      } else return "07:00";
    },
    minTimeForEndTime() {
      var today = new Date();
      var date = today.toISOString().split("T")[0];
      var hours = today.getHours();
      hours += 1;
      var minutes = today.getMinutes();
      if (this.reservationInformation.startTime) {
        return this.reservationInformation.startTime;
      } else {
        if (this.reservationInformation.date == date) {
          var formattedTime = hours + ":" + (minutes < 10 ? "0" : "") + minutes;
          return formattedTime;
        } else return this.reservationInformation.startTime;
      }
    },
  },
  watch: {},
  methods: {
    setID() {
      sessionStorage.setItem("eventID", this.tempData.id);
      sessionStorage.setItem("conferenceRoom", this.tempData.conferenceRoom);
    },
    disabledDate(time) {
      return time.getTime() < Date.now() - 8.64e7;
    },
    checkLogin() {
      this.isDisable = this.$page.props.auth.user.position == "Admin" ? false : true;
    },
    updateReservation() {
      if (!this.isOccupied) {
        this.isLoadingBtn = true;
        axios
          .post("/updateReservation", {
            id: this.reservationInformation.id,
            conference_room: this.reservationInformation.conferenceRoom,
            date: this.reservationInformation.date,
            time_start: this.convert12HourTo24Hour(this.reservationInformation.startTime),
            time_end: this.convert12HourTo24Hour(this.reservationInformation.endTime),
            employee_name: this.reservationInformation.name,
            email: this.reservationInformation.email,
            department: this.reservationInformation.department,
            purpose: this.reservationInformation.purpose,
            equipment: this.reservationInformation.equipment.join(", "),
            ct_arrangement: this.reservationInformation.arrangement,
          })
          .then((response) => {
            if (response.data == "Already Occupied.") {
              this.isOccupied = true;
              ElMessage({
                message:
                  "Your selected Time Range is already occupied. Kindly select another Time Range.",
                type: "error",
                duration: 6000,
                showClose: true,
              });
            } else {
              ElMessage({
                message: "The reservation was updated successfully.",
                type: "success",
                duration: 3000,
              });
              this.$emit("updated");
              this.$emit("close");
            }
            this.isLoadingBtn = false;
          })
          .catch((error) => {
            console.error(error);
          });
      }
    },
    checkTimeRange() {
      if (
        this.reservationInformation.startTime &&
        this.reservationInformation.endTime &&
        this.reservationInformation.date
      ) {
        axios
          .get("/checkTimeRange", {
            params: {
              id: this.reservationInformation.id,
              conference_room: this.reservationInformation.conferenceRoom,
              date: this.reservationInformation.date,
              time_start: this.convert12HourTo24Hour(
                this.reservationInformation.startTime
              ),
              time_end: this.convert12HourTo24Hour(this.reservationInformation.endTime),
            },
          })
          .then((response) => {
            if (!response.data) {
              this.$emit("updated");
              this.isOccupied = true;
            } else this.isOccupied = false;
          })
          .catch((error) => {
            console.error(error);
          });
      } else this.isOccupied = false;
    },
    convert12HourTo24Hour(time12Hour) {
      // Split the input into hours, minutes, and AM/PM parts
      const [time, period] = time12Hour.split(" ");

      // Split the time into hours and minutes
      const [hours, minutes] = time.split(":").map(Number);

      // Convert to 24-hour format
      let hours24 = hours;
      if (period === "PM" && hours !== 12) {
        hours24 += 12;
      } else if (period === "AM" && hours === 12) {
        hours24 = 0;
      }

      // Format the result as "HH:mm"
      const formattedTime = `${hours24
        .toString()
        .padStart(2, "0")}:${minutes.toString().padStart(2, "0")}`;

      return formattedTime;
    },
    retrieveData(key) {
      if (key == "equipment") {
        if (this.reservationInformation[key].length == 0)
          this.reservationInformation[key] = this.tempData[key];
      } else if (!this.reservationInformation[key])
        this.reservationInformation[key] = this.tempData[key];
    },
  },
};
</script>
<style>
.el-picker-panel__icon-btn.d-arrow-right,
.el-picker-panel__icon-btn.d-arrow-left {
  display: none;
}
</style>
