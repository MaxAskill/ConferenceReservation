<template>
  <!-- <div class="mx-auto max-h-[85vh] w-full overflow-y-auto"> -->
  <div class="mx-auto w-full">
    <div class="w-auto mb-2 px-2.5 3xs:px-3 2xs:px-4 sm:px-6 lg:px-8">
      <div class="bg-white p-6 rounded-xl drop-shadow-lg">
        <div class="grid grid-cols-1 gap-y-2">
          <div class="relative w-full">
            <!-- <p>{{ reservationInformation }}</p> -->
            <label class="text-gray-500 text-sm">Conference Room</label>
            <el-select
              v-model="reservationInformation.conferenceRoom"
              @change="
                $emit('transferConfRoom', $event);
                clearForm();
              "
              @blur="
                isValid.conferenceRoom = !reservationInformation.conferenceRoom
                  ? true
                  : false
              "
              :class="{
                'w-full border border-rose-600 rounded': isValid.conferenceRoom,
                'w-full': !isValid.conferenceRoom,
              }"
              placeholder="Select Conference Room"
              required
            >
              <el-option
                v-for="item in listConferenceRoom"
                :key="item"
                :label="item"
                :value="item"
              />
            </el-select>
            <el-text
              v-show="isValid.conferenceRoom"
              class="px-2"
              type="danger"
              size="small"
              >Conference Room is required.
            </el-text>
          </div>
          <div class="relative w-full">
            <label class="text-gray-500 text-sm">Date</label><br />
            <el-date-picker
              style="width: 100%"
              v-model="reservationInformation.dateSchedule"
              @change="sendSelectedDateToSchedulesCalendar($event)"
              @blur="
                isValid.dateSchedule = !reservationInformation.dateSchedule ? true : false
              "
              :class="{
                'border border-rose-600 rounded': isValid.dateSchedule,
                '': !isValid.dateSchedule,
              }"
              type="date"
              placeholder="Pick a Date"
              format="YYYY-MM-DD"
              value-format="YYYY-MM-DD"
              :disabled-date="disabledDate"
              :disabled="isLoggedIn"
            />
            <br />
            <el-text v-show="isValid.dateSchedule" class="px-2" type="danger" size="small"
              >Date is required.
            </el-text>
          </div>
          <div class="relative w-full">
            <label class="text-gray-500 text-sm">Time Range</label>
            <div class="grid grid-cols-2 gap-3">
              <el-time-select
                v-model="reservationInformation.startTime"
                :max-time="reservationInformation.endTime"
                :min-time="minTimeForStartTime"
                class="w-full"
                placeholder="Start Time"
                start="07:30"
                step="00:30"
                end="19:30"
                format="hh:mm A"
                :disabled="isLoggedIn"
                @blur="
                  isValid.startTime = !reservationInformation.startTime ? true : false
                "
                :class="{
                  'w-full border border-rose-600 rounded': isValid.startTime,
                  'w-full': !isValid.startTime,
                }"
              />
              <el-time-select
                v-model="reservationInformation.endTime"
                :min-time="minTimeForEndTime"
                class="w-full"
                placeholder="End Time"
                start="08:00"
                step="00:30"
                end="19:30"
                format="hh:mm A"
                :disabled="isLoggedIn"
                @blur="isValid.endTime = !reservationInformation.endTime ? true : false"
                :class="{
                  'w-full border border-rose-600 rounded': isValid.endTime,
                  'w-full': !isValid.endTime,
                }"
              />
            </div>
            <el-text v-if="isOccupied" class="px-2" type="danger" size="small"
              >Time is Occupied.
            </el-text>
            <el-text
              v-else-if="isValid.startTime || isValid.endTime"
              class="px-2"
              type="danger"
              size="small"
              >Time Range is required.
            </el-text>
          </div>
          <div class="relative w-full">
            <label class="text-gray-500 text-sm">Requestor Name</label>
            <el-input
              v-model="reservationInformation.employeeName"
              @blur="
                isValid.employeeName = !reservationInformation.employeeName ? true : false
              "
              :class="{
                'w-full border border-rose-600 rounded': isValid.employeeName,
                'w-full': !isValid.employeeName,
              }"
              placeholder="Enter your name"
              required
              :disabled="isLoggedIn || isDisableEmployeeName"
            />
            <el-text v-show="isValid.employeeName" class="px-2" type="danger" size="small"
              >Requestor Name is required.
            </el-text>
          </div>
          <div class="relative w-full">
            <label class="text-gray-500 text-sm">Email</label>
            <el-input
              v-model="reservationInformation.employeeEmail"
              @input="validateEmail"
              @blur="
                isValid.employeeEmail = !reservationInformation.employeeEmail
                  ? true
                  : false
              "
              :class="{
                'w-full border border-rose-600 rounded': isValid.employeeEmail,
                'w-full': !isValid.employeeEmail,
              }"
              type="email"
              placeholder="Please input"
              required
              :disabled="isLoggedIn || isDisableEmployeeEmail"
            />
            <el-text v-if="isEmailValid" class="px-2" type="danger" size="small"
              >Invalid Email.
            </el-text>
            <el-text
              v-else-if="isValid.employeeEmail"
              class="px-2"
              type="danger"
              size="small"
              >Email is required.
            </el-text>
          </div>
          <div class="relative w-full">
            <label class="text-gray-500 text-sm">Department</label>
            <!-- <el-input
              v-model="reservationInformation.department"
              @blur="
                isValid.department = !reservationInformation.department ? true : false
              "
              :class="{
                'w-full border border-rose-600 rounded': isValid.department,
                'w-full': !isValid.department,
              }"
              placeholder="Please input"
              required
              :disabled="isDisable"
            /> -->
            <el-select
              v-model="reservationInformation.department"
              @blur="
                isValid.department = !reservationInformation.department ? true : false
              "
              :class="{
                'w-full border border-rose-600 rounded': isValid.department,
                'w-full': !isValid.department,
              }"
              class="w-full"
              placeholder="Select Department"
              required
              :disabled="isLoggedIn || isDisableDepartment"
            >
              <el-option
                v-for="item in listDepartment"
                :key="item"
                :label="item"
                :value="item"
              />
            </el-select>
            <el-text v-show="isValid.department" class="px-2" type="danger" size="small"
              >Department is required.
            </el-text>
          </div>
          <div class="relative w-full">
            <label class="text-gray-500 text-sm">Purpose</label>
            <!-- <el-input v-model="purpose" placeholder="Please input" /> -->
            <el-input
              v-model="reservationInformation.purpose"
              @blur="isValid.purpose = !reservationInformation.purpose ? true : false"
              :class="{
                'w-full border border-rose-600 rounded': isValid.purpose,
                'w-full': !isValid.purpose,
              }"
              :autosize="{ minRows: 3, maxRows: 5 }"
              maxlength="100"
              show-word-limit
              type="textarea"
              placeholder="Please input"
              required
              :disabled="isLoggedIn"
            />
            <el-text v-show="isValid.purpose" class="px-2" type="danger" size="small"
              >Purpose is required.
            </el-text>
          </div>
          <div class="relative w-full">
            <label class="text-gray-500 text-sm">IT Equipment</label>
            <el-select
              v-model="reservationInformation.equipment"
              @blur="
                isValid.equipment =
                  reservationInformation.equipment.length === 0 ? true : false
              "
              :class="{
                'w-full border border-rose-600 rounded': isValid.equipment,
                'w-full': !isValid.equipment,
              }"
              multiple
              collapse-tags
              collapse-tags-tooltip
              :max-collapse-tags="10"
              placeholder="Select"
              class="w-full"
              required
              :disabled="isLoggedIn"
            >
              <el-option
                v-for="item in listITEquipment"
                :key="item"
                :label="item"
                :value="item"
              />
            </el-select>
            <el-text v-show="isValid.equipment" class="px-2" type="danger" size="small"
              >Equipment is required.
            </el-text>
          </div>
          <div class="relative w-full">
            <div
              class="my-2 flex items-center text-sm"
              @blur="
                isValid.ctArrangement = !reservationInformation.ctArrangement
                  ? true
                  : false
              "
              :class="{
                'w-full border border-rose-600 rounded': isValid.ctArrangement,
                'w-full': !isValid.ctArrangement,
              }"
            >
              <label>Chairs and Tables Arrangement?</label>
              <el-radio-group
                v-model="reservationInformation.ctArrangement"
                class="ml-4"
                :disabled="isLoggedIn"
              >
                <el-radio label="Yes">Yes</el-radio>
                <el-radio label="No">No</el-radio>
              </el-radio-group>
            </div>
            <el-text
              v-show="isValid.ctArrangement"
              class="px-2"
              type="danger"
              size="small"
              >Chairs and Tables Arrangement is required.
            </el-text>
          </div>

          <div v-if="showCreateNew">
            <div v-if="!isEditMode" class="flex justify-center gap-2">
              <el-button
                type="success"
                @click="createNew"
                plain
                :loading-icon="Eleme"
                :loading="isLoadingBtn"
                >Create New
              </el-button>
              <el-tooltip content="Edit Reservation" placement="bottom">
                <el-button
                  v-show="isUser && reservationInformation.status !== 'Completed'"
                  type="primary"
                  plain
                  style="margin: 0px; padding: 4px 8px"
                  @click="handleEdit()"
                >
                  <el-icon><Edit /> </el-icon>
                </el-button>
              </el-tooltip>
              <el-popconfirm
                width="210"
                confirm-button-text="Confirm"
                cancel-button-text="Cancel"
                :icon="WarningFilled"
                icon-color="#c45656"
                title="Are you sure you want to cancel this reservation?"
                @confirm="cancelReservation()"
              >
                <template #reference>
                  <div>
                    <el-tooltip content="Cancel Reservation" placement="bottom">
                      <el-button
                        v-show="isUser && reservationInformation.status !== 'Completed'"
                        type="danger"
                        plain
                        style="margin: 0px; padding: 4px 8px"
                        @click="handleCancel"
                      >
                        <el-icon><CircleClose /></el-icon>
                      </el-button>
                    </el-tooltip>
                  </div>
                </template>
              </el-popconfirm>
            </div>
            <div v-else class="flex justify-center gap-2">
              <el-popconfirm
                width="190"
                confirm-button-text="Confirm"
                cancel-button-text="Cancel"
                title="Are you sure you want to save these changes for this reservation?"
                @confirm="updateReservation()"
              >
                <template #reference>
                  <el-button
                    type="warning"
                    @click="saveChanges"
                    plain
                    :loading-icon="Eleme"
                    :loading="isLoadingBtn"
                    >Save Changes
                  </el-button>
                </template>
              </el-popconfirm>
              <el-tooltip content="Discard Changes" placement="bottom">
                <el-button
                  type="info"
                  plain
                  style="margin: 0px; padding: 0px 8px"
                  @click="handleCloseEdit()"
                  :disabled="isLoadingBtn"
                >
                  <el-icon><CloseBold /> </el-icon>
                </el-button>
              </el-tooltip>
            </div>
          </div>

          <el-button
            v-else
            class="mx-auto"
            type="success"
            @click="reserveConferenceRoom"
            plain
            :loading-icon="Eleme"
            :loading="isLoadingBtn"
            >Reserve</el-button
          >
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import AuthenticatedLayout from "@/Layouts/DashboardLayout.vue";
import { Head, Link } from "@inertiajs/vue3";
import axios from "axios";
import { ElButton, ElMessage, ElMessageBox, ElProgress, buttonEmits } from "element-plus";
import { Eleme, Edit, Delete } from "@element-plus/icons-vue";

