<script>
import { ref, onMounted } from "vue";
import ApplicationLogo from "@/Components/ApplicationLogo.vue";
import Dropdown from "@/Components/Dropdown.vue";
import DropdownLink from "@/Components/DropdownLink.vue";
import NavLink from "@/Components/NavLink.vue";
import ResponsiveNavLink from "@/Components/ResponsiveNavLink.vue";
import { Link } from "@inertiajs/vue3";
import { ArrowDown } from "@element-plus/icons-vue";

export default {
  components: {
    Link,
  },
  data() {
    return {
      activePage: [],
    };
  },
  mounted() {
    this.link();
  },
  methods: {
    link() {
      const url = window.location.href.split("/");
      // const activePage = [];
      switch (url[url.length - 1]) {
        case "reservation-monitoring":
          this.activePage[1] = true;
          break;
        case "log-monitoring":
          this.activePage[2] = true;
          break;
        case "user-monitoring":
          this.activePage[3] = true;
          break;
        default:
          this.activePage[0] = true;
      }
    },
    clearForm() {
      let reservationInformation = {
        dateSchedule: "",
        employeeName: "",
        employeeEmail: "", // Corrected the typo in 'employeeEmail'
        department: "",
        purpose: "",
        equipment: [],
        ctArrangement: "Yes",
        startTime: "",
        endTime: "",
      };
      this.$emit("clearForm", reservationInformation);
      // location.reload();
    },
  },
};
// const showingNavigationDropdown = ref(false);
// const status = ref(false);
// const activePage = ref([]);

// onMounted(() => {
//   const url = window.location.href.split("/");

//   switch (url[url.length - 1]) {
//     case "reservation-monitoring":
//       activePage.value[1] = true;
//       break;
//     case "log-monitoring":
//       activePage.value[2] = true;
//       break;
//     case "user-monitoring":
//       activePage.value[3] = true;
//       break;
//     default:
//       activePage.value[0] = true;
//   }
// });

// const clearForm = function () {
//   console.log("Logout");
//   let reservationInformation = {
//     dateSchedule: "",
//     employeeName: "",
//     employeeEmail: "", // Corrected the typo in 'employeeEmail'
//     department: "",
//     purpose: "",
//     equipment: [],
//     ctArrangement: "Yes",
//     startTime: "",
//     endTime: "",
//   };

//   // this.emit("clearForm", reservationInformation);
// };
</script>
<style scoped>
.example-showcase .el-dropdown + .el-dropdown {
  margin-left: 15px;
}
.example-showcase .el-dropdown-link {
  cursor: pointer;
  color: var(--el-color-primary);
  display: flex;
  align-items: center;
}
</style>

