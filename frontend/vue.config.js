module.exports = {
  productionSourceMap: false,
  transpileDependencies: ["jspdf", "fast-png", "iobuffer"],
  configureWebpack: {
    performance: {
      hints: false,
    },
  },
  devServer: {
    proxy: {
      "/api": {
        target: "http://localhost:8000",
        changeOrigin: true,
      },
    },
  },
};
