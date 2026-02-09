const { defineConfig } = require('@vue/cli-service')
module.exports = defineConfig({
  publicPath: "/admin/",
  transpileDependencies: [
    'vuetify'
  ],
  pwa:{
    iconPaths:{
      faviconSVG: `/favicon.png`,
      favicon32: `/favicon.png`,
      favicon16: `/favicon.png`,
      appleTouchIcon: `/favicon.png`,
      maskIcon: `/favicon.png`,
      msTileImage: `/favicon.png`
    }
  }
})
