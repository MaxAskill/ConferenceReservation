<template>
  <div class="px-6 py-4 pb-6 bg-white rounded-xl drop-shadow-lg">
    <div class="mb-1 flex justify-center items-center">
      <img class="h-6 mb-3 my-1" :src="imagePath" alt="Icon" />
      <label class="text-lg uppercase font-bold cr-style">&nbsp;Conference Room</label>
    </div>
    <FullCalendar ref="calendar" :options="calendarOptions" />
    <!-- <ReservationDetailsModal
      v-if="isView"
      :reservationInformation="reservationInformation"
      @close="isView = false"
    >
    </ReservationDetailsModal> -->
    <div class="text-red-600 text-sm pt-1">
      Note: Cancellation of reservation must occur at least one hour prior to the reserved
      time.
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

import ReservationDetailsModal from "./ReservationDetailsModal.vue";
import { requiredNumber } from "element-plus/es/components/table-v2/src/common";

import imgPathATHOME from "../Logos/ATHOME.png";
import imgPathBARBIZON from "../Logos/BARBIZON.png";
import imgPathMONALISA from "../Logos/MONALISA.png";
import imgPathSWIMLAB from "../Logos/SWIMLAB.png";
import imgPathSASSA from "../Logos/SASSA.png";

export default {
  components: {
    FullCalendar,
    Link,
    ReservationDetailsModal,
  },
  props: ["conference_room"],
  data() {
    return {
      isView: false,
      isOpen: false,
      calendarOptions: {
        height: 700,
        plugins: [dayGridPlugin, timeGridPlugin, listPlugin, interactionPlugin],
        headerToolbar: {
          right: "dayGridMonth,timeGridWeek,timeGridDay",
          left: "prev,next,today",
          center: "title",
        },
        initialView: "timeGridWeek",
        // initialEvents: INITIAL_EVENTS, // alternatively, use the `events` setting to fetch from a feed
        weekends: true,
        events: [],
        eventTimeFormat: {
          hour: "numeric",
          minute: "2-digit",
          meridiem: "short",
        },
        editable: false,
        // select: this.handleDateSelect,
        eventClick: this.handleEventClick,
        eventsSet: this.handleEvents,
        slotMinTime: "07:00:00",
        slotMaxTime: "20:00:00",
        allDaySlot: false,
        dayMaxEventRows: true, // for all non-TimeGrid views
        navLinks: true,
      },

      datePick: "",
      employeeName: "",
      department: "",
      purpose: "",
      timeSlot: "",

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

      meetings: [],
      tempTimeSlot: [],
      reservationInformation: {},
      imagePath: "",
    };
  },
  created() {
    setInterval(this.dateTime, 1000);
    window.addEventListener("resize", this.handleViewportResize);
  },
  mounted() {
    // this.fetchMeeting();
  },
  watch: {
    conference_room: "fetchMeeting",
  },
  computed: {},
  methods: {
    async fetchMeeting() {
      let temp = this.conference_room.replaceAll("Conference Room", "").trim();
      temp = temp.replaceAll(" ", "");
      this.imagePath = this.getImgPath(temp);

      return axios
        .get("/fetchMeetings", {
          params: {
            conference_room: this.conference_room,
          },
        })
        .then((response) => {
          this.meetings = response.data;
          let temp = [];
          this.meetings.forEach((row) => {
            temp.push({
              id: row.id,
              title: row.department,
              start: new Date(row.timeStart),
              end: new Date(row.timeEnd),
              backgroundColor: this.getBGColor(row.department),
              borderColor: this.getBGColor(row.department),
            });
          });

          this.calendarOptions.events = temp;
        })
        .catch((error) => {
          console.error(error);
        });
    },
    goToSelectedDate(selectedDate) {
      // Parse the selected date from the input field
      const parsedDate = new Date(selectedDate);

      if (selectedDate) {
        // Check if the parsed date is valid
        if (!isNaN(parsedDate.getTime())) {
          // Access the FullCalendar instance using $refs and change the view
          this.$refs.calendar.getApi().changeView("timeGridDay");

          // Use the gotoDate method to set the selected date
          this.$refs.calendar.getApi().gotoDate(parsedDate);
        }
      } else {
        const today = new Date();
        const year = today.getFullYear();
        const month = today.getMonth() + 1; // Months are zero-based, so add 1
        const day = today.getDate();

        // Format the date as a string in yyyy-mm-dd format
        const formattedDate = `${year}-${month
          .toString()
          .padStart(2, "0")}-${day.toString().padStart(2, "0")}`;

        this.$refs.calendar.getApi().changeView("timeGridWeek");
        this.$refs.calendar.getApi().gotoDate(formattedDate);
      }
    },
    getBGColor(dept) {
      let bgColor = "";
      switch (dept) {
        case "EPC Merchandising":
          bgColor = "#008000";
          break;
        case "EPC Sales":
          bgColor = "#DAA520";
          break;
        case "NBFI Merchandising":
          bgColor = "#8A2BE2";
          break;
        case "NBFI Sales":
          bgColor = "#DC143C";
          break;
        case "Finance":
          bgColor = "#A0522D";
          break;
        case "Human Resource":
          bgColor = "#FF5722";
          break;
        case "Marketing":
          bgColor = "#2F4F4F";
          break;
        case "MIS":
          bgColor = "#ff70a6"; //#fb6f92
          break;
        case "Operations":
          bgColor = "#4169E1";
          break;
      }
      return bgColor;
    },
    getImgPath(x) {
      switch (x) {
        case "ATHOME":
          return imgPathATHOME;
        case "BARBIZON":
          return imgPathBARBIZON;
        case "SWIMLAB":
          return imgPathSWIMLAB;
        case "SASSA":
          return imgPathSASSA;
        case "MONALISA":
          return imgPathMONALISA;
      }
    },
    handleDateSelect(selectInfo) {
      // this.isOpen = !this.isOpen;
    },
    handleEventClick(clickInfo) {
      let tempEvents = this.meetings.map((e) => {
        if (e.id === Number(clickInfo.event.id))
          return {
            id: e.id,
            title: e.department,
            start: new Date(e.timeStart),
            end: new Date(e.timeEnd),
            backgroundColor: "#9ea39e",
            borderColor: "#8a8888",
          };
        else
          return {
            id: e.id,
            title: e.department,
            start: new Date(e.timeStart),
            end: new Date(e.timeEnd),
            backgroundColor: this.getBGColor(e.department),
            borderColor: this.getBGColor(e.department),
          };
      });
      this.calendarOptions.events = tempEvents;

      this.reservationInformation = [];
      this.isView = true;

      let result = this.meetings.find((item) => item.id === Number(clickInfo.event.id));

      this.reservationInformation = { ...result };

      this.reservationInformation.equipment = this.reservationInformation.equipment.split(
        ", "
      );
      this.reservationInformation.startTime = this.convertTo12HourFormat(
        this.reservationInformation.startTime
      );
      this.reservationInformation.endTime = this.convertTo12HourFormat(
        this.reservationInformation.endTime
      );

      this.$emit("transferReservationInformation", this.reservationInformation);
    },
    convertTo12HourFormat(time24Hour) {
      // Split the time into hours and minutes
      const [hours, minutes] = time24Hour.split(":").map(Number);

      // Determine whether it's AM or PM
      const period = hours < 12 ? "AM" : "PM";

      // Convert hours to 12-hour format
      const hours12 = hours % 12 || 12;

      // Format the time in "hh:mm AM/PM" format
      return `${hours12}:${minutes.toString().padStart(2, "0")} ${period}`;
    },
    handleEvents(events) {
      this.currentEvents = events;
    },
  },
};
</script>
<style>
.cr-style {
  letter-spacing: 0.05em;
  font-family: verdana;
  color: rgb(0, 161, 176);
  text-shadow: 1.8px 1.8px 2.6px rgba(224, 11, 132, 0.4);
  /* -webkit-text-stroke: 0.4px rgba(0, 161, 176, 0.7); */
}
</style>
