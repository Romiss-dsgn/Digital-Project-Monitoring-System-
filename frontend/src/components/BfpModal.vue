<template>
  <Teleport to="body">
    <Transition name="bfp-modal-fade">
      <div v-if="show" class="bfp-modal-overlay" @click.self="$emit('close')">
        <div class="bfp-modal" :style="{ width }" role="dialog" aria-modal="true">
          <div class="bfp-modal-header">
            <div class="bfp-modal-header-left">
              <div class="bfp-modal-emblem">
                <img :src="bfpLogo" alt="BFP Logo" class="bfp-logo-img" />
              </div>
              <div>
                <p class="bfp-modal-agency">{{ agency }}</p>
                <h5 class="bfp-modal-title">{{ title }}</h5>
              </div>
            </div>
            <button class="bfp-modal-close" type="button" @click="$emit('close')" aria-label="Close modal">
              <i class="material-icons-round">close</i>
            </button>
          </div>

          <div class="bfp-modal-stripe">
            <span>{{ region }}</span>
            <span>{{ stripe }}</span>
          </div>

          <div class="bfp-modal-body">
            <slot />
          </div>

          <div v-if="showFooter" class="bfp-modal-footer">
            <div class="bfp-footer-note">
              <slot name="note">{{ note }}</slot>
            </div>
            <div class="bfp-footer-actions">
              <button class="bfp-btn-cancel" type="button" :disabled="loading" @click="$emit('close')">{{ cancelText }}</button>
              <button class="bfp-btn-save" type="button" :disabled="loading" @click="$emit('confirm')">
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
import bfpLogo from "@/assets/img/BFP 11.png";

export default {
  name: "BfpModal",
  props: {
    show: { type: Boolean, default: false },
    title: { type: String, required: true },
    stripe: { type: String, default: "OFFICIAL FORM" },
    agency: { type: String, default: "Bureau of Fire Protection" },
    region: { type: String, default: "REGION II - CAGAYAN VALLEY" },
    note: { type: String, default: "Fields marked * are required." },
    cancelText: { type: String, default: "Cancel" },
    confirmText: { type: String, default: "Save" },
    confirmIcon: { type: String, default: "save" },
    showFooter: { type: Boolean, default: true },
    width: { type: String, default: "620px" },
    loading: { type: Boolean, default: false },
    confirmVariant: { type: String, default: "primary" }
  },
  emits: ["close", "confirm"],
  data() {
    return { bfpLogo };
  }
};
</script>