<template>
  <div class="relative top-0 min-h-screen overflow-y-auto bg-[#f4f3ef]">
    <div class="px-3 border-b border-gray-300 shadow-sm">
      <div class="grid grid-cols-3 px-2">
        <div class="flex flex-wrap justify-start content-center"></div>
        <a href="http://192.168.0.7:89/">
          <div class="flex items-center justify-center">
            <img class="h-8 w-8 my-2.5" src="../Logos/BElogo.png" alt="Icon" />
            <div class="font-black uppercase text-lg subpixel-antialiased c-text-shadow">
              &nbsp;CONFERENCE ROOM RESERVATION
            </div>
          </div>
        </a>
        <div
          v-if="$page.props.auth.user"
          class="flex flex-wrap justify-end content-center"
        >
          <el-dropdown>
            <el-link class="text-lg">Room Reservation</el-link>
            <template #dropdown>
              <el-dropdown-menu>
                <el-dropdown-item :disabled="activePage[0]" divided>
                  <el-link :disabled="activePage[0]" href="http://192.168.0.7:89/">
                    Reservation Form and Calendar
                  </el-link>
                </el-dropdown-item>
                <el-dropdown-item :disabled="activePage[1]" divided>
                  <el-link
                    :disabled="activePage[1]"
                    href="http://192.168.0.7:89/reservation-monitoring"
                  >
                    Reservation Monitoring
                  </el-link>
                </el-dropdown-item>
                <el-dropdown-item
                  v-if="$page.props.auth.user.position == 'Admin'"
                  :disabled="activePage[2]"
                  divided
                >
                  <el-link
                    :disabled="activePage[2]"
                    href="http://192.168.0.7:89/log-monitoring"
                  >
                    Log Monitoring
                  </el-link>
                </el-dropdown-item>
                <el-dropdown-item
                  v-if="$page.props.auth.user.position == 'Admin'"
                  :disabled="activePage[3]"
                  divided
                >
                  <el-link
                    :disabled="activePage[3]"
                    href="http://192.168.0.7:89/user-monitoring"
                  >
                    User Monitoring
                  </el-link>
                </el-dropdown-item>

                <el-dropdown-item :disabled="activePage[4]" divided>
                  <el-link
                    :disabled="activePage[4]"
                    href="https://calendar.google.com/calendar/u/0?cid=Y183MWFhNjE1NWE1NGRmYzQyODljNTNmZjdiMmY2OTBiMWQ3MGU4NDQ5YjdkODgzYzJhMjhjNWM0ODBlMTFlZTlhQGdyb3VwLmNhbGVuZGFyLmdvb2dsZS5jb20"
                    target="_blank"
                    type="primary"
                  >
                    Go to Google Calendar
                  </el-link>
                </el-dropdown-item>
              </el-dropdown-menu>
            </template>
          </el-dropdown>
          <div class="px-3"></div>
          <el-dropdown v-if="$page.props.auth.user">
            <el-button circle>
              <el-icon><Setting /></el-icon>
            </el-button>
            <template #dropdown>
              <el-dropdown-menu>
                <el-dropdown-item>
                  <Link
                    @click="clearForm"
                    :href="route('logout')"
                    method="post"
                    as="button"
                  >
                    <div class="flex gap-1.5">
                      <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="w-5"
                        viewBox="0 0 512 512"
                      >
                        <path
                          d="M304 336v40a40 40 0 01-40 40H104a40 40 0 01-40-40V136a40 40 0 0140-40h152c22.09 0 48 17.91 48 40v40M368 336l80-80-80-80M176 256h256"
                          fill="none"
                          stroke="currentColor"
                          stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="32"
                        />
                      </svg>
                      Log out
                    </div>
                  </Link></el-dropdown-item
                >
              </el-dropdown-menu>
            </template>
          </el-dropdown>
        </div>
        <div v-else class="flex flex-wrap justify-end content-center gap-4">
          <el-tooltip
            effect="dark"
            content="View the reservations for the conference rooms on the Google Calendar."
            placement="bottom"
          >
            <el-link
              href="https://calendar.google.com/calendar/u/0?cid=Y183MWFhNjE1NWE1NGRmYzQyODljNTNmZjdiMmY2OTBiMWQ3MGU4NDQ5YjdkODgzYzJhMjhjNWM0ODBlMTFlZTlhQGdyb3VwLmNhbGVuZGFyLmdvb2dsZS5jb20"
              target="_blank"
              type="primary"
            >
              Go to Google Calendar
            </el-link>
          </el-tooltip>
          <el-link
            v-if="!$page.props.auth.user"
            type="primary"
            href="http://192.168.0.7:89/login"
          >
            <el-button type="success"> Login </el-button>
          </el-link>
        </div>
      </div>
    </div>
    <main>
      <slot />
    </main>
    <div class="absolute bottom-0 left-2 w-fit">
      <p class="text-xs text-slate-500">v1.0.0.2311Y</p>
    </div>
  </div>
</template>

<style>
.c-text-shadow {
  letter-spacing: 0.05em;
  font-family: verdana;
  color: rgb(224, 11, 132);
  text-shadow: 2px 2px 2.5px rgba(0, 161, 176, 0.7);
  -webkit-text-stroke: 0.5px rgb(0, 161, 176);
}
</style>
