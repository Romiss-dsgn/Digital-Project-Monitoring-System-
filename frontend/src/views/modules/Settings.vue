<template>
  <div class="settings-page">
    <div class="settings-grid">
      <section class="settings-panel account-panel">
        <div class="panel-heading">
          <p>Account Settings</p>
          <h2>{{ form.name || "User Profile" }}</h2>
          <span>Manage the signed-in ConTrackPro personnel account.</span>
        </div>

        <div v-if="isLoading" class="settings-state">Loading profile...</div>
        <div v-else-if="loadError" class="settings-state settings-state--error">{{ loadError }}</div>

        <form v-else class="settings-form" @submit.prevent="saveProfile">
          <div class="field-grid">
            <label>
              <span>Full Name</span>
              <input v-model.trim="form.name" type="text" autocomplete="name" />
              <small v-if="errors.name">{{ errors.name }}</small>
            </label>

            <label>
              <span>Email Address</span>
              <input v-model.trim="form.email" type="email" autocomplete="email" />
              <small v-if="errors.email">{{ errors.email }}</small>
            </label>

            <label>
              <span>Badge Number</span>
              <input v-model.trim="form.badge_number" type="text" />
              <small v-if="errors.badge_number">{{ errors.badge_number }}</small>
            </label>

            <label>
              <span>Contact Number</span>
              <input v-model.trim="form.contact_number" type="text" autocomplete="tel" />
              <small v-if="errors.contact_number">{{ errors.contact_number }}</small>
            </label>

            <label>
              <span>Position</span>
              <input v-model.trim="form.position" type="text" />
              <small v-if="errors.position">{{ errors.position }}</small>
            </label>

            <label>
              <span>Office / Unit</span>
              <input v-model.trim="form.office_unit" type="text" />
              <small v-if="errors.office_unit">{{ errors.office_unit }}</small>
            </label>
          </div>

          <div class="password-box">
            <div>
              <strong>Password Update</strong>
              <span>Leave both password fields empty to keep the current password.</span>
            </div>
            <div class="field-grid">
              <label>
                <span>New Password</span>
                <input v-model="form.password" type="password" autocomplete="new-password" />
                <small v-if="errors.password">{{ errors.password }}</small>
              </label>

              <label>
                <span>Confirm New Password</span>
                <input v-model="form.password_confirmation" type="password" autocomplete="new-password" />
              </label>
            </div>
          </div>

          <div class="form-actions">
            <p v-if="statusMessage" :class="{ 'is-error': statusType === 'error' }">{{ statusMessage }}</p>
            <button type="submit" :disabled="isSaving">
              <span class="material-symbols-rounded">save</span>
              {{ isSaving ? "Saving..." : "Save Changes" }}
            </button>
          </div>
        </form>
      </section>

      <aside class="settings-panel profile-summary">
        <div class="profile-avatar">
          <span>{{ initials }}</span>
        </div>
        <h3>{{ form.name || "Admin User" }}</h3>
        <p>{{ form.position || "System Administrator" }}</p>
        <dl>
          <div>
            <dt>Email</dt>
            <dd>{{ form.email || "admin@contrackpro.test" }}</dd>
          </div>
          <div>
            <dt>Badge</dt>
            <dd>{{ form.badge_number || "Not set" }}</dd>
          </div>
          <div>
            <dt>Unit</dt>
            <dd>{{ form.office_unit || "BFP Region II" }}</dd>
          </div>
        </dl>
      </aside>
    </div>
  </div>
</template>

<script>
const EMPTY_FORM = {
  id: null,
  name: "",
  email: "",
  badge_number: "",
  contact_number: "",
  position: "",
  office_unit: "",
  password: "",
  password_confirmation: "",
};

export default {
  name: "Settings",
  data() {
    return {
      form: { ...EMPTY_FORM },
      errors: {},
      isLoading: true,
      isSaving: false,
      loadError: "",
      statusMessage: "",
      statusType: "success",
    };
  },
  computed: {
    initials() {
      return (this.form.name || "Admin User")
        .split(" ")
        .filter(Boolean)
        .slice(0, 2)
        .map((part) => part[0])
        .join("")
        .toUpperCase();
    },
  },
  async mounted() {
    await this.loadProfile();
  },
  methods: {
    async loadProfile() {
      this.isLoading = true;
      this.loadError = "";

      try {
        await this.$store.dispatch("profile/getProfile");
        const profile = this.$store.getters["profile/getUserProfile"] || {};
        this.form = {
          ...EMPTY_FORM,
          id: profile.id,
          name: profile.name || "",
          email: profile.email || "",
          badge_number: profile.badge_number || "",
          contact_number: profile.contact_number || "",
          position: profile.position || "",
          office_unit: profile.office_unit || "",
        };
      } catch (error) {
        this.loadError = "Unable to load the signed-in profile. Please login again.";
      } finally {
        this.isLoading = false;
      }
    },
    async saveProfile() {
      this.isSaving = true;
      this.errors = {};
      this.statusMessage = "";

      const payload = { ...this.form };

      if (!payload.password && !payload.password_confirmation) {
        delete payload.password;
        delete payload.password_confirmation;
      }

      try {
        await this.$store.dispatch("profile/editProfile", payload);
        const profile = this.$store.getters["profile/getUserProfile"] || {};
        this.form = {
          ...this.form,
          ...profile,
          password: "",
          password_confirmation: "",
        };
        this.statusType = "success";
        this.statusMessage = "Profile settings saved successfully.";
      } catch (error) {
        this.statusType = "error";
        this.statusMessage = "Unable to save profile settings.";
        this.errors = this.parseErrors(error);
      } finally {
        this.isSaving = false;
      }
    },
    parseErrors(error) {
      const apiErrors = error?.response?.data?.errors;

      if (!Array.isArray(apiErrors)) {
        return {};
      }

      return apiErrors.reduce((fields, item) => {
        const field = item?.source?.pointer?.split("/").pop();

        if (field) {
          fields[field] = item.detail;
        }

        return fields;
      }, {});
    },
  },
};
</script>

