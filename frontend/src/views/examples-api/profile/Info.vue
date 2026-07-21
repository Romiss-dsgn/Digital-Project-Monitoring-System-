<template>
  <div class="info-section">

    <!-- Personal Information -->
    <div class="info-block">
      <div class="info-block__header">
        <span class="material-symbols-rounded">person</span>
        <h6>Personal Information</h6>
        <button class="edit-btn" type="button" @click="toggleEdit" v-if="!isEditing">
          <span class="material-symbols-rounded">edit</span> Edit
        </button>
        <div class="edit-actions" v-else>
          <button class="cancel-btn" type="button" @click="cancelEdit">
            <span class="material-symbols-rounded">close</span> Cancel
          </button>
          <button class="save-btn" type="button" @click="handleSubmit">
            <span class="material-symbols-rounded">save</span> Save Changes
          </button>
        </div>
      </div>

      <div class="info-grid">
        <!-- Surname -->
        <div class="info-field">
          <label class="info-field__label">Surname</label>
          <div class="info-field__input-wrap" :class="{ 'is-readonly': !isEditing }">
            <span class="material-symbols-rounded info-field__icon">badge</span>
            <input type="text" class="info-field__input" placeholder="Enter surname"
              v-model="user.surname" :disabled="!isEditing" />
          </div>
          <validation-error :errors="apiValidationErrors.surname" />
        </div>

        <!-- First Name -->
        <div class="info-field">
          <label class="info-field__label">First Name</label>
          <div class="info-field__input-wrap" :class="{ 'is-readonly': !isEditing }">
            <span class="material-symbols-rounded info-field__icon">person</span>
            <input type="text" class="info-field__input" placeholder="Enter first name"
              v-model="user.first_name" :disabled="!isEditing" />
          </div>
          <validation-error :errors="apiValidationErrors.first_name" />
        </div>

        <!-- Middle Initial -->
        <div class="info-field">
          <label class="info-field__label">Middle Initial</label>
          <div class="info-field__input-wrap" :class="{ 'is-readonly': !isEditing }">
            <span class="material-symbols-rounded info-field__icon">sort_by_alpha</span>
            <input type="text" class="info-field__input" placeholder="e.g. A."
              v-model="user.middle_initial" :disabled="!isEditing" maxlength="3" />
          </div>
        </div>

        <!-- Email -->
        <div class="info-field">
          <label class="info-field__label">Email Address</label>
          <div class="info-field__input-wrap" :class="{ 'is-readonly': !isEditing }">
            <span class="material-symbols-rounded info-field__icon">mail</span>
            <input type="email" class="info-field__input" placeholder="Enter email"
              v-model="user.email" :disabled="!isEditing" />
          </div>
          <validation-error :errors="apiValidationErrors.email" />
        </div>

        <!-- Rank (read-only, badge hidden) -->
        <div class="info-field">
          <label class="info-field__label">Rank</label>
          <div class="info-field__input-wrap is-readonly">
            <span class="material-symbols-rounded info-field__icon">military_tech</span>
            <input type="text" class="info-field__input" :value="user.rank || '—'" disabled />
          </div>
        </div>

        <!-- Position with searchable dropdown -->
        <div class="info-field" style="position: relative;">
          <label class="info-field__label">Position / Designation</label>
          <div class="info-field__input-wrap" :class="{ 'is-readonly': !isEditing }">
            <span class="material-symbols-rounded info-field__icon">work</span>
            <input type="text" class="info-field__input" placeholder="Enter position"
              v-model="positionSearch"
              :disabled="!isEditing"
              @focus="showPositionDropdown = true"
              @blur="hidePositionDropdown" />
            <span class="material-symbols-rounded" style="font-size:1rem; color:#aab0bb;">
              {{ showPositionDropdown ? 'expand_less' : 'expand_more' }}
            </span>
          </div>
          <div class="position-dropdown" v-if="showPositionDropdown && isEditing">
            <div v-for="pos in filteredPositions" :key="pos"
              class="position-dropdown__item"
              @mousedown.prevent="selectPosition(pos)">
              {{ pos }}
            </div>
            <div class="position-dropdown__empty" v-if="filteredPositions.length === 0">
              No match found
            </div>
          </div>
        </div>

        <!-- Date of Birth -->
        <div class="info-field">
          <label class="info-field__label">Date of Birth</label>
          <div class="info-field__input-wrap" :class="{ 'is-readonly': !isEditing }">
            <span class="material-symbols-rounded info-field__icon">cake</span>
            <input type="date" class="info-field__input"
              v-model="user.date_of_birth" :disabled="!isEditing" />
          </div>
        </div>

        <!-- Sex/Gender -->
        <div class="info-field">
          <label class="info-field__label">Sex / Gender</label>
          <div class="info-field__input-wrap" :class="{ 'is-readonly': !isEditing }">
            <span class="material-symbols-rounded info-field__icon">wc</span>
            <select class="info-field__input" v-model="user.gender" :disabled="!isEditing">
              <option value="">Select gender</option>
              <option value="Male">Male</option>
              <option value="Female">Female</option>
            </select>
          </div>
        </div>

        <!-- Contact Number -->
        <div class="info-field">
          <label class="info-field__label">Contact Number</label>
          <div class="info-field__input-wrap" :class="{ 'is-readonly': !isEditing }">
            <span class="material-symbols-rounded info-field__icon">phone</span>
            <input type="tel" class="info-field__input" placeholder="9XXXXXXXXX (10 digits, no leading 0)"
              :value="user.contact_number" @input="handleContactNumberInput" maxlength="10" :disabled="!isEditing" />
          </div>
          <validation-error :errors="apiValidationErrors.contact_number" />
        </div>

        <!-- Username -->
        <div class="info-field">
          <label class="info-field__label">Username</label>
          <div class="info-field__input-wrap" :class="{ 'is-readonly': !isEditing }">
            <span class="material-symbols-rounded info-field__icon">alternate_email</span>
            <input type="text" class="info-field__input" placeholder="Enter username"
              v-model="user.username" :disabled="!isEditing" />
          </div>
        </div>
      </div>
    </div>

    <!-- Assignment / Unit -->
    <div class="info-block mt-4">
      <div class="info-block__header">
        <span class="material-symbols-rounded">apartment</span>
        <h6>Assignment / Unit</h6>
      </div>

      <div class="info-grid">
        <!-- Region (read-only, badge hidden) -->
        <div class="info-field">
          <label class="info-field__label">Region</label>
          <div class="info-field__input-wrap is-readonly">
            <span class="material-symbols-rounded info-field__icon">map</span>
            <input type="text" class="info-field__input" value="Region Office II" disabled />
          </div>
        </div>

        <!-- Province (read-only, badge hidden, default Cagayan) -->
        <div class="info-field">
          <label class="info-field__label">Province</label>
          <div class="info-field__input-wrap is-readonly">
            <span class="material-symbols-rounded info-field__icon">location_city</span>
            <input type="text" class="info-field__input" value="Cagayan" disabled />
          </div>
        </div>

        <!-- City/Municipality -->
        <div class="info-field">
          <label class="info-field__label">City / Municipality</label>
          <div class="info-field__input-wrap" :class="{ 'is-readonly': !isEditing }">
            <span class="material-symbols-rounded info-field__icon">location_on</span>
            <input type="text" class="info-field__input" placeholder="Tuguegarao City"
              v-model="user.city" :disabled="!isEditing" />
          </div>
        </div>

        <!-- Fire Station (read-only, badge hidden) -->
        <div class="info-field">
          <label class="info-field__label">Fire Station / Office Unit</label>
          <div class="info-field__input-wrap is-readonly">
            <span class="material-symbols-rounded info-field__icon">local_fire_department</span>
            <input type="text" class="info-field__input" :value="user.office_unit || '—'" disabled />
          </div>
        </div>

        <!-- Role (read-only, badge hidden) -->
        <div class="info-field">
          <label class="info-field__label">System Role</label>
          <div class="info-field__input-wrap is-readonly">
            <span class="material-symbols-rounded info-field__icon">admin_panel_settings</span>
            <input type="text" class="info-field__input" :value="user.role || '—'" disabled />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import ValidationError from "@/components/ValidationError.vue";
