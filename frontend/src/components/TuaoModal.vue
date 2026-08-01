<template>
  <Teleport to="body">
    <Transition name="tuao-modal-fade">
      <div v-if="show" class="tuao-modal-overlay" @click.self="$emit('close')">
        <div class="tuao-modal" :style="{ width }" role="dialog" aria-modal="true">
          <div class="tuao-modal-header">
            <div class="tuao-modal-header-left">
              <div class="tuao-modal-emblem">
                <img :src="tuaoLogo" alt="LGU Tuao Logo" class="tuao-logo-img" />
              </div>
              <div>
                <p class="tuao-modal-agency">{{ agency }}</p>
                <h5 class="tuao-modal-title">{{ title }}</h5>
              </div>
            </div>
            <button class="tuao-modal-close" type="button" @click="$emit('close')" aria-label="Close modal">
              <i class="material-icons-round">close</i>
            </button>
          </div>

          <div class="tuao-modal-stripe">
            <span>{{ region }}</span>
            <span>{{ stripe }}</span>
          </div>

          <div class="tuao-modal-body">
            <slot />
          </div>

          <div v-if="showFooter" class="tuao-modal-footer">
            <div class="tuao-footer-note">
              <slot name="note">{{ note }}</slot>
            </div>
            <div class="tuao-footer-actions">
              <button v-if="showCancel" class="tuao-btn-cancel" type="button" :disabled="loading" @click="$emit('close')">{{ cancelText }}</button>
              <button class="tuao-btn-save" type="button" :disabled="loading" @click="$emit('confirm')">
                <i class="material-icons-round">{{ confirmIcon }}</i>
                {{ loading ? 'Saving...' : confirmText }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script>
import tuaoLogo from "@/assets/img/LGU TUAO logo.jpeg";

export default {
  name: "TuaoModal",
  props: {
    show: { type: Boolean, default: false },
    title: { type: String, required: true },
    stripe: { type: String, default: "OFFICIAL FORM" },
    agency: { type: String, default: "Municipality of Tuao" },
    region: { type: String, default: "MUNICIPALITY OF TUAO" },
    note: { type: String, default: "Fields marked * are required." },
    cancelText: { type: String, default: "Cancel" },
    confirmText: { type: String, default: "Save" },
    confirmIcon: { type: String, default: "save" },
    showFooter: { type: Boolean, default: true },
    showCancel: { type: Boolean, default: true },
    width: { type: String, default: "620px" },
    loading: { type: Boolean, default: false },
    confirmVariant: { type: String, default: "primary" }
  },
  emits: ["close", "confirm"],
  data() {
    return { tuaoLogo };
  }
};
</script>

<style>
.tuao-modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(10, 10, 20, 0.6);
  backdrop-filter: blur(3px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1055;
  padding: 1rem;
}

.tuao-modal {
  background: #ffffff;
  border-radius: 16px;
  max-width: 100%;
  max-height: 92vh;
  overflow-y: auto;
  overflow-x: hidden;
  box-shadow: 0 24px 60px rgba(0, 0, 0, 0.25);
  display: flex;
  flex-direction: column;
}

.tuao-modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 18px 22px 14px;
  background: #1a1a2e;
  border-radius: 16px 16px 0 0;
}

.tuao-modal-header-left {
  display: flex;
  align-items: center;
  gap: 14px;
  min-width: 0;
}

.tuao-modal-emblem {
  width: 52px;
  height: 52px;
  background: rgba(255, 255, 255, 0.08);
  border: 1.5px solid rgba(255, 255, 255, 0.15);
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  overflow: hidden;
}

.tuao-logo-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  border-radius: 11px;
}

.tuao-modal-agency {
  font-size: 10.5px;
  font-weight: 600;
  letter-spacing: 0.1em;
  text-transform: uppercase;
  color: rgba(255, 255, 255, 0.55);
  margin: 0 0 3px;
}

.tuao-modal-title {
  font-size: 17px;
  font-weight: 700;
  color: #ffffff;
  margin: 0;
}

.tuao-modal-close {
  width: 34px;
  height: 34px;
  background: rgba(255, 255, 255, 0.08);
  border: 1px solid rgba(255, 255, 255, 0.12);
  border-radius: 8px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  color: rgba(255, 255, 255, 0.7);
  transition: all 0.15s;
  flex-shrink: 0;
}

.tuao-modal-close:hover {
  background: rgba(21, 101, 192, 0.6);
  color: #fff;
  border-color: transparent;
}

.tuao-modal-close .material-icons-round { font-size: 18px; }

.tuao-modal-stripe {
  background: linear-gradient(90deg, #1565c0 0%, #0d3b78 100%);
  padding: 7px 22px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 12px;
}

.tuao-modal-stripe span {
  font-size: 9.5px;
  font-weight: 700;
  letter-spacing: 0.12em;
  text-transform: uppercase;
  color: rgba(255, 255, 255, 0.85);
}

.tuao-modal-body {
  padding: 20px 22px;
  flex: 1;
}

.tuao-section { margin-bottom: 20px; }
.tuao-section:last-child { margin-bottom: 0; }

.tuao-section-label {
  display: flex;
  align-items: center;
  gap: 7px;
  font-size: 11px;
  font-weight: 700;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  color: #7f1d1d;
  background: #fef2f2;
  border-left: 3px solid #1565c0;
  padding: 7px 12px;
  border-radius: 0 8px 8px 0;
  margin-bottom: 14px;
}

.tuao-section-label .material-icons-round {
  font-size: 15px;
  color: #1565c0;
}

.tuao-form-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 12px;
}

