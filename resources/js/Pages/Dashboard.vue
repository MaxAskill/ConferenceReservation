<script>
import DashboardLayout from "@/Layouts/DashboardLayout.vue";
import Form from "@/Pages/Form.vue";
import SchedulesCalendar from "@/Pages/SchedulesCalendar.vue";
import { Head } from "@inertiajs/vue3";

// const goToDate = (date) => {
//   console.log("Dashboard Selected Date: ", date);
//   // schedulesCalendar.value.goToSelectedDate(date);
// };

export default {
  components: {
    DashboardLayout,
    Form,
    SchedulesCalendar,
  },
  data() {
    return {
      conference_room: "AT HOME Conference Room",
      reservationInformation: {},
      resetData: {},
      temp: {
        event: {
          id: null,
        },
      },
    };
  },
  async mounted() {
    this.temp.event.id = sessionStorage.getItem("eventID");
    if (sessionStorage.getItem("conferenceRoom"))
      this.conference_room = sessionStorage.getItem("conferenceRoom");
    await this.fetchMeeting();
    if (this.temp.event.id) {
      this.$refs.schedulesCalendar.handleEventClick(this.temp);
      sessionStorage.removeItem("eventID");
      sessionStorage.removeItem("conferenceRoom");
    }
  },
  methods: {
    async fetchMeeting() {
      await this.$refs.schedulesCalendar.fetchMeeting(this.conference_room);
    },
    goToDate(date) {
      this.$refs.schedulesCalendar.goToSelectedDate(date);
    },
    transferConfRoom(data) {
      this.conference_room = data;
      // this.$refs.schedulesCalendar.fetchMeeting();
    },
    transferReservationInformation(data) {
      this.reservationInformation = data;
    },
    clearForm(data) {
      this.resetData = data;
    },
  },
};
</script>

<template>
  <Head title="Home" />

  <DashboardLayout @clearForm="clearForm">
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Conference Room Reservation
      </h2>
    </template>

    <div class="grid grid-cols-6 gap-2 px-8 py-4">
      <Form
        @goToDate="goToDate"
        @transferConfRoom="transferConfRoom"
        @fetchMeeting="fetchMeeting"
        :clearingForm="resetData"
        :transferReservationInformation="reservationInformation"
        class="col-span-2"
      ></Form>
      <SchedulesCalendar
        ref="schedulesCalendar"
        :conference_room="conference_room"
        @transferReservationInformation="transferReservationInformation"
        class="col-span-4"
      ></SchedulesCalendar>
    </div>
  </DashboardLayout>
</template>
