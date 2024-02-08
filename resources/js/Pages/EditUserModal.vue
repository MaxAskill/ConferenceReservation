<template>
  <div
    class="overflow-x-hidden overflow-y-auto fixed inset-0 z-50 outline-none focus:outline-none md:justify-center md:items-center flex bg-black bg-opacity-50 py-12"
  >
    <div class="relative my-auto mx-auto max-w-full px-2 md:px-0">
      <div
        class="border-0 rounded-lg shadow-lg relative flex flex-col w-full bg-white outline-none focus:outline-none"
      >
        <div
          class="flex items-center justify-end px-2 md:px-5 py-3 border-b border-solid border-slate-200 rounded-t"
        >
          <h1 class="text-lg font-bold px-10">EDIT USER INFORMATION</h1>
        </div>
        <div class="relative px-2 md:px-5 py-3 flex-auto">
          <div class="grid grid-cols-1 gap-y-2">
            <div class="relative">
              <label class="text-gray-500 text-sm">Name</label>
              <el-input
                v-model="userInformation.name"
                placeholder="Enter your name"
                @blur="blurfunction"
                required
              />
            </div>
            <div class="relative">
              <label class="text-gray-500 text-sm">Email</label>
              <el-input
                v-model="userInformation.email"
                placeholder="Enter your name"
                aria-readonly="true"
              />
            </div>
            <div class="relative">
              <label class="text-gray-500 text-sm">Department</label>
              <el-select
                v-model="userInformation.department"
                class="w-full"
                placeholder="Select Conference Room"
                required
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
              <label class="text-gray-500 text-sm">Position</label>
              <el-select
                v-model="userInformation.position"
                class="w-full"
                placeholder="Select Conference Room"
                required
              >
                <el-option
                  v-for="item in listPosition"
                  :key="item"
                  :label="item"
                  :value="item"
                />
              </el-select>
            </div>
            <div class="relative">
              <label class="text-gray-500 text-sm">Status</label>
              <el-select
                v-model="userInformation.status"
                class="w-full"
                placeholder="Select Conference Room"
                required
              >
                <el-option
                  v-for="item in listStatus"
                  :key="item"
                  :label="item"
                  :value="item"
                />
              </el-select>
            </div>
          </div>
        </div>
        <div
          class="flex items-center justify-center px-5 py-3 border-t border-solid border-slate-200 rounded-b"
        >
          <div class="flex">
            <el-button class="mx-auto" type="success" @click="updateUser" plain>
              Update
            </el-button>
            <el-button class="mx-auto" type="info" @click="$emit('close')" plain>
              Cancel
            </el-button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
<script>
import { Head, Link } from "@inertiajs/vue3";
import { ElButton, ElMessage, ElMessageBox, ElProgress, buttonEmits } from "element-plus";
import FullCalendar from "@fullcalendar/vue3";
import dayGridPlugin from "@fullcalendar/daygrid";
import timeGridPlugin from "@fullcalendar/timegrid";
import listPlugin from "@fullcalendar/list";
import interactionPlugin from "@fullcalendar/interaction";

export default {
  components: {
    FullCalendar,
    Link,
  },
  props: ["userInformation"],
  data() {
    return {
      listDepartment: [
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
      listPosition: ["User", "Agent", "Admin"],
      listStatus: ["Active", "Inactive"],
    };
  },
  created() {
    setInterval(this.dateTime, 1000);
    window.addEventListener("resize", this.handleViewportResize);
  },
  mounted() {
    console.log(new Date());
  },
  watch: {},
  methods: {
    blurfunction() {
      console.log("Mabbuhay", this.datasss);
      console.log("Mabbuhay2222", this.userInformation);
    },
    updateUser() {
      console.log("User Info", this.userInformation);

      axios
        .post("/updateUser", {
          email: this.userInformation.email,
          name: this.userInformation.name,
          department: this.userInformation.department,
          status: this.userInformation.status,
          position: this.userInformation.position,
        })
        .then((response) => {
          console.log(response.data);
          window.location.reload();
        })
        .catch((error) => {
          console.error(error);
        });
    },
    reserveConferenceRoom() {
      console.log(
        "Reserve: ",
        this.dateSchedule,
        this.employeeName,
        this.department,
        this.purpose,
        this.conferenceRoom,
        this.timeSlot
      );
    },
  },
};
</script>