import formMixin from "@/mixins/formMixin.js";
import showSwal from "@/mixins/showSwal.js";
import _ from "lodash";

const BFP_POSITIONS = [
  "Administrative Staff - Contract Documentation",
  "Records Management Personnel",
  "Contract Monitoring Personnel",
  "Engineer - Planning",
  "Engineer - Supervision",
  "Engineer - Monitoring",
  "System Administrator",
];

const PH_COUNTRY_CODE = "63";
const CONTACT_NUMBER_MAX_LENGTH = 10;

export default {
  name: "Info",
  components: { ValidationError },
  mixins: [formMixin],
  data() {
    return {
      user: {},
      userBackup: {},
      isEditing: false,
      isEditingPassword: false,
      showCurrent: false,
      showNew: false,
      positionSearch: "",
      showPositionDropdown: false,
      passwords: {
        current: "",
        new: "",
      },
      loading: false,
    };
  },
  computed: {
    filteredPositions() {
      const q = this.positionSearch.toLowerCase();
      return BFP_POSITIONS.filter(p => p.toLowerCase().includes(q));
    },
  },
  async mounted() {
    this.loading = true;
    try {
      await this.$store.dispatch("profile/getProfile");
      this.user = _.omit(this.$store.getters["profile/getUserProfile"], "links");
      this.user.contact_number = this.stripCountryCode(this.user.contact_number);
      this.positionSearch = this.user.position || "";
      this.userBackup = { ...this.user };
    } catch {
      showSwal.methods.showSwal({ type: "error", message: "Oops, something went wrong!", width: 500 });
    } finally {
      this.loading = false;
    }
  },
  methods: {
    stripCountryCode(value) {
      let digits = String(value || "").replace(/\D/g, "");
      if (digits.startsWith(PH_COUNTRY_CODE)) {
        digits = digits.slice(PH_COUNTRY_CODE.length);
      } else if (digits.startsWith("0")) {
        digits = digits.slice(1);
      }
      return digits.slice(0, CONTACT_NUMBER_MAX_LENGTH);
    },
    handleContactNumberInput(event) {
      const digitsOnly = event.target.value.replace(/\D/g, "").slice(0, CONTACT_NUMBER_MAX_LENGTH);
      this.user.contact_number = digitsOnly;
      event.target.value = digitsOnly;
    },
    toggleEdit() {
      this.userBackup = { ...this.user };
      this.positionSearch = this.user.position || "";
      this.isEditing = true;
    },
    cancelEdit() {
      this.user = { ...this.userBackup };
      this.positionSearch = this.user.position || "";
      this.isEditing = false;
      this.resetApiValidation();
    },
    selectPosition(pos) {
      this.positionSearch = pos;
      this.user.position = pos;
      this.showPositionDropdown = false;
    },
    hidePositionDropdown() {
      setTimeout(() => {
        this.showPositionDropdown = false;
        this.user.position = this.positionSearch;
      }, 150);
    },
    togglePasswordEdit() {
      this.isEditingPassword = true;
    },
    cancelPasswordEdit() {
      this.passwords = { current: "", new: "" };
      this.isEditingPassword = false;
    },
    async handleSubmit() {
      if (this.user.id <= 3 && (process.env.VUE_APP_IS_DEMO ?? 1) == 1) {
        showSwal.methods.showSwal({ type: "error", message: "You are not allowed to change data of default users.", width: 500 });
        return;
      }
      this.resetApiValidation();
      const localDigits = this.stripCountryCode(this.user.contact_number);
      if (localDigits && localDigits.length !== CONTACT_NUMBER_MAX_LENGTH) {
        this.apiValidationErrors = {
          contact_number: [`Contact number must be ${CONTACT_NUMBER_MAX_LENGTH} digits.`],
        };
        return;
      }
      const payload = {
        ...this.user,
        contact_number: localDigits ? `${PH_COUNTRY_CODE}${localDigits}` : "",
      };
      try {
        await this.$store.dispatch("profile/editProfile", payload);
        this.user = _.omit(this.$store.getters["profile/getUserProfile"], "links");
        this.user.contact_number = this.stripCountryCode(this.user.contact_number);
        this.userBackup = { ...this.user };
        this.positionSearch = this.user.position || "";
        this.isEditing = false;
        showSwal.methods.showSwal({ type: "success", message: "Profile updated successfully!", width: 500 });
      } catch (error) {
        this.setApiValidation(error.response.data.errors);
        showSwal.methods.showSwal({ type: "error", message: "Oops, something went wrong!", width: 500 });
      }
    },
    async handlePasswordSubmit() {
      if (!this.passwords.current || !this.passwords.new) {
        showSwal.methods.showSwal({ type: "error", message: "Please fill in all password fields.", width: 500 });
        return;
      }
      try {
        await this.$store.dispatch("profile/editPassword", {
          current_password: this.passwords.current,
          password: this.passwords.new,
          password_confirmation: this.passwords.new,
        });
        this.passwords = { current: "", new: "" };
        this.isEditingPassword = false;
        showSwal.methods.showSwal({ type: "success", message: "Password updated successfully!", width: 500 });
      } catch (error) {
        showSwal.methods.showSwal({ type: "error", message: "Incorrect current password.", width: 500 });
      }
    },
  },
};
</script>