export default {
  components: {
    AuthenticatedLayout,
    Link,
  },
  props: ["transferReservationInformation", "clearingForm"],
  data() {
    return {
      default: {
        dateSchedule: "",
        employeeName: "",
        employeeEmail: "",
        department: "",
        purpose: "",
        conferenceRoom: "AT HOME Conference Room",
        // timeSlot: [],
        startTime: "",
        endTime: "",
        equipment: [],
        ctArrangement: "Yes",
      },
      reservationInformation: {},
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

      isValid: {
        dateSchedule: false,
        employeeName: false,
        employeeEmail: false,
        department: false,
        purpose: false,
        equipment: false,
        ctArrangement: false,
        conferenceRoom: false,
        startTime: false,
        endTime: false,
      },
      isOccupied: false,
      isEmailValid: false,
      isLoggedIn: false,
      isEditMode: false,
      isUser: false,
      isLoadingBtn: false,
      showCreateNew: false,
      isDisableEmployeeName: false,
      isDisableEmployeeEmail: false,
      isDisableDepartment: false,
      checkID: null,
      tempEdit: {},
    };
  },
  created() {
    this.reservationInformation = { ...this.default };
    setInterval(this.dateTime, 1000);
    window.addEventListener("resize", this.handleViewportResize);
  },
  mounted() {
    this.checkLogin();
  },
  computed: {
    minTimeForStartTime() {
      var today = new Date();
      var date = today.toISOString().split("T")[0];
      var hours = today.getHours();
      var minutes = today.getMinutes();
      if (this.reservationInformation.dateSchedule == date) {
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
        if (this.reservationInformation.dateSchedule == date) {
          var formattedTime = hours + ":" + (minutes < 10 ? "0" : "") + minutes;
          return formattedTime;
        } else return this.reservationInformation.startTime;
      }
    },
  },
  watch: {
    "reservationInformation.startTime": "checkTimeRange",
    "reservationInformation.endTime": "checkTimeRange",
    "reservationInformation.dateSchedule": "checkTimeRange",
    transferReservationInformation: {
      handler(val) {
        this.checkReservation();
      },
      deep: true,
    },
    clearingForm: {
      handler(val) {
        this.clearedFormLogout();
      },
      deep: true,
    },
  },
  methods: {
    clearedFormLogout() {
      this.isLoggedIn = false;
      this.reservationInformation = { ...this.clearingForm };

      this.isDisableEmployeeName = false;
      this.isDisableEmployeeEmail = false;
      this.isDisableDepartment = false;

      this.isValid.dateSchedule = false;
      this.isValid.employeeName = false;
      this.isValid.department = false;
      this.isValid.purpose = false;
      this.isValid.employeeEmail = false;
      this.isValid.startTime = false;
      this.isValid.endTime = false;
      this.isValid.ctArrangement = false;
      this.isValid.conferenceRoom = false;
      this.isValid.equipment = false;
    },
    clearForm() {
      this.reservationInformation.dateSchedule = "";
      this.reservationInformation.employeeName = "";
      this.reservationInformation.employeeEmail = "";
      this.reservationInformation.department = "";
      this.reservationInformation.purpose = "";
      this.reservationInformation.equipment = [];
      this.reservationInformation.ctArrangement = "Yes";
      this.reservationInformation.startTime = "";
      this.reservationInformation.endTime = "";
      this.isLoggedIn = false;

      if (this.reservationInformation.conferenceRoom == "MONALISA Conference Room") {
        this.listITEquipment.push("Camera");
        this.listITEquipment.push("Photoshoot Equipment");
      } else {
        this.listITEquipment.splice(4, 2);
      }

      this.checkLogin();
    },
    createNew() {
      this.reservationInformation = {
        ...this.default,
        conferenceRoom: this.reservationInformation.conferenceRoom,
      };

      this.showCreateNew = false;
      this.checkID = null;
      this.isLoggedIn = false;
      this.$emit("fetchMeeting");
      this.checkLogin();
    },
    checkLogin() {
      if (this.$page.props.auth.user) {
        this.reservationInformation.employeeName = this.$page.props.auth.user.name;
        this.reservationInformation.employeeEmail = this.$page.props.auth.user.email;
        this.reservationInformation.department = this.$page.props.auth.user.department;

        console.log("Reservation", this.reservationInformation.employeeName);
        this.isDisableEmployeeName = true;
        this.isDisableEmployeeEmail = true;
        this.isDisableDepartment = true;
      }
    },
    checkReservation() {
      this.reservationInformation = { ...this.transferReservationInformation };
      this.checkID = this.reservationInformation.id;
      this.reservationInformation.startTime = this.reservationInformation.startTime;
      this.reservationInformation.endTime = this.reservationInformation.endTime;

      console.log("Status", this.reservationInformation);
      if (this.$page.props.auth.user)
        this.isUser =
          this.reservationInformation.employeeEmail != this.$page.props.auth.user.email
            ? false
            : true;

      this.isLoggedIn = true;
      this.showCreateNew = true;
      this.isEditMode = false;

      this.formValidation();
    },
    handleEdit() {
      if (
        this.checkTime(
          this.reservationInformation.startTime,
          this.reservationInformation.dateSchedule
        )
      ) {
        ElMessage({
          message:
            "You are unable to edit your reservation within 1 hour prior to the reserved start time.",
          duration: 6000,
          showClose: true,
        });
      } else {
        this.isEditMode = true;
        this.isLoggedIn = false;

        this.isDisableEmployeeName = false;
        this.isDisableEmployeeEmail = false;
        this.isDisableDepartment = false;
        this.tempEdit = {};
        this.tempEdit = { ...this.reservationInformation };
      }
    },
    handleCloseEdit() {
      this.isEditMode = false;
      this.isLoggedIn = true;

      this.reservationInformation = { ...this.tempEdit };

      this.formValidation();
    },
    handleCancel() {
      if (
        this.checkTime(
          this.reservationInformation.startTime,
          this.reservationInformation.dateSchedule
        )
      ) {
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
    updateReservation() {
      this.formValidation();
      let decision = Object.values(this.isValid).includes(true) ? true : false;

      if (!decision && !this.isOccupied) {
        this.isLoadingBtn = true;
        axios
          .post("/updateReservation", {
            id: this.reservationInformation.id,
            conference_room: this.reservationInformation.conferenceRoom,
            date: this.reservationInformation.dateSchedule,
            time_start: this.convert12HourTo24Hour(this.reservationInformation.startTime),
            time_end: this.convert12HourTo24Hour(this.reservationInformation.endTime),
            employee_name: this.reservationInformation.employeeName,
            email: this.reservationInformation.employeeEmail,
            department: this.reservationInformation.department,
            purpose: this.reservationInformation.purpose,
            equipment: this.reservationInformation.equipment.join(", "),
            ct_arrangement: this.reservationInformation.ctArrangement,
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
                message: "Your reservation was updated successfully.",
                type: "success",
                duration: 3000,
              });
              this.$emit("fetchMeeting");
              this.createNew();
              this.isEditMode = false;
            }
            this.isLoadingBtn = false;
          })
          .catch((error) => {
            console.error(error);
          });
      }
    },
    cancelReservation() {
      axios
        .post("/cancelReservation", {
          id: this.checkID,
        })
        .then((response) => {
          ElMessage.error("Your reservation has been cancelled.");
          this.$emit("fetchMeeting");
          this.createNew();
        })
        .catch((error) => {
          console.error(error);
        });
    },
    disabledDate(time) {
      return time.getTime() < Date.now() - 8.64e7;
    },
    formValidation() {
      this.isValid.dateSchedule = !this.reservationInformation.dateSchedule
        ? true
        : false;
      this.isValid.employeeName = !this.reservationInformation.employeeName
        ? true
        : false;
      this.isValid.department = !this.reservationInformation.department ? true : false;
      this.isValid.purpose = !this.reservationInformation.purpose ? true : false;
      this.isValid.employeeEmail = !this.reservationInformation.employeeEmail
        ? true
        : false;
      this.isValid.startTime = !this.reservationInformation.startTime.length
        ? true
        : false;
      this.isValid.endTime = !this.reservationInformation.endTime.length ? true : false;
      this.isValid.ctArrangement = !this.reservationInformation.ctArrangement
        ? true
        : false;
      this.isValid.conferenceRoom = !this.reservationInformation.conferenceRoom
        ? true
        : false;
      this.isValid.equipment =
        this.reservationInformation.equipment.length === 0 ? true : false;

      // let decision = Object.values(this.isValid).includes(true) ? true : false;
      // return decision;
    },
    reserveConferenceRoom() {
      this.formValidation();
      let decision = Object.values(this.isValid).includes(true) ? true : false;

      if (!decision && !this.isOccupied && !this.isEmailValid) {
        this.isLoadingBtn = true;
        axios
          .post("/createReservation", {
            conference_room: this.reservationInformation.conferenceRoom,
            date: this.reservationInformation.dateSchedule,
            time_start: this.convert12HourTo24Hour(this.reservationInformation.startTime),
            time_end: this.convert12HourTo24Hour(this.reservationInformation.endTime),
            employee_name: this.capitalizeEachWord(
              this.reservationInformation.employeeName
            ),
            email: this.reservationInformation.employeeEmail,
            department: this.reservationInformation.department,
            purpose: this.reservationInformation.purpose,
            equipment: this.reservationInformation.equipment.join(", "),
            ct_arrangement: this.reservationInformation.ctArrangement,
          })
          .then((response) => {
            if (response.data == "Already Occupied.") {
              ElMessage({
                message:
                  "Your selected Time Range is already occupied. Kindly select another Time Range.",
                type: "error",
                duration: 6000,
                showClose: true,
              });
              this.isOccupied = true;
            } else {
              ElMessage({
                message: "Your reservation was saved successfully.",
                type: "success",
                duration: 5000,
              });
              this.reservationInformation.dateSchedule = "";
              this.reservationInformation.employeeName = "";
              this.reservationInformation.employeeEmail = "";
              this.reservationInformation.department = "";
              this.reservationInformation.purpose = "";
              this.reservationInformation.equipment = [];
              this.reservationInformation.ctArrangement = "Yes";
              this.reservationInformation.startTime = "";
              this.reservationInformation.endTime = "";
            }
            this.checkLogin();
            this.isLoadingBtn = false;
            this.$emit("fetchMeeting");
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
        this.reservationInformation.dateSchedule
      ) {
        axios
          .get("/checkTimeRange", {
            params: {
              id: this.checkID,
              conference_room: this.reservationInformation.conferenceRoom,
              date: this.reservationInformation.dateSchedule,
              time_start: this.convert12HourTo24Hour(
                this.reservationInformation.startTime
              ),
              time_end: this.convert12HourTo24Hour(this.reservationInformation.endTime),
            },
          })
          .then((response) => {
            if (!response.data) {
              this.$emit("fetchMeeting");
              this.isOccupied = true;
            } else this.isOccupied = false;
          })
          .catch((error) => {
            console.error(error);
          });
      } else this.isOccupied = false;
    },
    sendSelectedDateToSchedulesCalendar(date_selected) {
      this.$emit("goToDate", date_selected);
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

    capitalizeEachWord(text) {
      let pattern = /\w*/g;
      let result = text.replace(pattern, function (word) {
        return word.charAt(0).toUpperCase() + word.substr(1).toLowerCase();
      });

      return result;
    },
    validateEmail(inputEmail) {
      if (inputEmail) {
        if (/^[\w.-]+@[a-zA-Z_-]+?\.[a-zA-Z]{2,3}$/.test(inputEmail)) {
          this.isEmailValid = false;
        } else this.isEmailValid = true;
      } else this.isEmailValid = false;
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
  },
};
</script>
<style>
.el-picker-panel__icon-btn.d-arrow-right,
.el-picker-panel__icon-btn.d-arrow-left {
  display: none;
}
</style>