.tuao-field-half { grid-column: span 1; }
.tuao-field-full { grid-column: 1 / -1; }

.tuao-label {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 11.5px;
  font-weight: 600;
  color: #374151;
  margin-bottom: 5px;
  letter-spacing: 0.01em;
}

.tuao-required {
  color: #1565c0;
  font-size: 13px;
}

.tuao-input-wrap { position: relative; }

.tuao-input-icon {
  position: absolute;
  left: 11px;
  top: 50%;
  transform: translateY(-50%);
  font-size: 16px;
  color: #9ca3af;
  pointer-events: none;
}

.tuao-input {
  width: 100%;
  padding: 9px 12px 9px 36px;
  border: 1.5px solid #e5e7eb;
  border-radius: 9px;
  font-size: 13px;
  color: #111827;
  background: #fafafa;
  outline: none;
  transition: border-color 0.15s, box-shadow 0.15s, background 0.15s;
  -webkit-appearance: none;
  appearance: none;
}

.tuao-input:focus {
  border-color: #1565c0;
  background: #fff;
  box-shadow: 0 0 0 3px rgba(21, 101, 192, 0.1);
}

.tuao-select {
  cursor: pointer;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%239ca3af' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right 12px center;
  padding-right: 32px;
}

.tuao-textarea {
  padding: 10px 12px;
  resize: vertical;
  min-height: 72px;
  line-height: 1.5;
}

.tuao-modal-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 14px;
  padding: 14px 22px;
  background: #f9fafb;
  border-top: 1px solid #f0f0f0;
  border-radius: 0 0 16px 16px;
}

.tuao-footer-note {
  font-size: 11.5px;
  color: #6b7280;
}

.tuao-footer-actions {
  display: flex;
  gap: 10px;
  align-items: center;
}

.tuao-btn-cancel {
  padding: 9px 18px;
  border: 1.5px solid #e5e7eb;
  border-radius: 9px;
  background: transparent;
  font-size: 13px;
  font-weight: 500;
  color: #6b7280;
  cursor: pointer;
  transition: all 0.15s;
}

.tuao-btn-cancel:hover {
  background: #f3f4f6;
  border-color: #d1d5db;
  color: #374151;
}

.tuao-btn-save {
  display: inline-flex;
  align-items: center;
  gap: 7px;
  padding: 9px 22px;
  background: #1565c0;
  border: none;
  border-radius: 9px;
  font-size: 13px;
  font-weight: 600;
  color: #ffffff;
  cursor: pointer;
  transition: all 0.15s;
}

.tuao-btn-save:hover {
  background: #a93226;
  transform: translateY(-1px);
  box-shadow: 0 4px 14px rgba(21, 101, 192, 0.35);
}

.tuao-btn-save .material-icons-round { font-size: 17px; }

.tuao-filter-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 10px;
}

.tuao-check-option {
  display: flex;
  align-items: center;
  gap: 9px;
  padding: 9px 11px;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  color: #374151;
  font-size: 13px;
  cursor: pointer;
  background: #fafafa;
}

.tuao-check-option input {
  accent-color: #1565c0;
}

.tuao-upload-panel {
  border: 2px dashed #e5e7eb;
  border-radius: 12px;
  padding: 24px 18px;
  text-align: center;
  background: #fafafa;
}

.tuao-upload-panel .material-icons-round {
  font-size: 34px;
  color: #1565c0;
  display: block;
  margin-bottom: 8px;
}

.tuao-upload-title {
  font-size: 14px;
  font-weight: 700;
  color: #111827;
  margin: 0 0 3px;
}

.tuao-upload-sub {
  font-size: 12px;
  color: #9ca3af;
  margin: 0 0 12px;
}

.tuao-modal::-webkit-scrollbar { width: 5px; }
.tuao-modal::-webkit-scrollbar-track { background: transparent; }
.tuao-modal::-webkit-scrollbar-thumb { background: #e5e7eb; border-radius: 99px; }

.tuao-modal-fade-enter-active,
.tuao-modal-fade-leave-active {
  transition: opacity 0.2s ease;
}

.tuao-modal-fade-enter-active .tuao-modal,
.tuao-modal-fade-leave-active .tuao-modal {
  transition: transform 0.22s cubic-bezier(0.34, 1.2, 0.64, 1), opacity 0.2s ease;
}

.tuao-modal-fade-enter-from,
.tuao-modal-fade-leave-to {
  opacity: 0;
}

.tuao-modal-fade-enter-from .tuao-modal {
  transform: translateY(24px) scale(0.97);
  opacity: 0;
}

.tuao-modal-fade-leave-to .tuao-modal {
  transform: translateY(12px) scale(0.98);
  opacity: 0;
}

@media (max-width: 576px) {
  .tuao-form-grid,
  .tuao-filter-grid {
    grid-template-columns: 1fr;
  }

  .tuao-field-half { grid-column: span 1; }

  .tuao-modal-footer {
    flex-direction: column;
    align-items: stretch;
  }

  .tuao-footer-actions {
    justify-content: flex-end;
  }

  .tuao-modal-stripe {
    align-items: flex-start;
    flex-direction: column;
    gap: 3px;
  }
}
</style>