<style scoped>
.info-block {
  background: #fff;
  border: 1px solid #eef0f4;
  border-radius: 14px;
  padding: 1.25rem 1.5rem;
}

.info-block__header {
  display: flex;
  align-items: center;
  gap: 0.6rem;
  margin-bottom: 1.25rem;
}

.info-block__header .material-symbols-rounded {
  color: #850000;
  font-size: 1.3rem;
}

.info-block__header h6 {
  font-size: 0.95rem;
  font-weight: 800;
  color: #1f140f;
  margin: 0;
  flex: 1;
}

.edit-btn {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  background: #f5e6e6;
  color: #850000;
  border: none;
  border-radius: 8px;
  padding: 0.4rem 0.9rem;
  font-size: 0.82rem;
  font-weight: 700;
  cursor: pointer;
  transition: background 0.2s;
}

.edit-btn:hover { background: #ead5d5; }
.edit-btn .material-symbols-rounded { font-size: 1rem; }

.edit-actions {
  display: flex;
  gap: 0.5rem;
  margin-left: auto;
}

.cancel-btn {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  background: #f1f2f5;
  color: #5a332d;
  border: none;
  border-radius: 8px;
  padding: 0.4rem 0.9rem;
  font-size: 0.82rem;
  font-weight: 700;
  cursor: pointer;
}

.save-btn {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  background: linear-gradient(135deg, #850000, #c0392b);
  color: #fff;
  border: none;
  border-radius: 8px;
  padding: 0.4rem 0.9rem;
  font-size: 0.82rem;
  font-weight: 700;
  cursor: pointer;
  transition: opacity 0.2s;
}

.save-btn:hover { opacity: 0.88; }

.save-btn .material-symbols-rounded,
.cancel-btn .material-symbols-rounded,
.edit-btn .material-symbols-rounded { font-size: 1rem; }

.info-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
}

@media (max-width: 640px) {
  .info-grid { grid-template-columns: 1fr; }
}

.info-field__label {
  display: flex;
  align-items: center;
  gap: 0.4rem;
  font-size: 0.72rem;
  font-weight: 700;
  color: #5a332d;
  letter-spacing: 0.05em;
  text-transform: uppercase;
  margin-bottom: 0.4rem;
}

.info-field__input-wrap {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  background: #f8f9fc;
  border: 1px solid #e2e6ea;
  border-radius: 10px;
  padding: 0.6rem 0.9rem;
  transition: border-color 0.2s, background 0.2s;
}

.info-field__input-wrap:focus-within {
  border-color: #850000;
  background: #fff;
}

.info-field__input-wrap.is-readonly {
  background: #f4f5f7;
  border-color: #e8eaed;
  opacity: 0.8;
}

.info-field__icon {
  font-size: 1.1rem;
  color: #850000;
  flex-shrink: 0;
}

.info-field__input {
  width: 100%;
  border: none;
  outline: none;
  background: transparent;
  font-size: 0.88rem;
  color: #1f140f;
}

.info-field__input::placeholder { color: #aab0bb; }
.info-field__input:disabled { cursor: default; color: #4a4a4a; }

select.info-field__input { cursor: pointer; }
select.info-field__input:disabled { cursor: default; }

.pw-toggle {
  background: transparent;
  border: none;
  cursor: pointer;
  color: #850000;
  display: flex;
  align-items: center;
  padding: 0;
}

.pw-toggle .material-symbols-rounded { font-size: 1.1rem; }

.position-dropdown {
  position: absolute;
  top: 100%;
  left: 0;
  right: 0;
  background: #fff;
  border: 1px solid #e2e6ea;
  border-radius: 10px;
  box-shadow: 0 8px 24px rgba(15,23,42,0.10);
  z-index: 100;
  max-height: 200px;
  overflow-y: auto;
  margin-top: 4px;
}

.position-dropdown__item {
  padding: 0.55rem 1rem;
  font-size: 0.88rem;
  color: #1f140f;
  cursor: pointer;
  transition: background 0.15s;
}

.position-dropdown__item:hover {
  background: #fdf0f0;
  color: #850000;
}

.position-dropdown__empty {
  padding: 0.55rem 1rem;
  font-size: 0.85rem;
  color: #aab0bb;
}
</style>