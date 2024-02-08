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
        ></div>
        <div class="relative px-2 md:px-5 py-3 flex-auto">
          Are you sure you want to cancel the reservation?
        </div>
        <div
          class="flex items-center justify-center px-5 py-3 border-t border-solid border-slate-200 rounded-b"
        >
          <div class="flex">
            <el-button class="mx-auto" type="primary" @click="cancelReservation" plain
              >Yes</el-button
            >
            <el-button class="mx-auto" type="info" @click="$emit('close')" plain>
              No
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
import axios from "axios";

export default {
  components: {
    FullCalendar,
    Link,
  },
  props: ["reservationInformation"],
  data() {
    return {
      dateSchedule: "",
      employeeName: "",
      employeeEmail: "",
      department: "",
      purpose: "",
      conferenceRoom: "",
      timeSlot: "",
      equipment: "",
      arrangement: "",

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

      listConferenceRoom: [
        "AT HOME Conference Room",
        "BARBIZON Conference Room",
        "MONALISA Conference Room",
        "SASSA Conference Room",
        "SWIMLAB Conference Room",
      ],
      listITEquipment: ["Projector", "Laptop & mouse", "Pointer"],

      listTimeSlot: [
        {
          value: "07:30 - 08:00",
          label: "07:30 AM - 08:00 AM",
        },
        {
          value: "08:00 - 08:30",
          label: "08:00 AM - 08:30 AM",
        },
        {
          value: "08:30 - 09:00",
          label: "08:30 AM - 09:00 AM",
        },
        {
          value: "09:00 - 09:30",
          label: "09:00 AM - 09:30 AM",
        },
        {
          value: "09:30 - 10:00",
          label: "09:30 AM - 10:00 AM",
        },
        {
          value: "10:00 - 10:30",
          label: "10:00 AM - 10:30 AM",
        },
        {
          value: "10:30 - 11:00",
          label: "10:30 AM - 11:00 AM",
        },
        {
          value: "11:00 - 11:30",
          label: "11:00 AM - 11:30 AM",
        },
        {
          value: "11:30 - 12:00",
          label: "11:30 AM - 12:00 PM",
        },
        {
          value: "13:00 - 13:30",
          label: "01:00 PM - 01:30 PM",
        },
        {
          value: "13:30 - 14:00",
          label: "01:30 PM - 02:00 PM",
        },
        {
          value: "14:00 - 14:30",
          label: "02:00 PM - 02:30 PM",
        },
        {
          value: "14:30 - 15:00",
          label: "02:30 PM - 03:00 PM",
        },
        {
          value: "15:00 - 15:30",
          label: "03:00 PM - 03:30 PM",
        },
        {
          value: "15:30 - 16:00",
          label: "03:30 PM - 04:00 PM",
        },
        {
          value: "16:00 - 16:30",
          label: "04:00 PM - 04:30 PM",
        },
        {
          value: "16:30 - 17:00",
          label: "04:30 PM - 05:00 PM",
        },
        {
          value: "17:00 - 17:30",
          label: "05:00 PM - 05:30 PM",
        },
        {
          value: "17:30 - 18:00",
          label: "05:30 PM - 06:00 PM",
        },
        {
          value: "18:00 - 18:30",
          label: "06:00 PM - 06:30 PM",
        },
        {
          value: "18:30 - 19:00",
          label: "06:30 PM - 07:00 PM",
        },
        {
          value: "19:00 - 19:30",
          label: "7:00 PM - 07:30 PM",
        },
      ],
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
    cancelReservation() {
      console.log("ID:", this.reservationInformation.id);

      axios
        .post("/cancelReservation", {
          id: this.reservationInformation.id,
        })
        .then((response) => {
          console.log("Success Response:", response.data);
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
    handleDateSelect(selectInfo) {
      console.log("Selected Date: ", selectInfo.startStr);
      const dateObj = new Date(selectInfo.startStr);
      const monthNames = [
        "JANUARY",
        "FEBRUARY",
        "MARCH",
        "APRIL",
        "MAY",
        "JUNE",
        "JULY",
        "AUGUST",
        "SEPTEMBER",
        "OCTOBER",
        "NOVEMBER",
        "DECEMBER",
      ];

      const month = monthNames[dateObj.getMonth()];
      const day = dateObj.getDate();
      const year = dateObj.getFullYear();

      this.datePick = month + " " + day + ", " + year;
      this.isOpen = !this.isOpen;
    },
    handleEventClick(clickInfo) {
      if (
        confirm(`Are you sure you want to delete the event '${clickInfo.event.title}'`)
      ) {
        clickInfo.event.remove();
      }
    },
  },
};
</script>
