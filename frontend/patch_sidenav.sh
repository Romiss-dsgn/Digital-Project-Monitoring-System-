#!/bin/bash
set -e
FILE="src/examples/Sidenav/index.vue"

echo "== 1. Template: move toggle button into header row =="
BSTART=$(grep -n '<button' "$FILE" | head -1 | cut -d: -f1)
BEND=$(grep -n '</button>' "$FILE" | head -1 | cut -d: -f1)
AEND=$(awk -v s="$BEND" 'NR>s && /<\/a>/{print NR; exit}' "$FILE")
echo "  deleting lines $BSTART-$AEND"

cat > /tmp/template_fragment.txt << 'EOF'
      <div class="sidenav-header-row">
        <a class="m-0 navbar-brand d-flex align-items-center" href="/dashboard">
          <div class="logo-wrapper">
            <img
              :src="logo"
              class="navbar-brand-img"
              alt="main_logo"
            />
            <div class="logo-glow"></div>
          </div>
          <div class="brand-text-wrapper">
            <span class="font-weight-bold text-white sidebar-brand-text">LGU Tuao</span>
            <span class="sidebar-brand-sub">Municipality of Tuao</span>
          </div>
        </a>
        <button
          type="button"
          class="sidenav-toggle-btn d-none d-xl-flex"
          :aria-expanded="(!isCollapsed).toString()"
          aria-label="Toggle sidebar"
          @click="toggleSidenav"
        >
          <span class="material-symbols-rounded">{{ isCollapsed ? 'chevron_right' : 'chevron_left' }}</span>
        </button>
      </div>
EOF

sed -i "${BSTART},${AEND}d" "$FILE"
sed -i "$((BSTART-1))r /tmp/template_fragment.txt" "$FILE"

echo "== 2. CSS: strip circle background, add header-row rule =="
CSTART=$(grep -n '\.sidenav-toggle-btn {' "$FILE" | head -1 | cut -d: -f1)
CEND=$(awk -v s="$CSTART" 'NR>=s && /material-symbols-rounded/{f=1} f && /^}$/{print NR; exit}' "$FILE")
echo "  deleting lines $CSTART-$CEND"

cat > /tmp/css_fragment.txt << 'EOF'
.sidenav-header-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  width: 100%;
  gap: 8px;
}
.sidenav-toggle-btn {
  flex-shrink: 0;
  width: 28px;
  height: 28px;
  display: flex;
  align-items: center;
  justify-content: center;
  border: none;
  background: transparent;
  color: #fff;
  cursor: pointer;
  opacity: 0.85;
  transition: opacity 0.2s ease;
}
.sidenav-toggle-btn:hover {
  opacity: 1;
}
.sidenav-toggle-btn .material-symbols-rounded {
  font-size: 20px;
  color: #fff;
}
EOF

sed -i "${CSTART},${CEND}d" "$FILE"
sed -i "$((CSTART-1))r /tmp/css_fragment.txt" "$FILE"

echo "== 3. Collapsed state: stack instead of overlap =="
KSTART=$(grep -n '\.sidenav-collapsed \.sidenav-toggle-btn {' "$FILE" | head -1 | cut -d: -f1)
KEND=$(awk -v s="$KSTART" 'NR>=s && /^}$/{print NR; exit}' "$FILE")
echo "  deleting lines $KSTART-$KEND"

cat > /tmp/collapsed_fragment.txt << 'EOF'
.sidenav.sidenav-collapsed .sidenav-header-row {
  flex-direction: column;
  gap: 6px;
}
.sidenav.sidenav-collapsed .sidenav-toggle-btn {
  width: 22px;
  height: 22px;
}
EOF

sed -i "${KSTART},${KEND}d" "$FILE"
sed -i "$((KSTART-1))r /tmp/collapsed_fragment.txt" "$FILE"

echo "Done patching $FILE"