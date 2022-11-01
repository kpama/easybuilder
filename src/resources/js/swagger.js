const SwaggerUI = require('swagger-ui')
const SwaggerUIStandalonePreset = require('swagger-ui/dist/swagger-ui-standalone-preset')

SwaggerUI({
  dom_id: '#doc',
  configUrl: `${window.location.pathname}-config`,
  plugins: [
    SwaggerUI.plugins.Topbar,
    SwaggerUI.plugins.DownloadUrl
  ],
  presets: [
    SwaggerUI.presets.apis,
    SwaggerUIStandalonePreset
  ],
  layout: "StandaloneLayout"
})