import { defineConfig } from "vite";
import react from "@vitejs/plugin-react";

const UI_PORT = Number(process.env.BOARD_UI_PORT || process.env.LINEAR_UI_PORT || 47382);
const API_PORT = Number(process.env.BOARD_UI_API_PORT || process.env.LINEAR_UI_API_PORT || 47381);

export default defineConfig({
  plugins: [react()],
  server: {
    host: "127.0.0.1",
    port: UI_PORT,
    strictPort: true,
    proxy: {
      "/api": `http://127.0.0.1:${API_PORT}`,
    },
  },
});
