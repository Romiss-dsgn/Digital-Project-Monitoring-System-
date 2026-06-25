# ConTrackPro System Logo Assets

Place app logo assets here when the sidebar/header logo needs to be replaced.

Recommended files:

- `logo.png` - light logo for dark or maroon backgrounds.
- `logo-dark.png` - dark logo for white or light backgrounds.

Recommended specs:

- PNG with transparent background.
- Square source image when possible.
- At least `128x128` so it scales cleanly.
- Keep enough padding so the BFP seal does not become clipped or oval.

Current UI usage:

- Sidebar brand header.
- Main dashboard shell.
- Login and request access pages may use separate BFP image assets from `frontend/src/assets/img/`.

When replacing the logo:

1. Keep file names stable if the component already imports them.
2. Test at desktop and narrow widths.
3. Confirm the image remains circular and centered.