<style scoped>
.settings-page {
  min-height: calc(100vh - 80px);
  padding: 2rem;
  background: #f4f7fb;
}

.settings-grid {
  display: grid;
  grid-template-columns: minmax(0, 1fr) 320px;
  gap: 1.5rem;
}

.settings-panel {
  background: #ffffff;
  border: 1px solid #e6edf6;
  border-radius: 8px;
  box-shadow: 0 18px 40px rgba(15, 23, 42, 0.06);
}

.account-panel {
  padding: 1.5rem;
}

.panel-heading {
  margin-bottom: 1.5rem;
}

.panel-heading p {
  margin: 0 0 0.35rem;
  color: #850000;
  font-size: 0.72rem;
  font-weight: 800;
  letter-spacing: 0.08em;
  text-transform: uppercase;
}

.panel-heading h2 {
  margin: 0;
  color: #111827;
  font-size: 1.45rem;
  font-weight: 850;
}

.panel-heading span {
  display: block;
  margin-top: 0.35rem;
  color: #6b7280;
  font-size: 0.9rem;
}

.settings-state {
  padding: 2.5rem;
  color: #6b7280;
  text-align: center;
}

.settings-state--error {
  color: #b91c1c;
}

.field-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 1rem;
}

.settings-form label {
  display: flex;
  flex-direction: column;
  gap: 0.4rem;
  margin: 0;
}

.settings-form label span {
  color: #374151;
  font-size: 0.78rem;
  font-weight: 800;
}

.settings-form input {
  width: 100%;
  min-height: 44px;
  border: 1px solid #d8e0eb;
  border-radius: 8px;
  padding: 0 0.85rem;
  color: #111827;
  outline: 0;
  transition: border-color 0.16s ease, box-shadow 0.16s ease;
}

.settings-form input:focus {
  border-color: #850000;
  box-shadow: 0 0 0 3px rgba(133, 0, 0, 0.12);
}

.settings-form small {
  color: #b91c1c;
  font-size: 0.72rem;
}

.password-box {
  margin-top: 1.5rem;
  padding: 1rem;
  border-radius: 8px;
  background: #f8fafc;
  border: 1px solid #e5eaf2;
}

.password-box > div:first-child {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
  margin-bottom: 1rem;
}

.password-box strong {
  color: #111827;
  font-size: 0.92rem;
}

.password-box span {
  color: #6b7280;
  font-size: 0.82rem;
}

.form-actions {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  margin-top: 1.5rem;
}

.form-actions p {
  margin: 0;
  color: #047857;
  font-size: 0.86rem;
  font-weight: 700;
}

.form-actions p.is-error {
  color: #b91c1c;
}

.form-actions button {
  display: inline-flex;
  align-items: center;
  gap: 0.45rem;
  min-height: 44px;
  border: 0;
  border-radius: 8px;
  padding: 0 1.15rem;
  background: linear-gradient(90deg, #b9403b 0%, #2f59a4 100%);
  color: #ffffff;
  font-weight: 800;
  letter-spacing: 0.03em;
  text-transform: uppercase;
  box-shadow: 0 10px 22px rgba(80, 40, 80, 0.16);
}

.form-actions button:disabled {
  cursor: wait;
  opacity: 0.7;
}

.profile-summary {
  align-self: start;
  padding: 1.5rem;
  text-align: center;
}

.profile-avatar {
  display: grid;
  place-items: center;
  width: 88px;
  height: 88px;
  margin: 0 auto 1rem;
  border-radius: 50%;
  background: #850000;
  color: #ffffff;
  box-shadow: 0 10px 24px rgba(133, 0, 0, 0.22);
}

.profile-avatar span {
  font-size: 1.6rem;
  font-weight: 900;
}

.profile-summary h3 {
  margin: 0;
  color: #111827;
  font-size: 1.1rem;
  font-weight: 850;
}

.profile-summary p {
  margin: 0.35rem 0 1.25rem;
  color: #6b7280;
  font-size: 0.86rem;
}

.profile-summary dl {
  display: grid;
  gap: 0.8rem;
  margin: 0;
  text-align: left;
}

.profile-summary dl div {
  padding: 0.8rem;
  border-radius: 8px;
  background: #f8fafc;
}

.profile-summary dt {
  color: #850000;
  font-size: 0.68rem;
  font-weight: 900;
  letter-spacing: 0.07em;
  text-transform: uppercase;
}

.profile-summary dd {
  margin: 0.25rem 0 0;
  color: #111827;
  font-size: 0.83rem;
  overflow-wrap: anywhere;
}

@media (max-width: 991.98px) {
  .settings-grid {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 767.98px) {
  .settings-page {
    padding: 1rem;
  }

  .field-grid {
    grid-template-columns: 1fr;
  }

  .form-actions {
    align-items: stretch;
    flex-direction: column;
  }
}
</style>
