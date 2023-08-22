const path = require("path")
const MiniCssExtractPlugin = require("mini-css-extract-plugin")
const wpScriptsWebpackConfig = require("@wordpress/scripts/config/webpack.config")

module.exports = {
  ...wpScriptsWebpackConfig,
  output: {
    path: path.join(__dirname, "/build"),
    filename: "[name].min.js",
  },
  plugins: [
    new MiniCssExtractPlugin({
      filename: "[name].min.css",
    }),
    ...wpScriptsWebpackConfig.plugins.filter(
      plugin => !(plugin instanceof MiniCssExtractPlugin)
    )
  ],
}