<style>
.bfp-modal-overlay {
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

.bfp-modal {
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

.bfp-modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 18px 22px 14px;
  background: #1a1a2e;
  border-radius: 16px 16px 0 0;
}

.bfp-modal-header-left {
  display: flex;
  align-items: center;
  gap: 14px;
  min-width: 0;
}

.bfp-modal-emblem {
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

.bfp-logo-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  border-radius: 11px;
}

.bfp-modal-agency {
  font-size: 10.5px;
  font-weight: 600;
  letter-spacing: 0.1em;
  text-transform: uppercase;
  color: rgba(255, 255, 255, 0.55);
  margin: 0 0 3px;
}

.bfp-modal-title {
  font-size: 17px;
  font-weight: 700;
  color: #ffffff;
  margin: 0;
}

.bfp-modal-close {
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

.bfp-modal-close:hover {
  background: rgba(192, 57, 43, 0.6);
  color: #fff;
  border-color: transparent;
}

.bfp-modal-close .material-icons-round { font-size: 18px; }

.bfp-modal-stripe {
  background: linear-gradient(90deg, #c0392b 0%, #922b21 100%);
  padding: 7px 22px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 12px;
}

.bfp-modal-stripe span {
  font-size: 9.5px;
  font-weight: 700;
  letter-spacing: 0.12em;
  text-transform: uppercase;
  color: rgba(255, 255, 255, 0.85);
}

.bfp-modal-body {
  padding: 20px 22px;
  flex: 1;
}

.bfp-section { margin-bottom: 20px; }
.bfp-section:last-child { margin-bottom: 0; }

.bfp-section-label {
  display: flex;
  align-items: center;
  gap: 7px;
  font-size: 11px;
  font-weight: 700;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  color: #7f1d1d;
  background: #fef2f2;
  border-left: 3px solid #c0392b;
  padding: 7px 12px;
  border-radius: 0 8px 8px 0;
  margin-bottom: 14px;
}

.bfp-section-label .material-icons-round {
  font-size: 15px;
  color: #c0392b;
}

.bfp-form-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 12px;
}

.bfp-field-half { grid-column: span 1; }
.bfp-field-full { grid-column: 1 / -1; }

.bfp-label {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 11.5px;
  font-weight: 600;
  color: #374151;
  margin-bottom: 5px;
  letter-spacing: 0.01em;
}

.bfp-required {
  color: #c0392b;
  font-size: 13px;
}

.bfp-input-wrap { position: relative; }

.bfp-input-icon {
  position: absolute;
  left: 11px;
  top: 50%;
  transform: translateY(-50%);
  font-size: 16px;
  color: #9ca3af;
  pointer-events: none;
}

.bfp-input {
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

.bfp-input:focus {
  border-color: #c0392b;
  background: #fff;
  box-shadow: 0 0 0 3px rgba(192, 57, 43, 0.1);
}

.bfp-select {
  cursor: pointer;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%239ca3af' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right 12px center;
  padding-right: 32px;
}

.bfp-textarea {
  padding: 10px 12px;
  resize: vertical;
  min-height: 72px;
  line-height: 1.5;
}

.bfp-modal-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 14px;
  padding: 14px 22px;
  background: #f9fafb;
  border-top: 1px solid #f0f0f0;
  border-radius: 0 0 16px 16px;
}

.bfp-footer-note {
  font-size: 11.5px;
  color: #6b7280;
}

.bfp-footer-actions {
  display: flex;
  gap: 10px;
  align-items: center;
}

.bfp-btn-cancel {
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

.bfp-btn-cancel:hover {
  background: #f3f4f6;
  border-color: #d1d5db;
  color: #374151;
}

.bfp-btn-save {
  display: inline-flex;
  align-items: center;
  gap: 7px;
  padding: 9px 22px;
  background: #c0392b;
  border: none;
  border-radius: 9px;
  font-size: 13px;
  font-weight: 600;
  color: #ffffff;
  cursor: pointer;
  transition: all 0.15s;
}

.bfp-btn-save:hover {
  background: #a93226;
  transform: translateY(-1px);
  box-shadow: 0 4px 14px rgba(192, 57, 43, 0.35);
}

.bfp-btn-save .material-icons-round { font-size: 17px; }

.bfp-filter-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 10px;
}

.bfp-check-option {
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

.bfp-check-option input {
  accent-color: #c0392b;
}

.bfp-upload-panel {
  border: 2px dashed #e5e7eb;
  border-radius: 12px;
  padding: 24px 18px;
  text-align: center;
  background: #fafafa;
}

.bfp-upload-panel .material-icons-round {
  font-size: 34px;
  color: #c0392b;
  display: block;
  margin-bottom: 8px;
}

.bfp-upload-title {
  font-size: 14px;
  font-weight: 700;
  color: #111827;
  margin: 0 0 3px;
}

.bfp-upload-sub {
  font-size: 12px;
  color: #9ca3af;
  margin: 0 0 12px;
}

.bfp-modal::-webkit-scrollbar { width: 5px; }
.bfp-modal::-webkit-scrollbar-track { background: transparent; }
.bfp-modal::-webkit-scrollbar-thumb { background: #e5e7eb; border-radius: 99px; }

.bfp-modal-fade-enter-active,
.bfp-modal-fade-leave-active {
  transition: opacity 0.2s ease;
}

.bfp-modal-fade-enter-active .bfp-modal,
.bfp-modal-fade-leave-active .bfp-modal {
  transition: transform 0.22s cubic-bezier(0.34, 1.2, 0.64, 1), opacity 0.2s ease;
}

.bfp-modal-fade-enter-from,
.bfp-modal-fade-leave-to {
  opacity: 0;
}

.bfp-modal-fade-enter-from .bfp-modal {
  transform: translateY(24px) scale(0.97);
  opacity: 0;
}

.bfp-modal-fade-leave-to .bfp-modal {
  transform: translateY(12px) scale(0.98);
  opacity: 0;
}

@media (max-width: 576px) {
  .bfp-form-grid,
  .bfp-filter-grid {
    grid-template-columns: 1fr;
  }

  .bfp-field-half { grid-column: span 1; }

  .bfp-modal-footer {
    flex-direction: column;
    align-items: stretch;
  }

  .bfp-footer-actions {
    justify-content: flex-end;
  }

  .bfp-modal-stripe {
    align-items: flex-start;
    flex-direction: column;
    gap: 3px;
  }
}
</style>
