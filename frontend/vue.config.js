module.exports = {
  productionSourceMap: false,
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
