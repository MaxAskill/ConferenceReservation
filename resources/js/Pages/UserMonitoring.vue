<script setup>
import DashboardLayout from "@/Layouts/DashboardLayout.vue";
import { Head } from "@inertiajs/vue3";
</script>

<template>
  <Head title="User Monitoring" />

  <DashboardLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">User Monitoring</h2>
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
      <!-- {{ queriedTableData }} -->
      <el-table :data="queriedTableData" style="width: 100%">
        <!-- <el-table-column width="50">
          <template #default="scope">
            <span>{{
              (pagination.currentPage - 1) * pagination.perPage + scope.$index + 1
            }}</span>
          </template>
        </el-table-column> -->
        <el-table-column type="index" :index="indexMethod" />
        <el-table-column prop="name" label="Name" />
        <el-table-column prop="email" label="Email" />
        <el-table-column prop="department" label="Department" />
        <el-table-column prop="position" label="Position" />
        <el-table-column prop="status" label="Status" />

        <el-table-column fixed="right" label="Operations" width="120">
          <template #default="scope">
            <el-button type="warning" size="small" circle @click="editUser(scope.row)">
              <el-icon><Edit /> </el-icon
            ></el-button>
            <el-button
              v-if="scope.row.status === 'Active'"
              type="danger"
              size="small"
              circle
              @click="deactivateUser(scope.row)"
            >
              <el-icon><CircleClose /></el-icon>
            </el-button>
            <el-button
              v-else
              type="success"
              size="small"
              circle
              @click="activateUser(scope.row)"
            >
              <el-icon><SwitchButton /></el-icon>
            </el-button>
          </template>
        </el-table-column>
      </el-table>
    </div>
  </DashboardLayout>
  <EditUserModal
    v-if="isEdit"
    :userInformation="userInformations"
    @close="isEdit = false"
  >
  </EditUserModal>
  <ConfirmCancellation
    v-if="isCancel"
    :userInformation="userInformation"
    @close="isCancel = false"
  >
  </ConfirmCancellation>
</template>

<script>
import EditUserModal from "./EditUserModal.vue";
export default {
  components: {
    EditUserModal,
  },
  data() {
    return {
      isEdit: false,
      isCancel: false,
      userInformation: [],
      userInformations: {
        name: "",
        email: "",
        department: "",
        position: "",
        status: "",
      },
      tableData: [],
      tempTableData: [],
      searchInput: "",
      propsToSearch: ["name", "email", "department", "position", "status"],
      pagination: {
        perPage: 10,
        currentPage: 1,
        perPageOptions: [10, 20, 50, 100],
        total: 0,
      },
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
    this.fetchUsers();
  },
  watch: {},
  methods: {
    editUser(userDetails) {
      console.log("Details", userDetails);
      this.userInformations.name = userDetails.name;
      this.userInformations.email = userDetails.email;
      this.userInformations.department = userDetails.department;
      this.userInformations.position = userDetails.position;
      this.userInformations.status = userDetails.status;
      this.userInformation = userDetails;
      console.log("Detailed", this.userInformation);
      this.isEdit = true;
    },
    deactivateUser(userDetails) {
      console.log("Details", userDetails);
      // this.userInformation = userDetails;
      // console.log("Detailed", this.userInformation);
      // this.isCancel = true;

      axios
        .post("/updateUserStatus", {
          email: userDetails.email,
          status: "Inactive",
        })
        .then((response) => {
          console.log(response.data);
          window.location.reload();
        })
        .catch((error) => {
          console.error(error);
        });
    },
    activateUser(userDetails) {
      console.log("User Details:", userDetails);
      axios
        .post("/updateUserStatus", {
          email: userDetails.email,
          status: "Active",
        })
        .then((response) => {
          console.log(response.data);
          window.location.reload();
        })
        .catch((error) => {
          console.error(error);
        });
    },
    fetchUsers() {
      axios
        .get("/getUsers")
        .then((response) => {
          console.log("Response: ", response.data);

          response.data.forEach((data) => {
            this.tableData.push({
              name: data.name,
              status: data.status,
              department: data.department,
              position: data.position,
              email: data.email,
            });
          });
          // response.data.timeSlor;
          // this.tableData = response.data;

          // this.tableData = response.data.map((item, index) => {
          //   return { index: index + 1, ...item };
          // });
        })
        .catch((error) => {
          console.error(error);
        });
    },
  },
};
</script>
